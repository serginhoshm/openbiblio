<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Catalog;

use PDO;
use PDOStatement;

final class CatalogCopyRepository
{
    public function __construct(
        private readonly PDO $connection,
        private readonly string $lockName = 'OpenBiblio',
        private readonly int $lockTimeoutSeconds = 10,
    ) {
        if ($lockName === '' || $lockTimeoutSeconds < 0) {
            throw new \InvalidArgumentException('A configuração de bloqueio do catálogo é inválida.');
        }
    }

    /**
     * @param array<string, string> $customFields
     */
    private function validateCopyFields(array $customFields): void
    {
        $allowedCodes = [];
        foreach ($this->customFieldDefinitions() as $definition) {
            $code = $definition['code'] ?? null;
            if (!is_string($code)) {
                throw new \UnexpectedValueException('A configuração de campos personalizados está inválida.');
            }
            $allowedCodes[$code] = true;
        }
        foreach ($customFields as $code => $value) {
            if (!isset($allowedCodes[$code]) || !is_string($value)) {
                throw new CatalogCopyRejected('Um campo personalizado do exemplar é inválido.');
            }
        }
    }

    /**
     * @return list<array{bibid: int, title: string, author: string}>
     */
    public function searchBibliographies(string $term): array
    {
        if ($term === '' || strlen($term) > 256) {
            throw new \InvalidArgumentException('Informe um termo com até 256 caracteres.');
        }
        $barcodeTerm = trim($term);
        $titleTerm = '%' . $term . '%';

        return $this->fetchAll(
            'SELECT DISTINCT b.bibid, b.title, b.author FROM biblio AS b '
            . 'LEFT JOIN biblio_copy AS c ON c.bibid = b.bibid '
            . 'WHERE b.title LIKE :title OR b.author LIKE :author OR c.barcode_nmbr = :barcode '
            . 'ORDER BY b.title, b.bibid LIMIT 50',
            [
                ':title' => [$titleTerm, PDO::PARAM_STR],
                ':author' => [$titleTerm, PDO::PARAM_STR],
                ':barcode' => [$barcodeTerm, PDO::PARAM_STR],
            ],
        );
    }

    /**
     * @return array{bibid: int, title: string, author: string}|null
     */
    public function findBibliography(int $bibId): ?array
    {
        if ($bibId < 1) {
            throw new \InvalidArgumentException('O identificador do registro bibliográfico é inválido.');
        }
        $rows = $this->fetchAll(
            'SELECT bibid, title, author FROM biblio WHERE bibid = :bibid',
            [':bibid' => [$bibId, PDO::PARAM_INT]],
        );
        if (count($rows) > 1) {
            throw new \UnexpectedValueException('O registro bibliográfico não é único.');
        }
        if ($rows === []) {
            return null;
        }

        return [
            'bibid' => (int) $rows[0]['bibid'],
            'title' => (string) ($rows[0]['title'] ?? ''),
            'author' => (string) ($rows[0]['author'] ?? ''),
        ];
    }

    /**
     * @return list<array{copyid: int, barcode_nmbr: string, copy_desc: ?string, status_cd: string}>
     */
    public function copies(int $bibId): array
    {
        if ($bibId < 1) {
            throw new \InvalidArgumentException('O identificador do registro bibliográfico é inválido.');
        }

        return $this->fetchAll(
            'SELECT copyid, barcode_nmbr, copy_desc, status_cd FROM biblio_copy '
            . 'WHERE bibid = :bibid ORDER BY copyid',
            [':bibid' => [$bibId, PDO::PARAM_INT]],
        );
    }

    /**
     * @return array{copyid: int, barcode_nmbr: string, copy_desc: ?string, status_cd: string}|null
     */
    public function findCopy(int $bibId, int $copyId): ?array
    {
        if ($bibId < 1 || $copyId < 1) {
            throw new \InvalidArgumentException('Os identificadores bibliográficos do exemplar são inválidos.');
        }
        $rows = $this->fetchAll(
            'SELECT copyid, barcode_nmbr, copy_desc, status_cd FROM biblio_copy '
            . 'WHERE bibid = :bibid AND copyid = :copyid',
            [
                ':bibid' => [$bibId, PDO::PARAM_INT],
                ':copyid' => [$copyId, PDO::PARAM_INT],
            ],
        );
        if (count($rows) > 1) {
            throw new \UnexpectedValueException('O exemplar não é único dentro do registro bibliográfico.');
        }

        return $rows[0] ?? null;
    }

    /**
     * @return list<array{code: string, description: string}>
     */
    public function customFieldDefinitions(): array
    {
        return $this->fetchAll(
            'SELECT code, description FROM biblio_copy_fields_dm ORDER BY code',
            [],
        );
    }

