<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Catalog;

use PDO;
use PDOStatement;

final class BarcodeSearchRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findByExactBarcode(string $barcode, bool $publicOnly = false): array
    {
        if ($barcode === '') {
            throw new \InvalidArgumentException('O código de barras não pode ser vazio.');
        }

        if ($publicOnly) {
            $columns = 'b.bibid, b.title, b.title_remainder, b.author, '
                . 'c.copyid, c.barcode_nmbr, c.status_cd, status.description AS status_description';
        } else {
            $columns = 'b.*, c.copyid, c.barcode_nmbr, c.status_cd, c.due_back_dt, c.mbrid, '
                . 'status.description AS status_description';
        }

        $sql = 'SELECT ' . $columns . ' '
            . 'FROM biblio AS b '
            . 'INNER JOIN biblio_copy AS c ON c.bibid = b.bibid '
            . 'LEFT JOIN biblio_status_dm AS status ON status.code = c.status_cd '
            . 'WHERE c.barcode_nmbr = :barcode';
        if ($publicOnly) {
            $sql .= " AND b.opac_flg = 'Y'";
        }
        $sql .= ' ORDER BY c.barcode_nmbr ASC';

        $statement = $this->connection->prepare($sql);
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar a consulta de código de barras.');
        }

        $statement->bindValue(':barcode', $barcode, PDO::PARAM_STR);
        if (!$statement->execute()) {
            throw new \RuntimeException('A consulta exata por código de barras falhou.');
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search public bibliographies by every parsed title term.
     *
     * @return list<array<string, mixed>>
     */
    public function searchPublicByTitle(string $searchText): array
    {
        $terms = SearchTerms::fromInput($searchText);
        if ($terms === []) {
            throw new \InvalidArgumentException('Informe ao menos um termo para pesquisar o título.');
        }

        $conditions = [];
        foreach ($terms as $index => $term) {
            $titlePlaceholder = ':title' . $index;
            $remainderPlaceholder = ':remainder' . $index;
            $conditions[] = "(b.title LIKE {$titlePlaceholder} OR b.title_remainder LIKE {$remainderPlaceholder})";
        }

        $sql = 'SELECT b.bibid, b.title, b.title_remainder, b.author, '
            . 'c.copyid, c.barcode_nmbr, c.status_cd, status.description AS status_description '
            . 'FROM biblio AS b '
            . 'LEFT JOIN biblio_copy AS c ON c.bibid = b.bibid '
            . 'LEFT JOIN biblio_status_dm AS status ON status.code = c.status_cd '
            . "WHERE b.opac_flg = 'Y' AND " . implode(' AND ', $conditions)
            . ' ORDER BY b.title ASC';

        $statement = $this->connection->prepare($sql);
        if (!$statement instanceof PDOStatement) {
            throw new \RuntimeException('Não foi possível preparar a pesquisa pública por título.');
        }

        foreach ($terms as $index => $term) {
            $pattern = '%' . $term . '%';
            $statement->bindValue(':title' . $index, $pattern, PDO::PARAM_STR);
            $statement->bindValue(':remainder' . $index, $pattern, PDO::PARAM_STR);
        }
        if (!$statement->execute()) {
            throw new \RuntimeException('A pesquisa pública por título falhou.');
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
