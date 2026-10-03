<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Catalog;

use PDO;

final class BibliographyRepository
{
    private const CORE_COLUMNS = [
        'title' => 'title',
        'title_remainder' => 'title_remainder',
        'responsibility_stmt' => 'responsibility_stmt',
        'author' => 'author',
        'topic1' => 'topic1',
        'topic2' => 'topic2',
        'topic3' => 'topic3',
        'topic4' => 'topic4',
        'topic5' => 'topic5',
    ];

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
     * @return array<string, mixed>|null
     */
    public function find(int $bibId): ?array
    {
        if ($bibId < 1) {
            throw new \InvalidArgumentException('O identificador bibliográfico é inválido.');
        }
        $record = $this->fetchOne(
            'SELECT bibid, material_cd, collection_cd, call_nmbr1, call_nmbr2, call_nmbr3, '
            . 'title, title_remainder, responsibility_stmt, author, topic1, topic2, topic3, topic4, topic5, opac_flg '
            . 'FROM biblio WHERE bibid = :bibid',
            [':bibid' => [$bibId, PDO::PARAM_INT]],
        );
        if ($record === null) {
            return null;
        }
        if (!is_numeric($record['bibid'] ?? null)
            || !is_numeric($record['material_cd'] ?? null)
            || !is_numeric($record['collection_cd'] ?? null)) {
            throw new \UnexpectedValueException('O banco retornou dados básicos inválidos do registro.');
        }
        $record['bibid'] = (int) $record['bibid'];
        $record['material_cd'] = (int) $record['material_cd'];
        $record['collection_cd'] = (int) $record['collection_cd'];
        $record['opac_flg'] = ($record['opac_flg'] ?? null) === 'Y';
        $record['fields'] = $this->fetchAll(
            'SELECT fieldid, tag, ind1_cd, ind2_cd, subfield_cd, field_data '
            . 'FROM biblio_field WHERE bibid = :bibid ORDER BY tag, subfield_cd, fieldid',
            [':bibid' => [$bibId, PDO::PARAM_INT]],
        );

        return $record;
    }

    /**
     * @return list<array{code: int, description: string, default: bool}>
     */
    public function materials(): array
    {
        return $this->domain('material_type_dm');
    }

    /**
     * @return list<array{code: int, description: string, default: bool}>
     */
    public function collections(): array
    {
        return $this->domain('collection_dm');
    }

    /**
     * @return list<array{tag: int, subfield: string, description: string, required: bool}>
     */
    public function materialFields(int $materialCode): array
    {
        if ($materialCode < 1) {
            throw new \InvalidArgumentException('O tipo de material é inválido.');
        }
        $rows = $this->fetchAll(
            'SELECT tag, subfieldCd, descr, required FROM material_usmarc_xref '
            . 'WHERE materialCd = :material_cd ORDER BY tag, subfieldCd',
            [':material_cd' => [$materialCode, PDO::PARAM_INT]],
        );
        $fields = [];
        foreach ($rows as $row) {
            if (!is_numeric($row['tag'] ?? null)
                || !is_string($row['subfieldCd'] ?? null)
                || !is_string($row['descr'] ?? null)
                || !is_string($row['required'] ?? null)) {
                throw new \UnexpectedValueException('A configuração MARC do material está inválida.');
            }
            $fields[] = [
                'tag' => (int) $row['tag'],
                'subfield' => $row['subfieldCd'],
                'description' => $row['descr'],
                'required' => $row['required'] === 'Y',
            ];
        }

        return $fields;
    }

