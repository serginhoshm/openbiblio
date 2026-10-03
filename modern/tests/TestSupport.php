<?php

declare(strict_types=1);

use OpenBiblio\Modern\Auth\SessionStore;

function assertSame(mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf(
            "Expected %s, got %s.",
            var_export($expected, true),
            var_export($actual, true),
        ));
    }
}

final class CapturingPdo extends PDO
{
    public string $preparedSql = '';

    public CapturingStatement $statement;

    /**
     * @var list<array<string, mixed>>
     */
    private array $rows;

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function __construct(array $rows = [['barcode_nmbr' => 'BK-42']])
    {
        $this->rows = $rows;
        $this->statement = new CapturingStatement($rows);
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $this->preparedSql = $query;
        $this->statement = new CapturingStatement($this->rows);

        return $this->statement;
    }
}

final class CapturingStatement extends PDOStatement
{
    /** @var array<string, array{mixed, int}> */
    public array $bindings = [];

    public bool $executed = false;

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function __construct(private readonly array $rows)
    {
    }

    public function bindValue(string|int $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        $this->bindings[$param] = [$value, $type];

        return true;
    }

    public function execute(?array $params = null): bool
    {
        $this->executed = true;

        return true;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return $this->rows;
    }

    public function fetch(
        int $mode = PDO::FETCH_DEFAULT,
        int $cursorOrientation = PDO::FETCH_ORI_NEXT,
        int $cursorOffset = 0,
    ): mixed {
        return $this->rows[0] ?? false;
    }
}

final class ArraySessionStore implements SessionStore
{
    public bool $regenerated = false;

    public bool $destroyed = false;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(private array $values = [])
    {
    }

    public function get(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->values[$key]);
    }

    public function regenerate(): void
    {
        $this->regenerated = true;
    }

    public function destroy(): void
    {
        $this->values = [];
        $this->destroyed = true;
    }
}

final class ScriptedPdo extends PDO
{
    /**
     * @var list<string>
     */
    public array $queries = [];

    public string $insertId = '12';

    /**
     * @var array<string, array<string, array{mixed, int}>>
     */
    public array $bindings = [];

    /**
     * @param Closure(string): array{rows?: list<array<string, mixed>>, affected?: int} $resolver
     */
    public function __construct(private readonly Closure $resolver)
    {
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $this->queries[] = $query;
        $result = ($this->resolver)($query);

        return new ScriptedStatement(
            $result['rows'] ?? [],
            $result['affected'] ?? 1,
            function (array $bindings) use ($query): void {
                $this->bindings[$query] = $bindings;
            },
        );
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $this->queries[] = $query;
        $result = ($this->resolver)($query);

        return new ScriptedStatement(
            $result['rows'] ?? [],
            $result['affected'] ?? 1,
        );
    }

    public function lastInsertId(?string $name = null): string|false
    {
        return $this->insertId;
    }
}

final class ScriptedStatement extends PDOStatement
{
    /** @var array<string, array{mixed, int}> */
    public array $bindings = [];

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function __construct(
        private readonly array $rows,
        private readonly int $affected,
        private readonly ?Closure $onExecute = null,
    ) {
    }

    public function bindValue(string|int $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        $this->bindings[$param] = [$value, $type];

        return true;
    }

    public function execute(?array $params = null): bool
    {
        if ($this->onExecute !== null) {
            ($this->onExecute)($this->bindings);
        }

        return true;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return $this->rows;
    }

    public function fetch(
        int $mode = PDO::FETCH_DEFAULT,
        int $cursorOrientation = PDO::FETCH_ORI_NEXT,
        int $cursorOffset = 0,
    ): mixed {
        return $this->rows[0] ?? false;
    }

    public function rowCount(): int
    {
        return $this->affected;
    }
}

/**
 * @param array<string, mixed>|null $member
 * @param array<string, mixed>|null $copy
 * @param array<string, mixed> $settings
 * @param array<string, mixed>|null $hold
 */
