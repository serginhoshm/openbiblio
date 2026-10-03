<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use PDO;
use PDOStatement;

final class MemberHistoryRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    /**
     * @return array{first_name: string, last_name: string}|null
     */
    public function findMember(int $memberId): ?array
    {
        if ($memberId < 1) {
            throw new \InvalidArgumentException('O identificador do membro é inválido.');
        }
        $statement = $this->connection->prepare(
            'SELECT first_name, last_name FROM member WHERE mbrid = :mbrid',
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
     * @return list<array{
     *   barcode_nmbr: string,
     *   title: string,
     *   author: string,
     *   status_description: string,
     *   status_begin_dt: string,
     *   due_back_dt: string|null
     * }>
     */
    public function forMember(int $memberId): array
    {
        if ($memberId < 1) {
            throw new \InvalidArgumentException('O identificador do membro é inválido.');
        }
        $statement = $this->connection->prepare(
            'SELECT copy.barcode_nmbr, biblio.title, biblio.author, status.description AS status_description, '
            . 'history.status_begin_dt, history.due_back_dt '
            . 'FROM biblio_status_hist AS history '
            . 'INNER JOIN biblio ON biblio.bibid = history.bibid '
            . 'INNER JOIN biblio_copy AS copy ON copy.bibid = history.bibid AND copy.copyid = history.copyid '
            . 'INNER JOIN member ON member.mbrid = history.mbrid '
            . 'INNER JOIN biblio_status_dm AS status ON status.code = history.status_cd '
            . 'WHERE history.mbrid = :mbrid ORDER BY history.status_begin_dt DESC',
        );
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar a consulta do histórico do membro.');
        }
        $statement->bindValue(':mbrid', $memberId, PDO::PARAM_INT);
        if (!$statement->execute()) {
            throw new \RuntimeException('A consulta do histórico do membro falhou.');
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