    /**
     * @param array<string, mixed> $record
     * @param list<array{fieldid: ?int, tag: int, ind1_cd: string, ind2_cd: string, subfield_cd: string, field_data: string}> $fields
     */
    public function create(array $record, array $fields): int
    {
        $this->validateRecord($record, $fields);
        $bibId = 0;
        $this->withLock(function () use ($record, $fields, &$bibId): void {
            $this->assertDomainValue('material_type_dm', (int) $record['material_cd']);
            $this->assertDomainValue('collection_dm', (int) $record['collection_cd']);
            $this->assertRequiredFields($record, $fields);
            $this->execute(
                'INSERT INTO biblio (create_dt, last_change_dt, last_change_userid, material_cd, collection_cd, '
                . 'call_nmbr1, call_nmbr2, call_nmbr3, title, title_remainder, responsibility_stmt, author, '
                . 'topic1, topic2, topic3, topic4, topic5, opac_flg) VALUES '
                . '(CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :userid, :material_cd, :collection_cd, :call1, :call2, '
                . ':call3, :title, :remainder, :responsibility, :author, :topic1, :topic2, :topic3, :topic4, :topic5, :opac)',
                $this->recordParameters($record),
            );
            $bibId = (int) $this->connection->lastInsertId();
            if ($bibId < 1) {
                throw new \RuntimeException('O banco não retornou o identificador do registro bibliográfico.');
            }
            $this->saveFields($bibId, $fields, []);
        });

        return $bibId;
    }

    /**
     * @param array<string, mixed> $record
     * @param list<array{fieldid: ?int, tag: int, ind1_cd: string, ind2_cd: string, subfield_cd: string, field_data: string}> $fields
     */
    public function update(int $bibId, array $record, array $fields): void
    {
        if ($bibId < 1) {
            throw new \InvalidArgumentException('O identificador bibliográfico é inválido.');
        }
        $this->validateRecord($record, $fields);
        $this->withLock(function () use ($bibId, $record, $fields): void {
            if ($this->fetchOne(
                'SELECT bibid FROM biblio WHERE bibid = :bibid',
                [':bibid' => [$bibId, PDO::PARAM_INT]],
            ) === null) {
                throw new BibliographyRejected('O registro bibliográfico solicitado não existe mais.');
            }
            $this->assertDomainValue('material_type_dm', (int) $record['material_cd']);
            $this->assertDomainValue('collection_dm', (int) $record['collection_cd']);
            $this->assertRequiredFields($record, $fields);
            $ownedRows = $this->fetchAll(
                'SELECT fieldid FROM biblio_field WHERE bibid = :bibid',
                [':bibid' => [$bibId, PDO::PARAM_INT]],
            );
            $ownedIds = [];
            foreach ($ownedRows as $row) {
                $ownedIds[] = (int) $row['fieldid'];
            }
            foreach ($fields as $field) {
                if ($field['fieldid'] !== null && !in_array($field['fieldid'], $ownedIds, true)) {
                    throw new BibliographyRejected('Um campo MARC não pertence a este registro.');
                }
            }
            $this->execute(
                'UPDATE biblio SET last_change_dt = CURRENT_TIMESTAMP, last_change_userid = :userid, '
                . 'material_cd = :material_cd, collection_cd = :collection_cd, call_nmbr1 = :call1, '
                . 'call_nmbr2 = :call2, call_nmbr3 = :call3, title = :title, title_remainder = :remainder, '
                . 'responsibility_stmt = :responsibility, author = :author, topic1 = :topic1, topic2 = :topic2, '
                . 'topic3 = :topic3, topic4 = :topic4, topic5 = :topic5, opac_flg = :opac WHERE bibid = :bibid',
                $this->recordParameters($record) + [':bibid' => [$bibId, PDO::PARAM_INT]],
            );
            $this->saveFields($bibId, $fields, $ownedIds);
        });
    }

    /**
     * @return array{copies: int, holds: int}|null
     */
    public function deletionBlockers(int $bibId): ?array
    {
        if ($this->find($bibId) === null) {
            return null;
        }

        return [
            'copies' => $this->countRows('SELECT COUNT(*) AS row_count FROM biblio_copy WHERE bibid = :bibid', $bibId),
            'holds' => $this->countRows('SELECT COUNT(*) AS row_count FROM biblio_hold WHERE bibid = :bibid', $bibId),
        ];
    }

    public function delete(int $bibId): void
    {
        if ($bibId < 1) {
            throw new \InvalidArgumentException('O identificador bibliográfico é inválido.');
        }
        $this->withLock(function () use ($bibId): void {
            $blockers = $this->deletionBlockers($bibId);
            if ($blockers === null) {
                throw new BibliographyRejected('O registro bibliográfico solicitado não existe mais.');
            }
            if ($blockers['copies'] > 0 || $blockers['holds'] > 0) {
                throw new BibliographyRejected(
                    'O registro não pode ser removido enquanto possuir exemplares ou reservas.',
                );
            }
            $this->execute(
                'DELETE FROM biblio_field WHERE bibid = :bibid',
                [':bibid' => [$bibId, PDO::PARAM_INT]],
            );
            $this->execute(
                'DELETE FROM biblio WHERE bibid = :bibid',
                [':bibid' => [$bibId, PDO::PARAM_INT]],
            );
        });
    }