    /**
     * @return array<string, string>
     */
    public function customFieldValues(int $bibId, int $copyId): array
    {
        if ($bibId < 1 || $copyId < 1) {
            throw new \InvalidArgumentException('Os identificadores bibliográficos do exemplar são inválidos.');
        }
        $rows = $this->fetchAll(
            'SELECT code, data FROM biblio_copy_fields WHERE bibid = :bibid AND copyid = :copyid',
            [
                ':bibid' => [$bibId, PDO::PARAM_INT],
                ':copyid' => [$copyId, PDO::PARAM_INT],
            ],
        );
        $values = [];
        foreach ($rows as $row) {
            if (!is_string($row['code'] ?? null) || !is_string($row['data'] ?? null)) {
                throw new \UnexpectedValueException('O banco retornou campos personalizados inválidos para o exemplar.');
            }
            $values[$row['code']] = $row['data'];
        }

        return $values;
    }

    /**
     * @param array<string, string> $customFields
     */
    public function updateCopy(
        int $bibId,
        int $copyId,
        string $barcode,
        string $description,
        array $customFields,
    ): void {
        if ($bibId < 1 || $copyId < 1 || $barcode === ''
            || strlen($barcode) > 20 || strlen($description) > 160) {
            throw new \InvalidArgumentException('Os dados do exemplar são inválidos.');
        }
        $this->acquireLock();
        $operationError = null;
        try {
            if ($this->findCopy($bibId, $copyId) === null) {
                throw new CatalogCopyRejected('O exemplar solicitado não existe mais.');
            }
            $this->validateCopyFields($customFields);

            $duplicates = $this->fetchAll(
                'SELECT bibid, copyid FROM biblio_copy '
                . 'WHERE barcode_nmbr = :barcode AND NOT (bibid = :bibid AND copyid = :copyid) LIMIT 1',
                [
                    ':barcode' => [$barcode, PDO::PARAM_STR],
                    ':bibid' => [$bibId, PDO::PARAM_INT],
                    ':copyid' => [$copyId, PDO::PARAM_INT],
                ],
            );
            if ($duplicates !== []) {
                throw new CatalogCopyRejected('Este código de barras já está associado a outro exemplar.');
            }
            $this->execute(
                'UPDATE biblio_copy SET barcode_nmbr = :barcode, copy_desc = :copy_desc '
                . 'WHERE bibid = :bibid AND copyid = :copyid',
                [
                    ':barcode' => [$barcode, PDO::PARAM_STR],
                    ':copy_desc' => [
                        $description === '' ? null : $description,
                        $description === '' ? PDO::PARAM_NULL : PDO::PARAM_STR,
                    ],
                    ':bibid' => [$bibId, PDO::PARAM_INT],
                    ':copyid' => [$copyId, PDO::PARAM_INT],
                ],
            );
            $this->replaceCopyFields($bibId, $copyId, $customFields);
        } catch (\Throwable $error) {
            $operationError = $error;
            throw $error;
        } finally {
            $this->releaseLockPreservingCause($operationError);
        }
    }

    /**
     * @return array{status_cd: string, holds: int}|null
     */
    public function copyDeletionBlockers(int $bibId, int $copyId): ?array
    {
        $copy = $this->findCopy($bibId, $copyId);
        if ($copy === null) {
            return null;
        }
        $holds = $this->fetchAll(
            'SELECT COUNT(*) AS row_count FROM biblio_hold WHERE bibid = :bibid AND copyid = :copyid',
            [
                ':bibid' => [$bibId, PDO::PARAM_INT],
                ':copyid' => [$copyId, PDO::PARAM_INT],
            ],
        );
        if (!is_numeric($holds[0]['row_count'] ?? null)) {
            throw new \UnexpectedValueException('O banco retornou uma contagem inválida de reservas.');
        }

        return ['status_cd' => $copy['status_cd'], 'holds' => (int) $holds[0]['row_count']];
    }

    public function deleteCopy(int $bibId, int $copyId): void
    {
        if ($bibId < 1 || $copyId < 1) {
            throw new \InvalidArgumentException('Os identificadores bibliográficos do exemplar são inválidos.');
        }
        $this->acquireLock();
        $operationError = null;
        try {
            $blockers = $this->copyDeletionBlockers($bibId, $copyId);
            if ($blockers === null) {
                throw new CatalogCopyRejected('O exemplar solicitado não existe mais.');
            }
            if (in_array($blockers['status_cd'], ['out', 'hld', 'crt'], true) || $blockers['holds'] > 0) {
                throw new CatalogCopyRejected(
                    'Não é possível remover um exemplar emprestado, reservado ou em processamento.',
                );
            }
            foreach ([
                'DELETE FROM biblio_copy_fields WHERE bibid = :bibid AND copyid = :copyid',
                'DELETE FROM biblio_status_hist WHERE bibid = :bibid AND copyid = :copyid',
                'DELETE FROM biblio_copy WHERE bibid = :bibid AND copyid = :copyid',
            ] as $query) {
                $this->execute(
                    $query,
                    [
                        ':bibid' => [$bibId, PDO::PARAM_INT],
                        ':copyid' => [$copyId, PDO::PARAM_INT],
                    ],
                );
            }
        } catch (\Throwable $error) {
            $operationError = $error;
            throw $error;
        } finally {
            $this->releaseLockPreservingCause($operationError);
        }
    }

