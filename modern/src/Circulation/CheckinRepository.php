<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use PDO;
use PDOStatement;

final class CheckinRepository
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
     * @return list<array{bibid: int, copyid: int, barcode_nmbr: string, title: string, author: string, status_begin_dt: string}>
     */
    public function getShelvingCart(): array
    {
        return $this->fetchAll(
            "SELECT c.bibid, c.copyid, c.barcode_nmbr, b.title, b.author, c.status_begin_dt "
            . 'FROM biblio_copy AS c INNER JOIN biblio AS b ON b.bibid = c.bibid '
            . "WHERE c.status_cd = 'crt' ORDER BY c.status_begin_dt, c.barcode_nmbr",
            [],
        );
    }

    /**
     * @return array{barcode_nmbr: string, member: array{mbrid: int, first_name: string, last_name: string}|null, late_days: int, fee: float, status_cd: string}
     */
    public function shelve(string $barcode, int $staffUserId): array
    {
        if ($barcode === '' || strlen($barcode) > 20 || $staffUserId < 1) {
            throw new \InvalidArgumentException('O código do exemplar ou o usuário da operação é inválido.');
        }

        $this->acquireLock();
        $operationError = null;
        try {
            return $this->performShelving($barcode, $staffUserId);
        } catch (\Throwable $error) {
            $operationError = $error;
            throw $error;
        } finally {
            $this->releaseLockPreservingCause($operationError);
        }
    }

    /**
     * @param list<array{bibid: int, copyid: int}> $copies
     */
    public function complete(array $copies, bool $all): int
    {
        if (!$all && $copies === []) {
            throw new CheckinRejected('Selecione pelo menos um exemplar para concluir a devolução.');
        }
        foreach ($copies as $copy) {
            if (!isset($copy['bibid'], $copy['copyid']) || $copy['bibid'] < 1 || $copy['copyid'] < 1) {
                throw new \InvalidArgumentException('A lista de exemplares para devolução é inválida.');
            }
        }

        $this->acquireLock();
        $operationError = null;
        try {
            return $this->performComplete($copies, $all);
        } catch (\Throwable $error) {
            $operationError = $error;
            throw $error;
        } finally {
            $this->releaseLockPreservingCause($operationError);
        }
    }

    /**
     * @return array{barcode_nmbr: string, member: array{mbrid: int, first_name: string, last_name: string}|null, late_days: int, fee: float, status_cd: string}
     */
    private function performShelving(string $barcode, int $staffUserId): array
    {
        $rows = $this->fetchAll(
            "SELECT c.bibid, c.copyid, c.barcode_nmbr, c.status_cd, c.status_begin_dt, "
            . 'c.due_back_dt, c.mbrid, c.renewal_count, '
            . 'GREATEST(0, DATEDIFF(CURRENT_DATE, c.due_back_dt)) AS late_days, '
            . 'COALESCE(collection.daily_late_fee, 0) AS daily_late_fee, '
            . 'm.first_name, m.last_name '
            . 'FROM biblio_copy AS c '
            . 'INNER JOIN biblio AS b ON b.bibid = c.bibid '
            . 'LEFT JOIN collection_dm AS collection ON collection.code = b.collection_cd '
            . 'LEFT JOIN member AS m ON m.mbrid = c.mbrid '
            . 'WHERE c.barcode_nmbr = :barcode ORDER BY c.bibid, c.copyid LIMIT 2',
            [':barcode' => [$barcode, PDO::PARAM_STR]],
        );
        if (count($rows) > 1) {
            throw new CheckinRejected('O código de barras está associado a mais de um exemplar.');
        }
        $copy = $rows[0] ?? null;
        if ($copy === null) {
            throw new CheckinRejected('Código de barras do exemplar inválido.');
        }
        if ($copy['status_cd'] !== 'out') {
            throw new CheckinRejected('Somente exemplares atualmente emprestados podem ser devolvidos.');
        }

        $holdRows = $this->fetchAll(
            'SELECT holdid FROM biblio_hold WHERE bibid = :bibid AND copyid = :copyid '
            . 'ORDER BY hold_begin_dt LIMIT 1',
            [
                ':bibid' => [(int) $copy['bibid'], PDO::PARAM_INT],
                ':copyid' => [(int) $copy['copyid'], PDO::PARAM_INT],
            ],
        );
        $newStatus = $holdRows === [] ? 'crt' : 'hld';
        $statusBegin = $this->currentTimestamp();
        $memberId = $copy['mbrid'] === null ? null : (int) $copy['mbrid'];
        $dueBackDate = $copy['due_back_dt'] === null ? null : (string) $copy['due_back_dt'];
        $renewalCount = (int) $copy['renewal_count'];

        $this->execute(
            "UPDATE biblio_copy SET status_cd = :new_status, status_begin_dt = :status_begin_dt, "
            . 'due_back_dt = NULL, mbrid = NULL '
            . "WHERE bibid = :bibid AND copyid = :copyid AND status_cd = 'out'",
            [
                ':new_status' => [$newStatus, PDO::PARAM_STR],
                ':status_begin_dt' => [$statusBegin, PDO::PARAM_STR],
                ':bibid' => [(int) $copy['bibid'], PDO::PARAM_INT],
                ':copyid' => [(int) $copy['copyid'], PDO::PARAM_INT],
            ],
            true,
        );
        $this->insertHistory(
            (int) $copy['bibid'],
            (int) $copy['copyid'],
            $newStatus,
            $statusBegin,
            null,
            $memberId,
            $renewalCount,
        );

        $lateDays = (int) $copy['late_days'];
        $dailyFee = (float) $copy['daily_late_fee'];
        $fee = round($lateDays * $dailyFee, 2);
        if ($memberId !== null && $lateDays > 0 && $dailyFee > 0) {
            $this->execute(
                'INSERT INTO member_account '
                . '(mbrid, create_dt, create_userid, transaction_type_cd, amount, description) '
                . "VALUES (:mbrid, CURRENT_TIMESTAMP, :userid, '+c', :amount, :description)",
                [
                    ':mbrid' => [$memberId, PDO::PARAM_INT],
                    ':userid' => [$staffUserId, PDO::PARAM_INT],
                    ':amount' => [$fee, PDO::PARAM_STR],
                    ':description' => [
                        'Taxa de atraso (barcode=' . $copy['barcode_nmbr'] . ')',
                        PDO::PARAM_STR,
                    ],
                ],
            );
        }

        $member = $memberId === null ? null : [
            'mbrid' => $memberId,
            'first_name' => (string) ($copy['first_name'] ?? ''),
            'last_name' => (string) ($copy['last_name'] ?? ''),
        ];

        return [
            'barcode_nmbr' => (string) $copy['barcode_nmbr'],
            'member' => $member,
            'late_days' => $lateDays,
            'fee' => $fee,
            'status_cd' => $newStatus,
        ];
    }

    /**
     * @param list<array{bibid: int, copyid: int}> $copies
     */
    private function performComplete(array $copies, bool $all): int
    {
        if ($all) {
            $rows = $this->fetchAll(
                "SELECT bibid, copyid FROM biblio_copy WHERE status_cd = 'crt'",
                [],
            );
        } else {
            $rows = [];
            foreach ($copies as $copy) {
                $match = $this->fetchAll(
                    "SELECT bibid, copyid FROM biblio_copy "
                    . "WHERE bibid = :bibid AND copyid = :copyid AND status_cd = 'crt'",
                    [
                        ':bibid' => [$copy['bibid'], PDO::PARAM_INT],
                        ':copyid' => [$copy['copyid'], PDO::PARAM_INT],
                    ],
                );
                array_push($rows, ...$match);
            }
        }

        $changed = 0;
        foreach ($rows as $copy) {
            $bibId = (int) $copy['bibid'];
            $copyId = (int) $copy['copyid'];
            $statusBegin = $this->currentTimestamp();
            $this->execute(
                "UPDATE biblio_copy SET status_cd = 'in', status_begin_dt = :status_begin_dt "
                . 'WHERE bibid = :bibid AND copyid = :copyid AND status_cd = \'crt\'',
                [
                    ':status_begin_dt' => [$statusBegin, PDO::PARAM_STR],
                    ':bibid' => [$bibId, PDO::PARAM_INT],
                    ':copyid' => [$copyId, PDO::PARAM_INT],
                ],
                true,
            );
            $changed++;
        }

        return $changed;
    }

    private function insertHistory(
        int $bibId,
        int $copyId,
        string $status,
        string $statusBegin,
        ?string $dueBack,
        ?int $memberId,
        int $renewalCount,
    ): void {
        $this->execute(
            'INSERT INTO biblio_status_hist '
            . '(bibid, copyid, status_cd, status_begin_dt, due_back_dt, mbrid, renewal_count) '
            . 'VALUES (:bibid, :copyid, :status_cd, :status_begin_dt, :due_back_dt, :mbrid, :renewal_count)',
            [
                ':bibid' => [$bibId, PDO::PARAM_INT],
                ':copyid' => [$copyId, PDO::PARAM_INT],
                ':status_cd' => [$status, PDO::PARAM_STR],
                ':status_begin_dt' => [$statusBegin, PDO::PARAM_STR],
                ':due_back_dt' => [$dueBack, $dueBack === null ? PDO::PARAM_NULL : PDO::PARAM_STR],
                ':mbrid' => [$memberId, $memberId === null ? PDO::PARAM_NULL : PDO::PARAM_INT],
                ':renewal_count' => [$renewalCount, PDO::PARAM_INT],
            ],
        );
    }

    private function currentTimestamp(): string
    {
        $rows = $this->fetchAll('SELECT CURRENT_TIMESTAMP AS status_begin_dt', []);
        $timestamp = $rows[0]['status_begin_dt'] ?? null;
        if (!is_string($timestamp) || $timestamp === '') {
            throw new \RuntimeException('O banco não retornou o horário da devolução.');
        }

        return $timestamp;
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

    private function releaseLockPreservingCause(?\Throwable $operationError): void
    {
        try {
            $rows = $this->fetchAll(
                'SELECT RELEASE_LOCK(:lock_name) AS released',
                [':lock_name' => [$this->lockName, PDO::PARAM_STR]],
            );
            if ((int) ($rows[0]['released'] ?? 0) !== 1) {
                throw new \RuntimeException('Não foi possível liberar o bloqueio de circulação.');
            }
        } catch (\Throwable $releaseError) {
            if ($operationError === null) {
                throw $releaseError;
            }

            throw new \RuntimeException(
                'A operação de devolução falhou e o bloqueio também não pôde ser liberado: '
                . $releaseError->getMessage(),
                0,
                $operationError,
            );
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
            throw new CheckinRejected('O exemplar mudou durante a devolução; atualize os dados e tente novamente.');
        }
    }

    /**
     * @param array<string, array{mixed, int}> $parameters
     */
    private function statement(string $sql, array $parameters): PDOStatement
    {
        $statement = $this->connection->prepare($sql);
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar uma operação de devolução.');
        }
        foreach ($parameters as $name => [$value, $type]) {
            $statement->bindValue($name, $value, $type);
        }
        if (!$statement->execute()) {
            throw new \RuntimeException('Uma operação de devolução falhou no banco de dados.');
        }

        return $statement;
    }
}