    /**
     * @param array<string, mixed> $record
     * @param list<array<string, mixed>> $fields
     */
    private function validateRecord(array $record, array $fields): void
    {
        foreach (['material_cd', 'collection_cd', 'last_change_userid'] as $key) {
            if (!is_int($record[$key] ?? null) || $record[$key] < 1) {
                throw new \InvalidArgumentException('Os dados básicos do registro bibliográfico são inválidos.');
            }
        }
        foreach (['call_nmbr1', 'call_nmbr2', 'call_nmbr3'] as $key) {
            if (!is_string($record[$key] ?? null) || strlen($record[$key]) > 20) {
                throw new \InvalidArgumentException('A classificação não pode exceder 20 caracteres por campo.');
            }
        }
        if ($record['call_nmbr1'] === '') {
            throw new BibliographyRejected('Informe a primeira parte da classificação do registro.');
        }
        foreach (self::CORE_COLUMNS as $key) {
            if (!is_string($record[$key] ?? null) || preg_match('//u', $record[$key]) !== 1) {
                throw new \InvalidArgumentException('Os campos bibliográficos devem conter texto UTF-8 válido.');
            }
        }
        if (!is_bool($record['opac_flg'] ?? null) || count($fields) > 100) {
            throw new \InvalidArgumentException('Os dados MARC do registro são inválidos.');
        }
        $seenIds = [];
        foreach ($fields as $field) {
            if (!is_array($field)
                || (!is_null($field['fieldid'] ?? null)
                    && (!is_int($field['fieldid']) || $field['fieldid'] < 1))
                || !is_int($field['tag'] ?? null) || $field['tag'] < 0 || $field['tag'] > 999
                || !is_string($field['ind1_cd'] ?? null) || strlen($field['ind1_cd']) > 1
                || !is_string($field['ind2_cd'] ?? null) || strlen($field['ind2_cd']) > 1
                || !is_string($field['subfield_cd'] ?? null) || strlen($field['subfield_cd']) !== 1
                || !is_string($field['field_data'] ?? null) || strlen($field['field_data']) > 65000
                || preg_match('//u', $field['field_data']) !== 1) {
                throw new \InvalidArgumentException('Um campo MARC contém dados inválidos.');
            }
            if ($field['fieldid'] !== null) {
                if (isset($seenIds[$field['fieldid']])) {
                    throw new \InvalidArgumentException('Um campo MARC foi enviado mais de uma vez.');
                }
                $seenIds[$field['fieldid']] = true;
            }
        }
    }

