<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Catalog;

use Closure;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;

final class PublicCatalogSearchController
{
    /**
     * @param Closure(): BarcodeSearchRepository $repositoryFactory
     */
    public function __construct(private readonly Closure $repositoryFactory)
    {
    }

    public function show(Request $request): Response
    {
        $barcode = $request->query['barcode'] ?? '';
        $title = $request->query['title'] ?? '';
        if (!is_string($barcode) || !is_string($title)) {
            return new Response(
                $this->page('', '', '<p>Informe um único código ou título.</p>'),
                400,
            );
        }

        $barcode = trim($barcode);
        $title = trim($title);
        $results = '';
        if ($barcode !== '') {
            $results = $this->renderResults($this->repository()->findByExactBarcode($barcode, true));
        } elseif ($title !== '') {
            $results = $this->renderResults($this->repository()->searchPublicByTitle($title));
        }

        return new Response($this->page($barcode, $title, $results));
    }

    private function repository(): BarcodeSearchRepository
    {
        $repository = ($this->repositoryFactory)();
        if (!$repository instanceof BarcodeSearchRepository) {
            throw new \UnexpectedValueException('A fábrica não retornou um repositório de busca válido.');
        }

        return $repository;
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function renderResults(array $rows): string
    {
        if ($rows === []) {
            return '<p>Nenhum resultado encontrado.</p>';
        }

        $bibliographies = [];
        foreach ($rows as $row) {
            $bibid = $row['bibid'] ?? null;
            if (!is_string($bibid) && !is_int($bibid)) {
                throw new \UnexpectedValueException('O catálogo retornou um identificador bibliográfico inválido.');
            }

            $key = (string) $bibid;
            if (!isset($bibliographies[$key])) {
                $bibliographies[$key] = [
                    'title' => self::escape($row['title'] ?? ''),
                    'author' => self::escape($row['author'] ?? ''),
                    'copies' => [],
                ];
            }

            if (($row['copyid'] ?? null) !== null) {
                $copyBarcode = self::escape($row['barcode_nmbr'] ?? '');
                $status = self::escape($row['status_description'] ?? $row['status_cd'] ?? '');
                $bibliographies[$key]['copies'][] =
                    '<li>Código ' . $copyBarcode . ', situação ' . $status . '</li>';
            }
        }

        $items = '';
        foreach ($bibliographies as $bibliography) {
            $items .= '<li><strong>' . $bibliography['title'] . '</strong>';
            if ($bibliography['author'] !== '') {
                $items .= ' — ' . $bibliography['author'];
            }
            if ($bibliography['copies'] !== []) {
                $items .= '<ul>' . implode('', $bibliography['copies']) . '</ul>';
            }
            $items .= '</li>';
        }

        return '<ul>' . $items . '</ul>';
    }

    private function page(string $barcode, string $title, string $results): string
    {
        return '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Pesquisa do catálogo</title><h1>Pesquisa do catálogo</h1>'
            . '<form method="get" action="/opac">'
            . '<label for="barcode">Código de barras</label> '
            . '<input id="barcode" name="barcode" value="' . self::escape($barcode) . '"> '
            . '<button type="submit">Pesquisar código</button></form>'
            . '<form method="get" action="/opac">'
            . '<label for="title">Título</label> '
            . '<input id="title" name="title" value="' . self::escape($title) . '"> '
            . '<button type="submit">Pesquisar título</button></form>'
            . $results
            . '</html>';
    }

    private static function escape(mixed $value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new \UnexpectedValueException('O catálogo retornou um valor que não pode ser exibido como texto.');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