function checkoutFixture(
    ?array $member = [
        'mbrid' => 9,
        'barcode_nmbr' => 'M-9',
        'first_name' => 'Ana',
        'last_name' => 'Silva',
        'classification' => 1,
    ],
    ?array $copy = [
        'bibid' => 42,
        'copyid' => 7,
        'barcode_nmbr' => 'B-7',
        'status_cd' => 'in',
        'status_begin_dt' => '2020-01-01 00:00:00',
        'due_back_dt' => null,
        'mbrid' => null,
        'renewal_count' => 0,
        'material_cd' => 1,
        'days_due_back' => 14,
        'checkout_limit' => 10,
        'renewal_limit' => 2,
        'overdue' => 0,
    ],
    array $settings = ['block_checkouts_when_fines_due' => 'N', 'balance' => 0, 'hold_max_days' => 0],
    int $currentCheckoutCount = 0,
    ?array $hold = null,
): ScriptedPdo {
    return new ScriptedPdo(static function (string $query) use (
        $member,
        $copy,
        $settings,
        $currentCheckoutCount,
        $hold,
    ): array {
        if (str_contains($query, 'GET_LOCK(')) {
            return ['rows' => [['acquired' => 1]]];
        }

        if (str_contains($query, 'RELEASE_LOCK(')) {
            return ['rows' => [['released' => 1]]];
        }
        if (str_contains($query, 'FROM member WHERE barcode_nmbr')) {
            return ['rows' => $member === null ? [] : [$member]];
        }
        if (str_contains($query, 'FROM settings')) {
            return ['rows' => [$settings]];
        }
        if (str_contains($query, 'SELECT COUNT(*) AS row_count')) {
            return ['rows' => [['row_count' => $currentCheckoutCount]]];
        }
        if (str_contains($query, 'FROM biblio_copy AS c')) {
            return ['rows' => $copy === null ? [] : [$copy]];
        }
        if (str_contains($query, 'FROM biblio_hold')) {
            return ['rows' => $hold === null ? [] : [$hold]];
        }
        if (str_contains($query, 'DATE_ADD(CURRENT_DATE')) {
            return ['rows' => [['due_back_dt' => '2026-10-16']]];
        }
        if (str_contains($query, 'CURRENT_TIMESTAMP AS status_begin_dt')) {
            return ['rows' => [['status_begin_dt' => '2026-10-02 12:00:00']]];
        }

        return ['affected' => 1];
    });
}

/**
 * @param array<string, mixed>|null $copy
 * @param list<array<string, mixed>> $cart
 */
function checkinFixture(
    ?array $copy = [
        'bibid' => 42,
        'copyid' => 7,
        'barcode_nmbr' => 'B-7',
        'status_cd' => 'out',
        'status_begin_dt' => '2026-09-01 00:00:00',
        'due_back_dt' => '2026-09-30',
        'mbrid' => 9,
        'renewal_count' => 1,
        'late_days' => 2,
        'daily_late_fee' => '0.75',
        'first_name' => 'Ana',
        'last_name' => 'Silva',
    ],
    array $cart = [],
    array $holds = [],
): ScriptedPdo {
    return new ScriptedPdo(static function (string $query) use ($copy, $cart, $holds): array {
        if (str_contains($query, 'GET_LOCK(')) {
            return ['rows' => [['acquired' => 1]]];
        }
        if (str_contains($query, 'RELEASE_LOCK(')) {
            return ['rows' => [['released' => 1]]];
        }
        if (str_contains($query, 'CURRENT_TIMESTAMP AS status_begin_dt')) {
            return ['rows' => [['status_begin_dt' => '2026-10-02 12:00:00']]];
        }
        if (str_contains($query, 'FROM biblio_copy AS c') && str_contains($query, 'daily_late_fee')) {
            return ['rows' => $copy === null ? [] : [$copy]];
        }
        if (str_contains($query, 'FROM biblio_hold')) {
            return ['rows' => $holds];
        }
        if (str_contains($query, 'WHERE c.status_cd =')) {
            return ['rows' => $cart];
        }
        if (str_contains($query, 'WHERE status_cd =')) {
            return ['rows' => $cart];
        }
        if (str_contains($query, 'WHERE bibid = :bibid AND copyid = :copyid AND status_cd')) {
            return ['rows' => $cart !== [] ? [$cart[0]] : [['bibid' => 42, 'copyid' => 7]]];
        }

        return ['affected' => 1];
    });
}

/**
 * @param array<string, mixed>|null $member
 * @param array<string, mixed>|null $copy
 * @param list<array<string, mixed>> $holds
 */
function holdFixture(
    ?array $member = [
        'mbrid' => 9,
        'barcode_nmbr' => 'M-9',
        'first_name' => 'Ana',
        'last_name' => 'Silva',
    ],
    ?array $copy = [
        'bibid' => 42,
        'copyid' => 7,
        'barcode_nmbr' => 'B-7',
        'status_cd' => 'out',
        'mbrid' => 6,
    ],
    array $holds = [],
): ScriptedPdo {
    return new ScriptedPdo(static function (string $query) use ($member, $copy, $holds): array {
        if (str_contains($query, 'GET_LOCK(')) {
            return ['rows' => [['acquired' => 1]]];
        }
        if (str_contains($query, 'RELEASE_LOCK(')) {
            return ['rows' => [['released' => 1]]];
        }
        if (str_contains($query, 'FROM member WHERE barcode_nmbr')) {
            return ['rows' => $member === null ? [] : [$member]];
        }
        if (str_contains($query, 'FROM biblio_copy') && str_contains($query, 'WHERE barcode_nmbr')) {
            return ['rows' => $copy === null ? [] : [$copy]];
        }
        if (str_contains($query, 'FROM biblio_hold AS h')) {
            return ['rows' => $holds];
        }

        return ['affected' => 1];
    });
}

