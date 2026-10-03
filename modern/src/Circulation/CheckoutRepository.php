<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use PDO;
use PDOStatement;

final class CheckoutRepository
{
    public function __construct(
        private readonly PDO $connection,
        private readonly string $lockName = 'OpenBiblio',
        private readonly int $lockTimeoutSeconds = 10,
    ) {
        if ($lockName === '' || $lockTimeoutSeconds < 0) {
            throw new \InvalidArgumentException('A configuração de bloqueio de circulação é inválida.');
        }
    }

    /**
     * @return array{member: array<string, mixed>, copy: array<string, mixed>, due_back_dt: string, renewal_count: int}
     */
    public function checkout(string $memberBarcode, string $copyBarcode): array
    {
        if ($memberBarcode === '' || $copyBarcode === '') {
            throw new \InvalidArgumentException('Membro e exemplar são obrigatórios.');
        }

        $this->acquireLock();
        $operationError = null;
        try {
            return $this->performCheckout($memberBarcode, $copyBarcode);
        } catch (\Throwable $error) {
            $operationError = $error;
            throw $error;
        } finally {
            try {
                $this->releaseLock();
            } catch (\Throwable $releaseError) {
                if ($operationError === null) {
                    throw $releaseError;
                }

                throw new \RuntimeException(
                    'A operação de empréstimo falhou e o bloqueio também não pôde ser liberado: '
                    . $releaseError->getMessage(),
                    0,
                    $operationError,
                );
            }
        }
    }

