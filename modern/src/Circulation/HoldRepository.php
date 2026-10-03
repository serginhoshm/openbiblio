<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use PDO;
use PDOStatement;

final class HoldRepository
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
     * @return array{mbrid: int, barcode_nmbr: string, first_name: string, last_name: string}
     */
    public function findMember(string $barcode): array
    {
        if ($barcode === '' || strlen($barcode) > 20) {
            throw new \InvalidArgumentException('O código de barras do membro é inválido.');
        }
        $rows = $this->fetchAll(
            'SELECT mbrid, barcode_nmbr, first_name, last_name FROM member '
            . 'WHERE barcode_nmbr = :barcode ORDER BY mbrid LIMIT 2',
            [':barcode' => [$barcode, PDO::PARAM_STR]],
        );
        if (count($rows) > 1) {
            throw new HoldRejected('O código de barras está associado a mais de um membro.');
        }
        if ($rows === []) {
            throw new HoldRejected('Código de barras do membro inválido.');
        }

        return [
            'mbrid' => (int) $rows[0]['mbrid'],
            'barcode_nmbr' => (string) $rows[0]['barcode_nmbr'],
            'first_name' => (string) $rows[0]['first_name'],
            'last_name' => (string) $rows[0]['last_name'],
        ];
    }

    /**
     * @return list<array{holdid: int, bibid: int, copyid: int, hold_begin_dt: string, barcode_nmbr: string, status_cd: string, due_back_dt: ?string, title: string, author: string}>
     */
    public function holdsForMember(int $memberId): array
    {
        if ($memberId < 1) {
            throw new \InvalidArgumentException('O identificador do membro é inválido.');
        }

        return $this->fetchAll(
            'SELECT h.holdid, h.bibid, h.copyid, h.hold_begin_dt, '
            . 'c.barcode_nmbr, c.status_cd, c.due_back_dt, b.title, b.author '
            . 'FROM biblio_hold AS h '
            . 'INNER JOIN biblio_copy AS c ON c.bibid = h.bibid AND c.copyid = h.copyid '
            . 'INNER JOIN biblio AS b ON b.bibid = h.bibid '
            . 'WHERE h.mbrid = :mbrid ORDER BY h.hold_begin_dt DESC, h.holdid DESC',
            [':mbrid' => [$memberId, PDO::PARAM_INT]],
        );
    }

    /**
     * @return array{member: array{mbrid: int, barcode_nmbr: string, first_name: string, last_name: string}, holdid: int}
     */
    public function place(string $memberBarcode, string $copyBarcode): array
    {
        if ($memberBarcode === '' || $copyBarcode === '' || strlen($memberBarcode) > 20 || strlen($copyBarcode) > 20) {
            throw new \InvalidArgumentException('Os códigos de barras do membro e do exemplar são obrigatórios.');
        }
        $this->acquireLock();
        $operationError = null;
        try {
            $member = $this->findMember($memberBarcode);
            $copyRows = $this->fetchAll(
                'SELECT bibid, copyid, status_cd, mbrid FROM biblio_copy '
                . 'WHERE barcode_nmbr = :barcode ORDER BY bibid, copyid LIMIT 2',
                [':barcode' => [$copyBarcode, PDO::PARAM_STR]],
            );
            if (count($copyRows) > 1) {
                throw new HoldRejected('O código de barras está associado a mais de um exemplar.');
            }
            $copy = $copyRows[0] ?? null;
            if ($copy === null) {
                throw new HoldRejected('Código de barras do exemplar inválido.');
            }
            if (!in_array($copy['status_cd'], ['out', 'hld'], true)) {
                throw new HoldRejected('Só é possível reservar exemplares emprestados ou já reservados.');
            }
            if ($copy['status_cd'] === 'out' && (int) $copy['mbrid'] === $member['mbrid']) {
                throw new HoldRejected('O membro já está com este exemplar emprestado.');
            }

            $this->execute(
                'INSERT INTO biblio_hold (bibid, copyid, hold_begin_dt, mbrid) '
                . 'VALUES (:bibid, :copyid, CURRENT_TIMESTAMP, :mbrid)',
                [
                    ':bibid' => [(int) $copy['bibid'], PDO::PARAM_INT],
                    ':copyid' => [(int) $copy['copyid'], PDO::PARAM_INT],
                    ':mbrid' => [$member['mbrid'], PDO::PARAM_INT],
                ],
            );
            $holdId = (int) $this->connection->lastInsertId();
            if ($holdId < 1) {
                throw new \RuntimeException('O banco não retornou o identificador da reserva criada.');
            }

            return ['member' => $member, 'holdid' => $holdId];
        } catch (\Throwable $error) {
            $operationError = $error;
            throw $error;
        } finally {
            $this->releaseLockPreservingCause($operationError);
        }
    }

    public function cancel(int $memberId, int $holdId): void
    {
        if ($memberId < 1 || $holdId < 1) {
            throw new \InvalidArgumentException('O identificador do membro ou da reserva é inválido.');
        }
        $this->acquireLock();
        $operationError = null;
        try {
            $this->execute(
                'DELETE FROM biblio_hold WHERE mbrid = :mbrid AND holdid = :holdid',
                [
                    ':mbrid' => [$memberId, PDO::PARAM_INT],
                    ':holdid' => [$holdId, PDO::PARAM_INT],
                ],
                true,
            );
        } catch (\Throwable $error) {
            $operationError = $error;
            throw $error;
        } finally {
            $this->releaseLockPreservingCause($operationError);
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
                'A operação de reserva falhou e o bloqueio também não pôde ser liberado: '
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
    private function execute(string $sql, array $parameters, bool $mustAffectOneRow = false): PDOStatement
    {
        $statement = $this->statement($sql, $parameters);
        if ($mustAffectOneRow && $statement->rowCount() !== 1) {
            throw new HoldRejected('A reserva não existe mais ou já foi removida.');
        }

        return $statement;
    }

    /**
     * @param array<string, array{mixed, int}> $parameters
     */
    private function statement(string $sql, array $parameters): PDOStatement
    {
        $statement = $this->connection->prepare($sql);
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar uma operação de reserva.');
        }
        foreach ($parameters as $name => [$value, $type]) {
            $statement->bindValue($name, $value, $type);
        }
        if (!$statement->execute()) {
            throw new \RuntimeException('Uma operação de reserva falhou no banco de dados.');
        }

        return $statement;
    }
}