/**
 * @param list<array<string, mixed>> $searchResults
 * @param array<string, mixed>|null $bibliography
 * @param list<array<string, mixed>> $copies
 */
function catalogCopyFixture(
    array $searchResults = [],
    ?array $bibliography = ['bibid' => 42, 'title' => 'Book', 'author' => 'Author'],
    array $copies = [],
    array $duplicates = [],
    array $customDefinitions = [],
    array $customValues = [],
    int $copyHolds = 0,
): ScriptedPdo {
    return new ScriptedPdo(static function (string $query) use (
        $searchResults,
        $bibliography,
        $copies,
        $duplicates,
        $customDefinitions,
        $customValues,
        $copyHolds,
    ): array {
        if (str_contains($query, 'GET_LOCK(')) {
            return ['rows' => [['acquired' => 1]]];
        }
        if (str_contains($query, 'RELEASE_LOCK(')) {
            return ['rows' => [['released' => 1]]];
        }
        if (str_contains($query, 'MAX(copyid)')) {
            return ['rows' => [['next_copyid' => count($copies) + 1]]];
        }
        if (str_contains($query, 'SELECT bibid, copyid FROM biblio_copy WHERE barcode_nmbr')) {
            return ['rows' => $duplicates];
        }
        if (str_contains($query, 'SELECT DISTINCT b.bibid')) {
            return ['rows' => $searchResults];
        }
        if (str_contains($query, 'SELECT code, description FROM biblio_copy_fields_dm')) {
            return ['rows' => $customDefinitions];
        }
        if (str_contains($query, 'SELECT code, data FROM biblio_copy_fields')) {
            $rows = [];
            foreach ($customValues as $code => $data) {
                $rows[] = ['code' => $code, 'data' => $data];
            }

            return ['rows' => $rows];
        }
        if (str_contains($query, 'SELECT COUNT(*) AS row_count FROM biblio_hold WHERE bibid')) {
            return ['rows' => [['row_count' => $copyHolds]]];
        }
        if (str_contains($query, 'FROM biblio WHERE bibid')) {
            return ['rows' => $bibliography === null ? [] : [$bibliography]];
        }
        if (str_contains($query, 'FROM biblio_copy WHERE bibid')) {
            return ['rows' => $copies];
        }

        return ['affected' => 1];
    });
}

/**
 * @param list<array<string, mixed>> $existingFields
 * @param list<array<string, mixed>> $requiredFields
 */
function bibliographyFixture(
    ?array $bibliography = [
        'bibid' => 42,
        'material_cd' => 1,
        'collection_cd' => 2,
        'call_nmbr1' => 'QA76',
        'call_nmbr2' => '',
        'call_nmbr3' => '',
        'title' => 'Book',
        'title_remainder' => '',
        'responsibility_stmt' => '',
        'author' => 'Author',
        'topic1' => '',
        'topic2' => '',
        'topic3' => '',
        'topic4' => '',
        'topic5' => '',
        'opac_flg' => 'Y',
    ],
    array $existingFields = [['fieldid' => 8, 'tag' => 520, 'ind1_cd' => null, 'ind2_cd' => null, 'subfield_cd' => 'a', 'field_data' => 'Description']],
    array $requiredFields = [],
    int $copyCount = 0,
    int $holdCount = 0,
): ScriptedPdo {
    return new ScriptedPdo(static function (string $query) use (
        $bibliography,
        $existingFields,
        $requiredFields,
        $copyCount,
        $holdCount,
    ): array {
        if (str_contains($query, 'GET_LOCK(')) {
            return ['rows' => [['acquired' => 1]]];
        }
        if (str_contains($query, 'RELEASE_LOCK(')) {
            return ['rows' => [['released' => 1]]];
        }
        if (str_contains($query, 'FROM material_type_dm')) {
            return ['rows' => [['code' => 1, 'description' => 'Livro', 'default_flg' => 'Y']]];
        }
        if (str_contains($query, 'FROM collection_dm')) {
            return ['rows' => [['code' => 2, 'description' => 'Geral', 'default_flg' => 'Y']]];
        }
        if (str_contains($query, 'FROM material_usmarc_xref')) {
            return ['rows' => $requiredFields];
        }
        if (str_contains($query, 'SELECT COUNT(*) AS row_count FROM biblio_copy')) {
            return ['rows' => [['row_count' => $copyCount]]];
        }
        if (str_contains($query, 'SELECT COUNT(*) AS row_count FROM biblio_hold')) {
            return ['rows' => [['row_count' => $holdCount]]];
        }
        if (str_contains($query, 'FROM biblio_field WHERE bibid')) {
            return ['rows' => $existingFields];
        }
        if (str_contains($query, 'FROM biblio WHERE bibid')) {
            return ['rows' => $bibliography === null ? [] : [$bibliography]];
        }

        return ['affected' => 1];
    });
}