    /**
     * @return array{member: array<string, mixed>, copy: array<string, mixed>, due_back_dt: string, renewal_count: int}
     */
    private function performCheckout(string $memberBarcode, string $copyBarcode): array
    {
        $memberRows = $this->fetchAll(
            'SELECT mbrid, barcode_nmbr, first_name, last_name, classification '
            . 'FROM member WHERE barcode_nmbr = :barcode ORDER BY mbrid ASC LIMIT 2',
            [':barcode' => [$memberBarcode, PDO::PARAM_STR]],
        );
        if (count($memberRows) > 1) {
            throw new CheckoutRejected('O código de barras está associado a mais de um membro.');
        }
        $member = $memberRows[0] ?? null;
        if ($member === null) {
            throw new CheckoutRejected('Código de barras do membro inválido.');
        }

        $memberId = (int) $member['mbrid'];
        $holdMaxDays = $this->assertNoBlockingBalance($memberId);

        $copyRows = $this->fetchAll(
            'SELECT c.bibid, c.copyid, c.barcode_nmbr, c.status_cd, c.status_begin_dt, '
            . 'c.due_back_dt, c.mbrid, c.renewal_count, b.material_cd, '
            . 'COALESCE(collection.days_due_back, 0) AS days_due_back, '
            . 'COALESCE(priv.checkout_limit, 0) AS checkout_limit, '
            . 'COALESCE(priv.renewal_limit, 0) AS renewal_limit, '
            . 'CASE WHEN c.due_back_dt < CURRENT_DATE THEN 1 ELSE 0 END AS overdue '
            . 'FROM biblio_copy AS c '
            . 'INNER JOIN biblio AS b ON b.bibid = c.bibid '
            . 'LEFT JOIN collection_dm AS collection ON collection.code = b.collection_cd '
            . 'LEFT JOIN checkout_privs AS priv ON priv.material_cd = b.material_cd '
            . 'AND priv.classification = :classification '
            . 'WHERE c.barcode_nmbr = :barcode ORDER BY c.bibid, c.copyid LIMIT 2',
            [
                ':classification' => [(int) $member['classification'], PDO::PARAM_INT],
                ':barcode' => [$copyBarcode, PDO::PARAM_STR],
            ],
        );
        if (count($copyRows) > 1) {
            throw new CheckoutRejected('O código de barras está associado a mais de um exemplar.');
        }
        $copy = $copyRows[0] ?? null;
        if ($copy === null) {
            throw new CheckoutRejected('Código de barras do exemplar inválido.');
        }

        $bibId = (int) $copy['bibid'];
        $copyId = (int) $copy['copyid'];
        $daysDueBack = (int) $copy['days_due_back'];
        if ($daysDueBack <= 0) {
            throw new CheckoutRejected('Empréstimos não são permitidos para esta coleção.');
        }

        $currentStatus = (string) $copy['status_cd'];
        $isRenewal = $currentStatus === 'out' && (int) ($copy['mbrid'] ?? 0) === $memberId;
        if ($currentStatus === 'out' && !$isRenewal) {
            throw new CheckoutRejected('O exemplar já está emprestado para outro membro.');
        }

        if ($isRenewal) {
            if ((int) $copy['overdue'] === 1) {
                throw new CheckoutRejected('O exemplar está atrasado e não pode ser renovado.');
            }
            $renewalLimit = (int) $copy['renewal_limit'];
            if ($renewalLimit > 0 && (int) $copy['renewal_count'] >= $renewalLimit) {
                throw new CheckoutRejected('O exemplar atingiu seu limite de renovação.');
            }
        } else {
            $this->assertCheckoutLimit($memberId, (int) $copy['material_cd'], (int) $copy['checkout_limit']);
        }

        if ($currentStatus === 'hld') {
            $holds = $this->fetchAll(
                'SELECT holdid, mbrid, DATEDIFF(CURRENT_DATE, hold_begin_dt) AS hold_age FROM biblio_hold '
                . 'WHERE bibid = :bibid AND copyid = :copyid ORDER BY hold_begin_dt ASC LIMIT 1',
                [
                    ':bibid' => [$bibId, PDO::PARAM_INT],
                    ':copyid' => [$copyId, PDO::PARAM_INT],
                ],
            );
            $hold = $holds[0] ?? null;
            if ($hold !== null) {
                $isExpired = $holdMaxDays > 0 && (int) $hold['hold_age'] > $holdMaxDays;
                if ((int) $hold['mbrid'] !== $memberId && !$isExpired) {
                    throw new CheckoutRejected('O exemplar está reservado para outro membro.');
                }
                $this->execute(
                    'DELETE FROM biblio_hold WHERE bibid = :bibid AND copyid = :copyid AND holdid = :holdid',
                    [
                        ':bibid' => [$bibId, PDO::PARAM_INT],
                        ':copyid' => [$copyId, PDO::PARAM_INT],
                        ':holdid' => [(int) $hold['holdid'], PDO::PARAM_INT],
                    ],
                );
            }
        }

        $renewalCount = $isRenewal ? (int) $copy['renewal_count'] + 1 : 0;
        $dueDateRows = $this->fetchAll(
            'SELECT DATE_ADD(CURRENT_DATE, INTERVAL ' . $daysDueBack . ' DAY) AS due_back_dt',
            [],
        );
        $dueBackDate = $dueDateRows[0]['due_back_dt'] ?? null;
        if (!is_string($dueBackDate) || $dueBackDate === '') {
            throw new \RuntimeException('O banco não retornou a data de devolução calculada.');
        }
        $statusBeginRows = $this->fetchAll('SELECT CURRENT_TIMESTAMP AS status_begin_dt', []);
        $statusBeginDate = $statusBeginRows[0]['status_begin_dt'] ?? null;
        if (!is_string($statusBeginDate) || $statusBeginDate === '') {
            throw new \RuntimeException('O banco não retornou a data de início do empréstimo.');
        }

        $this->execute(
            "UPDATE biblio_copy SET status_cd = 'out', status_begin_dt = :status_begin_dt, "
            . 'due_back_dt = :due_back_dt, mbrid = :mbrid, renewal_count = :renewal_count '
            . 'WHERE bibid = :bibid AND copyid = :copyid AND status_cd = :status_cd',
            [
                ':status_begin_dt' => [$statusBeginDate, PDO::PARAM_STR],
                ':due_back_dt' => [$dueBackDate, PDO::PARAM_STR],
                ':mbrid' => [$memberId, PDO::PARAM_INT],
                ':renewal_count' => [$renewalCount, PDO::PARAM_INT],
                ':bibid' => [$bibId, PDO::PARAM_INT],
                ':copyid' => [$copyId, PDO::PARAM_INT],
                ':status_cd' => [$currentStatus, PDO::PARAM_STR],
            ],
            true,
        );
        $this->execute(
            'INSERT INTO biblio_status_hist '
            . '(bibid, copyid, status_cd, status_begin_dt, due_back_dt, mbrid, renewal_count) '
            . "VALUES (:bibid, :copyid, 'out', :status_begin_dt, :due_back_dt, :mbrid, :renewal_count)",
            [
                ':bibid' => [$bibId, PDO::PARAM_INT],
                ':copyid' => [$copyId, PDO::PARAM_INT],
                ':status_begin_dt' => [$statusBeginDate, PDO::PARAM_STR],
                ':due_back_dt' => [$dueBackDate, PDO::PARAM_STR],
                ':mbrid' => [$memberId, PDO::PARAM_INT],
                ':renewal_count' => [$renewalCount, PDO::PARAM_INT],
            ],
        );

        return [
            'member' => $member,
            'copy' => $copy,
            'due_back_dt' => $dueBackDate,
            'renewal_count' => $renewalCount,
        ];
    }