    /**
     * @param list<array<string, mixed>> $fields
     * @param list<int> $ownedIds
     */
    private function saveFields(int $bibId, array $fields, array $ownedIds): void
    {
        $retainedIds = [];
        foreach ($fields as $field) {
            if ($field['fieldid'] !== null) {
                if (!in_array($field['fieldid'], $ownedIds, true)) {
                    throw new BibliographyRejected('Um campo MARC não pertence a este registro.');
                }
                $retainedIds[] = $field['fieldid'];
                $this->execute(
                    'UPDATE biblio_field SET tag = :tag, ind1_cd = :ind1, ind2_cd = :ind2, '
                    . 'subfield_cd = :subfield, field_data = :data WHERE bibid = :bibid AND fieldid = :fieldid',
                    $this->fieldParameters($bibId, $field) + [':fieldid' => [$field['fieldid'], PDO::PARAM_INT]],
                );
            } else {
                $this->execute(
                    'INSERT INTO biblio_field (bibid, tag, ind1_cd, ind2_cd, subfield_cd, field_data) '
                    . 'VALUES (:bibid, :tag, :ind1, :ind2, :subfield, :data)',
                    $this->fieldParameters($bibId, $field),
                );
            }
        }
        $removedIds = array_values(array_diff($ownedIds, $retainedIds));
        if ($removedIds !== []) {
            $placeholders = [];
            $parameters = [':bibid' => [$bibId, PDO::PARAM_INT]];
            foreach ($removedIds as $index => $fieldId) {
                $key = ':fieldid' . $index;
                $placeholders[] = $key;
                $parameters[$key] = [$fieldId, PDO::PARAM_INT];
            }
            $this->execute(
                'DELETE FROM biblio_field WHERE bibid = :bibid AND fieldid IN (' . implode(', ', $placeholders) . ')',
                $parameters,
            );
        }
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, array{mixed, int}>
     */
    private function recordParameters(array $record): array
    {
        $parameters = [
            ':userid' => [$record['last_change_userid'], PDO::PARAM_INT],
            ':material_cd' => [$record['material_cd'], PDO::PARAM_INT],
            ':collection_cd' => [$record['collection_cd'], PDO::PARAM_INT],
            ':call1' => [$record['call_nmbr1'] === '' ? null : $record['call_nmbr1'], $record['call_nmbr1'] === '' ? PDO::PARAM_NULL : PDO::PARAM_STR],
            ':call2' => [$record['call_nmbr2'] === '' ? null : $record['call_nmbr2'], $record['call_nmbr2'] === '' ? PDO::PARAM_NULL : PDO::PARAM_STR],
            ':call3' => [$record['call_nmbr3'] === '' ? null : $record['call_nmbr3'], $record['call_nmbr3'] === '' ? PDO::PARAM_NULL : PDO::PARAM_STR],
            ':opac' => [$record['opac_flg'] ? 'Y' : 'N', PDO::PARAM_STR],
        ];
        foreach (self::CORE_COLUMNS as $key) {
            $parameterName = match ($key) {
                'title_remainder' => ':remainder',
                'responsibility_stmt' => ':responsibility',
                default => ':' . $key,
            };
            $parameters[$parameterName] = [
                $record[$key] === '' ? null : $record[$key],
                $record[$key] === '' ? PDO::PARAM_NULL : PDO::PARAM_STR,
            ];
        }

        return $parameters;
    }

    /**
     * @param array<string, mixed> $field
     * @return array<string, array{mixed, int}>
     */
    private function fieldParameters(int $bibId, array $field): array
    {
        return [
            ':bibid' => [$bibId, PDO::PARAM_INT],
            ':tag' => [$field['tag'], PDO::PARAM_INT],
            ':ind1' => [$field['ind1_cd'] === '' ? null : $field['ind1_cd'], $field['ind1_cd'] === '' ? PDO::PARAM_NULL : PDO::PARAM_STR],
            ':ind2' => [$field['ind2_cd'] === '' ? null : $field['ind2_cd'], $field['ind2_cd'] === '' ? PDO::PARAM_NULL : PDO::PARAM_STR],
            ':subfield' => [$field['subfield_cd'], PDO::PARAM_STR],
            ':data' => [$field['field_data'] === '' ? null : $field['field_data'], $field['field_data'] === '' ? PDO::PARAM_NULL : PDO::PARAM_STR],
        ];
    }

    private function assertDomainValue(string $table, int $code): void
    {
        if (!in_array($table, ['material_type_dm', 'collection_dm'], true)) {
            throw new \LogicException('Tabela de domínio não permitida.');
        }
        if ($this->fetchOne(
            'SELECT code FROM ' . $table . ' WHERE code = :code',
            [':code' => [$code, PDO::PARAM_INT]],
        ) === null) {
            throw new BibliographyRejected('O tipo de material ou a coleção selecionada não existe.');
        }
    }

    private function countRows(string $query, int $bibId): int
    {
        $rows = $this->fetchAll($query, [':bibid' => [$bibId, PDO::PARAM_INT]]);
        if (!is_numeric($rows[0]['row_count'] ?? null)) {
            throw new \UnexpectedValueException('O banco retornou uma contagem inválida do catálogo.');
        }

        return (int) $rows[0]['row_count'];
    }

    /**
     * @param array<string, mixed> $record
     * @param list<array<string, mixed>> $fields
     */
    private function assertRequiredFields(array $record, array $fields): void
    {
        $nativeFields = [
            '100a' => 'author',
            '245a' => 'title',
            '245b' => 'title_remainder',
            '245c' => 'responsibility_stmt',
            '650a' => 'topics',
        ];
        $present = [];
        foreach ($nativeFields as $marcKey => $column) {
            if ($column === 'topics') {
                foreach (['topic1', 'topic2', 'topic3', 'topic4', 'topic5'] as $topic) {
                    if ($record[$topic] !== '') {
                        $present[$marcKey] = true;
                    }
                }
            } elseif ($record[$column] !== '') {
                $present[$marcKey] = true;
            }
        }
        foreach ($fields as $field) {
            $key = sprintf('%03d%s', $field['tag'], $field['subfield_cd']);
            if ($field['field_data'] !== '' && !isset($nativeFields[$key])) {
                $present[$key] = true;
            }
        }
        foreach ($this->materialFields($record['material_cd']) as $field) {
            $key = sprintf('%03d%s', $field['tag'], $field['subfield']);
            if ($field['required'] && !isset($present[$key])) {
                throw new BibliographyRejected(
                    'Preencha o campo MARC obrigatório: ' . $field['description'] . '.',
                );
            }
        }
    }

    /**
     * @return list<array{code: int, description: string, default: bool}>
     */
    private function domain(string $table): array
    {
        if (!in_array($table, ['material_type_dm', 'collection_dm'], true)) {
            throw new \LogicException('Tabela de domínio não permitida.');
        }
        $rows = $this->fetchAll(
            'SELECT code, description, default_flg FROM ' . $table . ' ORDER BY description',
            [],
        );
        foreach ($rows as &$row) {
            if (!is_numeric($row['code'] ?? null) || !is_string($row['description'] ?? null)
                || !is_string($row['default_flg'] ?? null)) {
                throw new \UnexpectedValueException('Uma opção de catálogo retornada pelo banco é inválida.');
            }
            $row['code'] = (int) $row['code'];
            $row['default'] = $row['default_flg'] === 'Y';
            unset($row['default_flg']);
        }
        unset($row);

        return $rows;
    }

    private function withLock(callable $operation): void
    {
        $rows = $this->fetchAll('SELECT GET_LOCK(:lock_name, :timeout) AS acquired', [
            ':lock_name' => [$this->lockName, PDO::PARAM_STR],
            ':timeout' => [$this->lockTimeoutSeconds, PDO::PARAM_INT],
        ]);
        if ((int) ($rows[0]['acquired'] ?? 0) !== 1) {
            throw new \RuntimeException('Não foi possível obter o bloqueio exclusivo do catálogo.');
        }
        $operationError = null;
        try {
            $operation();
        } catch (\Throwable $error) {
            $operationError = $error;
            throw $error;
        } finally {
            try {
                $released = $this->fetchAll('SELECT RELEASE_LOCK(:lock_name) AS released', [
                    ':lock_name' => [$this->lockName, PDO::PARAM_STR],
                ]);
                if ((int) ($released[0]['released'] ?? 0) !== 1) {
                    throw new \RuntimeException('Não foi possível liberar o bloqueio do catálogo.');
                }
            } catch (\Throwable $releaseError) {
                if ($operationError === null) {
                    throw $releaseError;
                }
                throw new \RuntimeException(
                    'A operação bibliográfica falhou e o bloqueio também não pôde ser liberado: '
                    . $releaseError->getMessage(),
                    0,
                    $operationError,
                );
            }
        }
    }

    /**
     * @param array<string, array{mixed, int}> $parameters
     * @return list<array<string, mixed>>
     */
    private function fetchAll(string $sql, array $parameters): array
    {
        $statement = $this->connection->prepare($sql);
        if ($statement === false) {
            throw new \RuntimeException('Não foi possível preparar uma consulta bibliográfica.');
        }
        foreach ($parameters as $key => [$value, $type]) {
            $statement->bindValue($key, $value, $type);
        }
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string, array{mixed, int}> $parameters
     * @return array<string, mixed>|null
     */
    private function fetchOne(string $sql, array $parameters): ?array
    {
        return $this->fetchAll($sql, $parameters)[0] ?? null;
    }

    /**
     * @param array<string, array{mixed, int}> $parameters
     */
    private function execute(string $sql, array $parameters): void
    {
        $this->fetchAll($sql, $parameters);
    }
}
