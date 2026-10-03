<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Auth;

use PDO;
use PDOStatement;

final class StaffRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    /**
     * Validate credentials against the legacy lowercase-MD5 representation.
     *
     * @return array<string, mixed>|null
     */
    public function authenticate(string $username, string $password): ?array
    {
        if ($username === '' || $password === '') {
            return null;
        }

        $statement = $this->connection->prepare(
            'SELECT userid, username, first_name, last_name, suspended_flg, '
            . 'admin_flg, circ_flg, circ_mbr_flg, catalog_flg, reports_flg '
            . 'FROM staff '
            . 'WHERE username = LOWER(:username) AND pwd = MD5(LOWER(:password)) '
            . 'LIMIT 1',
        );
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar a autenticação.');
        }

        $statement->bindValue(':username', $username, PDO::PARAM_STR);
        $statement->bindValue(':password', $password, PDO::PARAM_STR);
        if (!$statement->execute()) {
            throw new \RuntimeException('A consulta de autenticação falhou.');
        }
        $staff = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($staff) ? $staff : null;
    }

    public function suspendByUsername(string $username): void
    {
        $statement = $this->connection->prepare(
            "UPDATE staff SET suspended_flg = 'Y' WHERE username = LOWER(:username)",
        );
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar o bloqueio do staff.');
        }

        $statement->bindValue(':username', $username, PDO::PARAM_STR);
        if (!$statement->execute()) {
            throw new \RuntimeException('Não foi possível suspender o staff após tentativas inválidas.');
        }
    }
}
