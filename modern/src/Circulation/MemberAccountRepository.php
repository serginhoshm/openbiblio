<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use PDO;
use PDOStatement;

final class MemberAccountRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function memberExists(int $memberId): bool
    {
        $statement = $this->connection->prepare('SELECT mbrid FROM member WHERE mbrid = :mbrid');
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar a consulta do membro.');
        }
        $statement->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
        if (!$statement->execute()) {
            throw new \RuntimeException('A consulta do membro falhou.');
        }

        return $statement->fetch(PDO::FETCH_ASSOC) !== false;
    }

    /**
     * @return list<array{code: string, description: string}>
     */
    public function transactionTypes(): array
    {
        $statement = $this->connection->query(
            'SELECT code, description FROM transaction_type_dm ORDER BY description, code',
        );
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível consultar os tipos de transação.');
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array{transid: int|string, create_dt: string, transaction_type_cd: string, transaction_type_desc: string, amount: string, description: string|null}>
     */
    public function transactions(int $memberId): array
    {
        $statement = $this->connection->prepare(
            'SELECT account.transid, account.create_dt, account.transaction_type_cd, '
            . 'type.description AS transaction_type_desc, account.amount, account.description '
            . 'FROM member_account AS account '
            . 'JOIN transaction_type_dm AS type ON type.code = account.transaction_type_cd '
            . 'WHERE account.mbrid = :mbrid ORDER BY account.create_dt, account.transid',
        );
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar o extrato do membro.');
        }
        $statement->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
        if (!$statement->execute()) {
            throw new \RuntimeException('A consulta do extrato do membro falhou.');
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTransaction(
        int $memberId,
        int $staffUserId,
        string $typeCode,
        string $amount,
        string $description,
    ): void {
        if ($memberId < 1 || $staffUserId < 1) {
            throw new \InvalidArgumentException('Os identificadores do membro e funcionário devem ser positivos.');
        }
        if (!$this->memberExists($memberId)) {
            throw new MemberAccountRejected('O membro selecionado não existe.');
        }

        $typeStatement = $this->connection->prepare(
            'SELECT code FROM transaction_type_dm WHERE code = :code',
        );
        if (!$typeStatement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível validar o tipo de transação.');
        }
        $typeStatement->bindValue(':code', $typeCode, PDO::PARAM_STR);
        if (!$typeStatement->execute()) {
            throw new \RuntimeException('A validação do tipo de transação falhou.');
        }
        $type = $typeStatement->fetch(PDO::FETCH_ASSOC);
        if ($type === false || ($type['code'] ?? null) !== $typeCode) {
            throw new MemberAccountRejected('Selecione um tipo de transação válido.');
        }

        $amount = self::normalizeAmount($amount);
        $description = trim($description);
        if (preg_match('//u', $description) !== 1) {
            throw new MemberAccountRejected('A descrição deve conter texto UTF-8 válido.');
        }
        $descriptionLength = preg_match_all('/./us', $description);
        if ($description === '' || $descriptionLength === false || $descriptionLength > 128) {
            throw new MemberAccountRejected('A descrição é obrigatória e deve ter até 128 caracteres.');
        }
        if (str_starts_with($typeCode, '-')) {
            $amount = '-' . $amount;
        }

        $statement = $this->connection->prepare(
            'INSERT INTO member_account '
            . '(mbrid, create_dt, create_userid, transaction_type_cd, amount, description) '
            . 'VALUES (:mbrid, NOW(), :userid, :type, :amount, :description)',
        );
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar a inclusão da transação.');
        }
        $statement->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
        $statement->bindValue(':userid', $staffUserId, PDO::PARAM_INT);
        $statement->bindValue(':type', $typeCode, PDO::PARAM_STR);
        $statement->bindValue(':amount', $amount, PDO::PARAM_STR);
        $statement->bindValue(':description', $description, PDO::PARAM_STR);
        if (!$statement->execute()) {
            throw new \RuntimeException('Não foi possível incluir a transação na conta do membro.');
        }
    }

    public function deleteTransaction(int $memberId, int $transactionId): void
    {
        if ($memberId < 1 || $transactionId < 1) {
            throw new \InvalidArgumentException('Os identificadores do membro e da transação devem ser positivos.');
        }
        $statement = $this->connection->prepare(
            'DELETE FROM member_account WHERE mbrid = :mbrid AND transid = :transid',
        );
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar a remoção da transação.');
        }
        $statement->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
        $statement->bindValue(':transid', $transactionId, PDO::PARAM_INT);
        if (!$statement->execute()) {
            throw new \RuntimeException('Não foi possível remover a transação da conta do membro.');
        }
        if ($statement->rowCount() !== 1) {
            throw new MemberAccountRejected('A transação não foi encontrada para este membro.');
        }
    }

    private static function normalizeAmount(string $amount): string
    {
        if (preg_match('/\A([0-9]{1,6})(?:\.([0-9]{1,2}))?\z/', trim($amount), $matches) !== 1) {
            throw new MemberAccountRejected('Informe um valor positivo com até duas casas decimais.');
        }
        $whole = ltrim($matches[1], '0');
        $whole = $whole === '' ? '0' : $whole;
        $fraction = str_pad($matches[2] ?? '', 2, '0');
        if ($whole === '0' && $fraction === '00') {
            throw new MemberAccountRejected('O valor deve ser maior que zero.');
        }

        return $whole . '.' . $fraction;
    }
}
