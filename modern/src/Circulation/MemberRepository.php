<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use PDO;
use PDOStatement;

final class MemberRepository
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
     * @return array<string, mixed>|null
     */
    public function findByBarcode(string $barcode): ?array
    {
        if ($barcode === '') {
            throw new \InvalidArgumentException('O código de barras do membro não pode ser vazio.');
        }

        $statement = $this->connection->prepare(
            'SELECT mbrid, barcode_nmbr, last_name, first_name, address, home_phone, '
            . 'work_phone, email, classification '
            . 'FROM member WHERE barcode_nmbr = :barcode ORDER BY mbrid ASC LIMIT 2',
        );
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar a busca de membro.');
        }

        $statement->bindValue(':barcode', $barcode, PDO::PARAM_STR);
        if (!$statement->execute()) {
            throw new \RuntimeException('A busca de membro por código de barras falhou.');
        }
        $members = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (count($members) > 1) {
            throw new \UnexpectedValueException('O código de barras está associado a mais de um membro.');
        }

        return $members[0] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $memberId): ?array
    {
        if ($memberId < 1) {
            throw new \InvalidArgumentException('O identificador do membro é inválido.');
        }
        $statement = $this->connection->prepare(
            'SELECT mbrid, barcode_nmbr, last_name, first_name, address, home_phone, '
            . 'work_phone, email, classification '
            . 'FROM member WHERE mbrid = :mbrid',
        );
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar a consulta do membro.');
        }
        $statement->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
        if (!$statement->execute()) {
            throw new \RuntimeException('A consulta do membro falhou.');
        }
        $member = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($member) ? $member : null;
    }

    /**
     * @return list<array{code: int, description: string}>
     */
    public function classifications(): array
    {
        $statement = $this->connection->query(
            'SELECT code, description FROM mbr_classify_dm ORDER BY description, code',
        );
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar a lista de classificações de membros.');
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array{code: string, description: string}>
     */
    public function customFieldDefinitions(): array
    {
        $statement = $this->connection->query(
            'SELECT code, description FROM member_fields_dm ORDER BY code',
        );
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível listar os campos adicionais de membros.');
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, string>
     */
    public function customFieldValues(int $memberId): array
    {
        if ($memberId < 1) {
            throw new \InvalidArgumentException('O identificador do membro é inválido.');
        }
        $statement = $this->connection->prepare(
            'SELECT code, data FROM member_fields WHERE mbrid = :mbrid',
        );
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível consultar os campos adicionais do membro.');
        }
        $statement->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
        if (!$statement->execute()) {
            throw new \RuntimeException('A consulta dos campos adicionais do membro falhou.');
        }
        $values = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (!is_string($row['code'] ?? null) || !is_string($row['data'] ?? null)) {
                throw new \UnexpectedValueException('O banco retornou campos adicionais inválidos.');
            }
            $values[$row['code']] = $row['data'];
        }

        return $values;
    }

    /**
     * @return array{checkouts: int, holds: int}
     */
    public function deletionBlockers(int $memberId): array
    {
        if ($memberId < 1) {
            throw new \InvalidArgumentException('O identificador do membro é inválido.');
        }

        return [
            'checkouts' => $this->countRows(
                "SELECT COUNT(*) AS row_count FROM biblio_copy "
                . "WHERE mbrid = :mbrid AND status_cd = 'out'",
                $memberId,
            ),
            'holds' => $this->countRows(
                'SELECT COUNT(*) AS row_count FROM biblio_hold WHERE mbrid = :mbrid',
                $memberId,
            ),
        ];
    }

    public function delete(int $memberId): void
    {
        if ($memberId < 1) {
            throw new \InvalidArgumentException('O identificador do membro é inválido.');
        }
        $this->acquireLock();
        $operationError = null;
        try {
            if ($this->findById($memberId) === null) {
                throw new MemberRejected('O membro solicitado não existe mais.');
            }
            $blockers = $this->deletionBlockers($memberId);
            if ($blockers['checkouts'] > 0 || $blockers['holds'] > 0) {
                throw new MemberRejected(
                    'Não é possível remover o membro enquanto houver empréstimos ativos ou reservas pendentes.',
                );
            }
            foreach ([
                'DELETE FROM biblio_status_hist WHERE mbrid = :mbrid',
                'DELETE FROM member_account WHERE mbrid = :mbrid',
                'DELETE FROM member_fields WHERE mbrid = :mbrid',
                'DELETE FROM member WHERE mbrid = :mbrid',
            ] as $query) {
                $statement = $this->connection->prepare($query);
                if (!$statement instanceof PDOStatement) {
                    throw new \RuntimeException('Não foi possível preparar a remoção dos dados do membro.');
                }
                $statement->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
                if (!$statement->execute()) {
                    throw new \RuntimeException('A remoção dos dados do membro falhou.');
                }
            }
        } catch (\Throwable $error) {
            $operationError = $error;
            throw $error;
        } finally {
            $this->releaseLockPreservingCause($operationError);
        }
    }

    /**
     * @param array<string, string>|null $customFields
     * @param array{barcode_nmbr: string, last_name: string, first_name: string, address: string, home_phone: string, work_phone: string, email: string, classification: int} $member
     * @return array{mbrid: int, barcode_nmbr: string}
     */
    public function create(
        array $member,
        int $staffUserId,
        bool $generateBarcode,
        ?array $customFields = null,
    ): array {
        if ($staffUserId < 1) {
            throw new \InvalidArgumentException('O usuário da operação é inválido.');
        }

        $this->acquireLock();
        $operationError = null;
        try {
            $classification = $this->connection->prepare(
                'SELECT code FROM mbr_classify_dm WHERE code = :code',
            );
            if (!$classification instanceof PDOStatement) {
                throw new \RuntimeException('Não foi possível verificar a classificação do membro.');
            }
            $classification->bindValue(':code', $member['classification'], PDO::PARAM_INT);
            if (!$classification->execute()) {
                throw new \RuntimeException('Não foi possível verificar a classificação do membro.');
            }
            if ($classification->fetch(PDO::FETCH_ASSOC) === false) {
                throw new MemberRejected('A classificação selecionada não existe.');
            }

            if ($generateBarcode) {
                $next = $this->connection->query(
                    'SELECT COALESCE(MAX(mbrid), 0) + 1 AS next_mbrid FROM member',
                );
                if (!$next instanceof PDOStatement) {
                    throw new \RuntimeException('Não foi possível gerar o código do novo membro.');
                }
                $generated = $next->fetch(PDO::FETCH_ASSOC);
                $nextMemberId = is_array($generated) ? (int) ($generated['next_mbrid'] ?? 0) : 0;
                if ($nextMemberId < 1) {
                    throw new \RuntimeException('O banco não retornou o próximo identificador de membro.');
                }
                $member['barcode_nmbr'] = (string) $nextMemberId;
            }

            $duplicate = $this->connection->prepare(
                'SELECT mbrid FROM member WHERE barcode_nmbr = :barcode ORDER BY mbrid LIMIT 1',
            );
            if (!$duplicate instanceof PDOStatement) {
                throw new \RuntimeException('Não foi possível verificar a duplicidade do código do membro.');
            }
            $duplicate->bindValue(':barcode', $member['barcode_nmbr'], PDO::PARAM_STR);
            if (!$duplicate->execute()) {
                throw new \RuntimeException('Não foi possível verificar a duplicidade do código do membro.');
            }
            if ($duplicate->fetch(PDO::FETCH_ASSOC) !== false) {
                throw new MemberRejected('Este código de barras já está associado a um membro.');
            }

            $insert = $this->connection->prepare(
                'INSERT INTO member '
                . '(barcode_nmbr, create_dt, last_change_dt, last_change_userid, last_name, first_name, '
                . 'address, home_phone, work_phone, email, classification) '
                . 'VALUES (:barcode, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :userid, :last_name, :first_name, '
                . ':address, :home_phone, :work_phone, :email, :classification)',
            );
            if (!$insert instanceof PDOStatement) {
                throw new \RuntimeException('Não foi possível preparar o cadastro de membro.');
            }
            $insert->bindValue(':barcode', $member['barcode_nmbr'], PDO::PARAM_STR);
            $insert->bindValue(':userid', $staffUserId, PDO::PARAM_INT);
            $insert->bindValue(':last_name', $member['last_name'], PDO::PARAM_STR);
            $insert->bindValue(':first_name', $member['first_name'], PDO::PARAM_STR);
            $insert->bindValue(':address', $member['address'], PDO::PARAM_STR);
            $insert->bindValue(':home_phone', $member['home_phone'], PDO::PARAM_STR);
            $insert->bindValue(':work_phone', $member['work_phone'], PDO::PARAM_STR);
            $insert->bindValue(':email', $member['email'], PDO::PARAM_STR);
            $insert->bindValue(':classification', $member['classification'], PDO::PARAM_INT);
            if (!$insert->execute()) {
                throw new \RuntimeException('O cadastro de membro falhou no banco de dados.');
            }

            $memberId = (int) $this->connection->lastInsertId();
            if ($memberId < 1) {
                throw new \RuntimeException('O banco não retornou o identificador do novo membro.');
            }
            if ($customFields !== null) {
                $this->replaceCustomFields($memberId, $customFields);
            }

            return ['mbrid' => $memberId, 'barcode_nmbr' => $member['barcode_nmbr']];
        } catch (\Throwable $error) {
            $operationError = $error;
            throw $error;
        } finally {
            $this->releaseLockPreservingCause($operationError);
        }
    }

    /**
     * @param array{barcode_nmbr: string, last_name: string, first_name: string, address: string, home_phone: string, work_phone: string, email: string, classification: int} $member
     * @param array<string, string>|null $customFields
     */
    public function update(
        array $member,
        int $memberId,
        int $staffUserId,
        ?array $customFields = null,
    ): void
    {
        if ($memberId < 1 || $staffUserId < 1) {
            throw new \InvalidArgumentException('O membro ou usuário da operação é inválido.');
        }
        $this->acquireLock();
        $operationError = null;
        try {
            $existing = $this->findById($memberId);
            if ($existing === null) {
                throw new MemberRejected('O membro solicitado não existe mais.');
            }
            $classification = $this->connection->prepare(
                'SELECT code FROM mbr_classify_dm WHERE code = :code',
            );
            if (!$classification instanceof PDOStatement) {
                throw new \RuntimeException('Não foi possível verificar a classificação do membro.');
            }
            $classification->bindValue(':code', $member['classification'], PDO::PARAM_INT);
            if (!$classification->execute()) {
                throw new \RuntimeException('Não foi possível verificar a classificação do membro.');
            }
            if ($classification->fetch(PDO::FETCH_ASSOC) === false) {
                throw new MemberRejected('A classificação selecionada não existe.');
            }

            $duplicate = $this->connection->prepare(
                'SELECT mbrid FROM member WHERE barcode_nmbr = :barcode AND mbrid <> :mbrid LIMIT 1',
            );
            if (!$duplicate instanceof PDOStatement) {
                throw new \RuntimeException('Não foi possível verificar a duplicidade do código do membro.');
            }
            $duplicate->bindValue(':barcode', $member['barcode_nmbr'], PDO::PARAM_STR);
            $duplicate->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
            if (!$duplicate->execute()) {
                throw new \RuntimeException('Não foi possível verificar a duplicidade do código do membro.');
            }
            if ($duplicate->fetch(PDO::FETCH_ASSOC) !== false) {
                throw new MemberRejected('Este código de barras já está associado a outro membro.');
            }

            $update = $this->connection->prepare(
                'UPDATE member SET last_change_dt = CURRENT_TIMESTAMP, last_change_userid = :userid, '
                . 'barcode_nmbr = :barcode, last_name = :last_name, first_name = :first_name, '
                . 'address = :address, home_phone = :home_phone, work_phone = :work_phone, '
                . 'email = :email, classification = :classification WHERE mbrid = :mbrid',
            );
            if (!$update instanceof PDOStatement) {
                throw new \RuntimeException('Não foi possível preparar a alteração do membro.');
            }
            $update->bindValue(':userid', $staffUserId, PDO::PARAM_INT);
            $update->bindValue(':barcode', $member['barcode_nmbr'], PDO::PARAM_STR);
            $update->bindValue(':last_name', $member['last_name'], PDO::PARAM_STR);
            $update->bindValue(':first_name', $member['first_name'], PDO::PARAM_STR);
            $update->bindValue(':address', $member['address'], PDO::PARAM_STR);
            $update->bindValue(':home_phone', $member['home_phone'], PDO::PARAM_STR);
            $update->bindValue(':work_phone', $member['work_phone'], PDO::PARAM_STR);
            $update->bindValue(':email', $member['email'], PDO::PARAM_STR);
            $update->bindValue(':classification', $member['classification'], PDO::PARAM_INT);
            $update->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
            if (!$update->execute()) {
                throw new \RuntimeException('A alteração do membro falhou no banco de dados.');
            }
            if ($customFields !== null) {
                $this->replaceCustomFields($memberId, $customFields);
            }
        } catch (\Throwable $error) {
            $operationError = $error;
            throw $error;
        } finally {
            $this->releaseLockPreservingCause($operationError);
        }
    }

    private function acquireLock(): void
    {
        $statement = $this->connection->prepare('SELECT GET_LOCK(:lock_name, :timeout) AS acquired');
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar o bloqueio de circulação.');
        }
        $statement->bindValue(':lock_name', $this->lockName, PDO::PARAM_STR);
        $statement->bindValue(':timeout', $this->lockTimeoutSeconds, PDO::PARAM_INT);
        if (!$statement->execute()) {
            throw new \RuntimeException('Não foi possível obter o bloqueio de circulação.');
        }
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($result) || (int) ($result['acquired'] ?? 0) !== 1) {
            throw new \RuntimeException('Não foi possível obter o bloqueio de circulação.');
        }
    }

    private function countRows(string $query, int $memberId): int
    {
        $statement = $this->connection->prepare($query);
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar a verificação de dependências do membro.');
        }
        $statement->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
        if (!$statement->execute()) {
            throw new \RuntimeException('A verificação de dependências do membro falhou.');
        }
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($result) || !is_numeric($result['row_count'] ?? null)) {
            throw new \UnexpectedValueException('O banco retornou uma contagem inválida de dependências.');
        }

        return (int) $result['row_count'];
    }

    /**
     * @param array<string, string> $customFields
     */
    private function replaceCustomFields(int $memberId, array $customFields): void
    {
        $definitions = $this->customFieldDefinitions();
        $allowedCodes = [];
        foreach ($definitions as $definition) {
            $code = $definition['code'] ?? null;
            if (!is_string($code)) {
                throw new \UnexpectedValueException('A configuração dos campos adicionais está inválida.');
            }
            $allowedCodes[$code] = true;
        }
        foreach ($customFields as $code => $value) {
            if (!isset($allowedCodes[$code]) || !is_string($value)) {
                throw new \InvalidArgumentException('Um campo adicional de membro é inválido.');
            }
        }

        $delete = $this->connection->prepare('DELETE FROM member_fields WHERE mbrid = :mbrid');
        if (!$delete instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar a atualização dos campos adicionais.');
        }
        $delete->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
        if (!$delete->execute()) {
            throw new \RuntimeException('Não foi possível remover os valores anteriores dos campos adicionais.');
        }

        foreach ($customFields as $code => $value) {
            if ($value === '') {
                continue;
            }
            $insert = $this->connection->prepare(
                'INSERT INTO member_fields (mbrid, code, data) VALUES (:mbrid, :code, :data)',
            );
            if (!$insert instanceof PDOStatement) {
                throw new \RuntimeException('Não foi possível preparar a gravação de um campo adicional.');
            }
            $insert->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
            $insert->bindValue(':code', $code, PDO::PARAM_STR);
            $insert->bindValue(':data', $value, PDO::PARAM_STR);
            if (!$insert->execute()) {
                throw new \RuntimeException('Não foi possível gravar um campo adicional do membro.');
            }
        }
    }

    private function releaseLockPreservingCause(?\Throwable $operationError): void
    {
        try {
            $statement = $this->connection->prepare('SELECT RELEASE_LOCK(:lock_name) AS released');
            if (!$statement instanceof PDOStatement) {
                throw new \RuntimeException('Não foi possível preparar a liberação do bloqueio de circulação.');
            }
            $statement->bindValue(':lock_name', $this->lockName, PDO::PARAM_STR);
            if (!$statement->execute()) {
                throw new \RuntimeException('Não foi possível liberar o bloqueio de circulação.');
            }
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if (!is_array($result) || (int) ($result['released'] ?? 0) !== 1) {
                throw new \RuntimeException('Não foi possível liberar o bloqueio de circulação.');
            }
        } catch (\Throwable $releaseError) {
            if ($operationError === null) {
                throw $releaseError;
            }
            throw new \RuntimeException(
                'O cadastro do membro falhou e o bloqueio também não pôde ser liberado: '
                . $releaseError->getMessage(),
                0,
                $operationError,
            );
        }
    }
}