    /**
     * @return array{copyid: int, barcode_nmbr: string}
     */
    public function createCopy(
        int $bibId,
        string $barcode,
        string $description,
        bool $autoBarcode = false,
        ?array $customFields = null,
    ): array {
        if ($bibId < 1 || strlen($description) > 160 || (!$autoBarcode && $barcode === '')) {
            throw new \InvalidArgumentException('Os dados do exemplar são inválidos.');
        }
        if (strlen($barcode) > 20) {
            throw new \InvalidArgumentException('O código de barras não pode exceder 20 caracteres.');
        }

        $this->acquireLock();
        $operationError = null;
        try {
            if ($this->findBibliography($bibId) === null) {
                throw new CatalogCopyRejected('O registro bibliográfico solicitado não existe.');
            }
            if ($customFields !== null) {
                $this->validateCopyFields($customFields);
            }
            if ($autoBarcode) {
                $rows = $this->fetchAll(
                    'SELECT COALESCE(MAX(copyid), 0) + 1 AS next_copyid FROM biblio_copy WHERE bibid = :bibid',
                    [':bibid' => [$bibId, PDO::PARAM_INT]],
                );
                $copyIdCandidate = (int) ($rows[0]['next_copyid'] ?? 0);
                if ($copyIdCandidate < 1) {
                    throw new \RuntimeException('Não foi possível calcular o próximo número de exemplar.');
                }
                $barcode = sprintf('%05d%d', $bibId, $copyIdCandidate);
                if (strlen($barcode) > 20) {
                    throw new CatalogCopyRejected('O código de barras automático excede o limite de 20 caracteres.');
                }
            }

            $duplicates = $this->fetchAll(
                'SELECT bibid, copyid FROM biblio_copy WHERE barcode_nmbr = :barcode LIMIT 1',
                [':barcode' => [$barcode, PDO::PARAM_STR]],
            );
            if ($duplicates !== []) {
                throw new CatalogCopyRejected('Este código de barras já está associado a outro exemplar.');
            }

            $this->execute(
                "INSERT INTO biblio_copy "
                . '(bibid, create_dt, copy_desc, barcode_nmbr, status_cd, status_begin_dt, '
                . 'due_back_dt, mbrid, renewal_count) '
                . "VALUES (:bibid, CURRENT_TIMESTAMP, :copy_desc, :barcode, 'in', CURRENT_TIMESTAMP, NULL, NULL, 0)",
                [
                    ':bibid' => [$bibId, PDO::PARAM_INT],
                    ':copy_desc' => [$description === '' ? null : $description, $description === '' ? PDO::PARAM_NULL : PDO::PARAM_STR],
                    ':barcode' => [$barcode, PDO::PARAM_STR],
                ],
            );
            $copyId = (int) $this->connection->lastInsertId();
            if ($copyId < 1) {
                throw new \RuntimeException('O banco não retornou o identificador do exemplar criado.');
            }
            if ($customFields !== null) {
                $this->replaceCopyFields($bibId, $copyId, $customFields);
            }

            return ['copyid' => $copyId, 'barcode_nmbr' => $barcode];
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
            throw new \RuntimeException('Não foi possível obter o bloqueio exclusivo do catálogo.');
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
                throw new \RuntimeException('Não foi possível liberar o bloqueio do catálogo.');
            }
        } catch (\Throwable $releaseError) {
            if ($operationError === null) {
                throw $releaseError;
            }

            throw new \RuntimeException(
                'A operação de catálogo falhou e o bloqueio também não pôde ser liberado: '
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
    private function execute(string $sql, array $parameters): void
    {
        $this->statement($sql, $parameters);
    }

    /**
     * @param array<string, string> $customFields
     */
    private function replaceCopyFields(int $bibId, int $copyId, array $customFields): void
    {
        $this->execute(
            'DELETE FROM biblio_copy_fields WHERE bibid = :bibid AND copyid = :copyid',
            [
                ':bibid' => [$bibId, PDO::PARAM_INT],
                ':copyid' => [$copyId, PDO::PARAM_INT],
            ],
        );
        foreach ($customFields as $code => $value) {
            if ($value === '') {
                continue;
            }
            $this->execute(
                'INSERT INTO biblio_copy_fields (bibid, copyid, code, data) '
                . 'VALUES (:bibid, :copyid, :code, :data)',
                [
                    ':bibid' => [$bibId, PDO::PARAM_INT],
                    ':copyid' => [$copyId, PDO::PARAM_INT],
                    ':code' => [$code, PDO::PARAM_STR],
                    ':data' => [$value, PDO::PARAM_STR],
                ],
            );
        }
    }

    /**
     * @param array<string, array{mixed, int}> $parameters
     */
    private function statement(string $sql, array $parameters): PDOStatement
    {
        $statement = $this->connection->prepare($sql);
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar uma operação de catálogo.');
        }
        foreach ($parameters as $name => [$value, $type]) {
            $statement->bindValue($name, $value, $type);
        }
        if (!$statement->execute()) {
            throw new \RuntimeException('Uma operação de catálogo falhou no banco de dados.');
        }

        return $statement;
    }
}