function memberCreateFixture(
    ?array $duplicate = null,
    array $classifications = [['code' => 1, 'description' => 'Adult']],
    int $nextMemberId = 10,
    ?array $existing = [
        'mbrid' => 9,
        'barcode_nmbr' => 'M-9',
        'last_name' => 'Silva',
        'first_name' => 'Ana',
        'address' => '',
        'home_phone' => '',
        'work_phone' => '',
        'email' => '',
        'classification' => 1,
    ],
    array $customDefinitions = [],
    array $customValues = [],
    int $activeCheckouts = 0,
    int $pendingHolds = 0,
): ScriptedPdo {
    return new ScriptedPdo(static function (string $query) use (
        $duplicate,
        $classifications,
        $nextMemberId,
        $existing,
        $customDefinitions,
        $customValues,
        $activeCheckouts,
        $pendingHolds,
    ): array {
        if (str_contains($query, 'GET_LOCK(')) {
            return ['rows' => [['acquired' => 1]]];
        }
        if (str_contains($query, 'RELEASE_LOCK(')) {
            return ['rows' => [['released' => 1]]];
        }
        if (str_contains($query, 'SELECT code, description FROM mbr_classify_dm')) {
            return ['rows' => $classifications];
        }
        if (str_contains($query, 'SELECT code, description FROM member_fields_dm')) {
            return ['rows' => $customDefinitions];
        }
        if (str_contains($query, 'SELECT code, data FROM member_fields')) {
            $rows = [];
            foreach ($customValues as $code => $data) {
                $rows[] = ['code' => $code, 'data' => $data];
            }

            return ['rows' => $rows];
        }
        if (str_contains($query, "FROM biblio_copy WHERE mbrid = :mbrid AND status_cd = 'out'")) {
            return ['rows' => [['row_count' => $activeCheckouts]]];
        }
        if (str_contains($query, 'FROM biblio_hold WHERE mbrid = :mbrid')) {
            return ['rows' => [['row_count' => $pendingHolds]]];
        }
        if (str_contains($query, 'SELECT code FROM mbr_classify_dm')) {
            return ['rows' => $classifications === [] ? [] : [['code' => $classifications[0]['code']]]];
        }
        if (str_contains($query, 'MAX(mbrid)')) {
            return ['rows' => [['next_mbrid' => $nextMemberId]]];
        }
        if (str_contains($query, 'FROM member WHERE barcode_nmbr')) {
            return ['rows' => $duplicate === null ? [] : [$duplicate]];
        }
        if (str_contains($query, 'FROM member WHERE mbrid')) {
            return ['rows' => $existing === null ? [] : [$existing]];
        }

        return ['affected' => 1];
    });
}

/**
 * @param list<array{code: string, description: string}> $types
 * @param list<array{transid: int|string, create_dt: string, transaction_type_cd: string, transaction_type_desc: string, amount: string, description: string|null}> $transactions
 */
function memberAccountFixture(
    bool $memberExists = true,
    array $types = [
        ['code' => '-p', 'description' => 'Pagamento'],
        ['code' => '+c', 'description' => 'Cobrança'],
    ],
    array $transactions = [],
    int $deleteAffected = 1,
): ScriptedPdo {
    return new ScriptedPdo(static function (string $query) use (
        $memberExists,
        $types,
        $transactions,
        $deleteAffected,
    ): array {
        if (str_contains($query, 'SELECT mbrid FROM member WHERE mbrid')) {
            return ['rows' => $memberExists ? [['mbrid' => 9]] : []];
        }
        if (str_contains($query, 'SELECT code, description FROM transaction_type_dm')) {
            return ['rows' => $types];
        }
        if (str_contains($query, 'SELECT code FROM transaction_type_dm WHERE code')) {
            return ['rows' => $types === [] ? [] : [['code' => $types[0]['code']]]];
        }
        if (str_contains($query, 'FROM member_account AS account')) {
            return ['rows' => $transactions];
        }
        if (str_starts_with($query, 'DELETE FROM member_account')) {
            return ['affected' => $deleteAffected];
        }

        return ['affected' => 1];
    });
}