    private function assertNoBlockingBalance(int $memberId): int
    {
        $rows = $this->fetchAll(
            'SELECT settings.block_checkouts_when_fines_due, settings.hold_max_days, '
            . 'COALESCE((SELECT SUM(amount) FROM member_account WHERE mbrid = :mbrid), 0) AS balance '
            . 'FROM settings LIMIT 1',
            [':mbrid' => [$memberId, PDO::PARAM_INT]],
        );
        $settings = $rows[0] ?? null;
        if ($settings === null) {
            throw new \RuntimeException('As configurações da biblioteca não foram encontradas.');
        }

        $blocksCheckouts = $settings['block_checkouts_when_fines_due'] === 'Y'
            || $settings['block_checkouts_when_fines_due'] === 'y';
        if ($blocksCheckouts && (float) $settings['balance'] > 0) {
            throw new CheckoutRejected('O membro deve multas; o empréstimo não é permitido.');
        }

        return (int) $settings['hold_max_days'];
    }

    private function assertCheckoutLimit(int $memberId, int $materialId, int $checkoutLimit): void
    {
        if ($checkoutLimit === 0) {
            return;
        }
        if ($checkoutLimit < 0) {
            throw new \UnexpectedValueException('O limite de empréstimo armazenado é inválido.');
        }

        $rows = $this->fetchAll(
            'SELECT COUNT(*) AS row_count FROM biblio_copy AS c '
            . 'INNER JOIN biblio AS b ON b.bibid = c.bibid '
            . 'WHERE c.mbrid = :mbrid AND b.material_cd = :material_cd',
            [
                ':mbrid' => [$memberId, PDO::PARAM_INT],
                ':material_cd' => [$materialId, PDO::PARAM_INT],
            ],
        );
        $count = (int) ($rows[0]['row_count'] ?? -1);
        if ($count < 0) {
            throw new \RuntimeException('Não foi possível contar os empréstimos ativos do membro.');
        }
        if ($count >= $checkoutLimit) {
            throw new CheckoutRejected('O membro atingiu o limite de empréstimo para esta coleção.');
        }
    }

    private function acquireLock(): void
    {
        $rows = $this->fetchAll(
            'SELECT GET_LOCK(:lock_name, :timeout) AS acquired',
            [
                ':lock_name' => [$this->lockName, PDO::PARAM_STR],
                ':timeout' => [$this->lockTimeoutSeconds, PDO::PARAM_INT],
            ],
        );
        if ((int) ($rows[0]['acquired'] ?? 0) !== 1) {
            throw new \RuntimeException('Não foi possível obter o bloqueio exclusivo de circulação.');
        }
    }

    private function releaseLock(): void
    {
        $rows = $this->fetchAll(
            'SELECT RELEASE_LOCK(:lock_name) AS released',
            [':lock_name' => [$this->lockName, PDO::PARAM_STR]],
        );
        if ((int) ($rows[0]['released'] ?? 0) !== 1) {
            throw new \RuntimeException('Não foi possível liberar o bloqueio de circulação.');
        }
    }

    /**
     * @param array<string, array{mixed, int}> $parameters
     * @return list<array<string, mixed>>
     */
    private function fetchAll(string $sql, array $parameters): array
    {
        return $this->statement($sql, $parameters)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string, array{mixed, int}> $parameters
     */
    private function execute(string $sql, array $parameters, bool $mustAffectOneRow = false): void
    {
        $statement = $this->statement($sql, $parameters);
        if ($mustAffectOneRow && $statement->rowCount() !== 1) {
            throw new CheckoutRejected('O exemplar mudou durante o empréstimo; atualize os dados e tente novamente.');
        }
    }

    /**
     * @param array<string, array{mixed, int}> $parameters
     */
    private function statement(string $sql, array $parameters): PDOStatement
    {
        $statement = $this->connection->prepare($sql);
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar uma operação de circulação.');
        }
        foreach ($parameters as $name => [$value, $type]) {
            $statement->bindValue($name, $value, $type);
        }
        if (!$statement->execute()) {
            throw new \RuntimeException('Uma operação de circulação falhou no banco de dados.');
        }

        return $statement;
    }
}
