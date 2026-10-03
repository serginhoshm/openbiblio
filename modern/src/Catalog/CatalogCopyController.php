<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Catalog;

use Closure;
use OpenBiblio\Modern\Auth\AccessGuard;
use OpenBiblio\Modern\Auth\SessionStore;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;

final class CatalogCopyController
{
    /**
     * @param Closure(): CatalogCopyRepository $repositoryFactory
     */
    public function __construct(
        private readonly Closure $repositoryFactory,
        private readonly SessionStore $session,
    ) {
    }

    public function search(Request $request): Response
    {
        $access = $this->authorize('/catalog-admin/search');
        if ($access !== null) {
            return $access;
        }
        $term = $request->query['q'] ?? '';
        if (!is_string($term)) {
            return new Response($this->searchPage([], '', 'Informe um único termo de busca.'), 400);
        }
        $term = trim($term);
        if ($term === '') {
            return new Response($this->searchPage([]), 200, ['Cache-Control' => 'no-store']);
        }
        if (strlen($term) > 256) {
            return new Response($this->searchPage([], $term, 'O termo de busca excede 256 caracteres.'), 400);
        }

        $results = $this->repository()->searchBibliographies($term);

        return new Response($this->searchPage($results, $term), 200, ['Cache-Control' => 'no-store']);
    }

    public function copies(Request $request): Response
    {
        $access = $this->authorize('/catalog-admin/copies');
        if ($access !== null) {
            return $access;
        }
        $bibId = self::positiveInt($request->query['bibid'] ?? null);
        if ($bibId === null) {
            return new Response('Informe um identificador bibliográfico válido.', 400);
        }
        $repository = $this->repository();
        $bibliography = $repository->findBibliography($bibId);
        if ($bibliography === null) {
            return new Response('Registro bibliográfico não encontrado.', 404);
        }

        return new Response(
            $this->copiesPage(
                $bibliography,
                $repository->copies($bibId),
                '',
                '',
                $repository->customFieldDefinitions(),
            ),
            200,
            ['Cache-Control' => 'no-store'],
        );
    }

    public function editForm(Request $request): Response
    {
        $access = $this->authorize('/catalog-admin/copies/edit');
        if ($access !== null) {
            return $access;
        }
        $bibId = self::positiveInt($request->query['bibid'] ?? null);
        $copyId = self::positiveInt($request->query['copyid'] ?? null);
        if ($bibId === null || $copyId === null) {
            return new Response('Informe identificadores válidos para o exemplar.', 400);
        }
        $repository = $this->repository();
        $bibliography = $repository->findBibliography($bibId);
        $copy = $repository->findCopy($bibId, $copyId);
        if ($bibliography === null || $copy === null) {
            return new Response('Registro bibliográfico ou exemplar não encontrado.', 404);
        }

        return new Response(
            $this->copyEditPage(
                $bibliography,
                $copy,
                $repository->customFieldDefinitions(),
                $repository->customFieldValues($bibId, $copyId),
            ),
            200,
            ['Cache-Control' => 'no-store'],
        );
    }

    public function updateCopy(Request $request): Response
    {
        $access = $this->authorize('/catalog-admin/copies/edit');
        if ($access !== null) {
            return $access;
        }
        if (!$this->validCsrf($request)) {
            return new Response(
                'Token de formulário inválido. Recarregue a página e tente novamente.',
                400,
                ['Cache-Control' => 'no-store'],
            );
        }
        $bibId = self::positiveInt($request->body['bibid'] ?? null);
        $copyId = self::positiveInt($request->body['copyid'] ?? null);
        $barcode = $request->body['barcode_nmbr'] ?? null;
        $description = $request->body['copy_desc'] ?? null;
        if ($bibId === null || $copyId === null || !is_string($barcode) || !is_string($description)) {
            return new Response('Os dados do exemplar são inválidos.', 400);
        }
        $barcode = trim($barcode);
        $description = trim($description);
        if ($barcode === '' || strlen($barcode) > 20 || strlen($description) > 160) {
            return new Response('O código ou a descrição do exemplar excede o limite permitido.', 400);
        }

        $repository = $this->repository();
        $bibliography = $repository->findBibliography($bibId);
        $copy = $repository->findCopy($bibId, $copyId);
        if ($bibliography === null || $copy === null) {
            return new Response('Registro bibliográfico ou exemplar não encontrado.', 404);
        }
        $definitions = $repository->customFieldDefinitions();
        $customFields = [];
        foreach ($definitions as $definition) {
            $code = $definition['code'] ?? null;
            if (!is_string($code) || $code === '' || strlen($code) > 16) {
                throw new \UnexpectedValueException('A configuração de campos personalizados está inválida.');
            }
            $value = $request->body['custom_' . $code] ?? '';
            if (!is_string($value) || preg_match('//u', $value) !== 1 || strlen($value) > 65535) {
                return new Response('Um campo personalizado do exemplar é inválido.', 400);
            }
            $customFields[$code] = $value;
        }

        try {
            $repository->updateCopy($bibId, $copyId, $barcode, $description, $customFields);
        } catch (CatalogCopyRejected $error) {
            return new Response(
                $this->copyEditPage($bibliography, $copy, $definitions, $customFields, $error->getMessage()),
                422,
                ['Cache-Control' => 'no-store'],
            );
        }

        return new Response('', 303, ['Location' => '/catalog-admin/copies?bibid=' . $bibId]);
    }

    public function deleteConfirmation(Request $request): Response
    {
        $access = $this->authorize('/catalog-admin/copies/delete');
        if ($access !== null) {
            return $access;
        }
        $bibId = self::positiveInt($request->query['bibid'] ?? null);
        $copyId = self::positiveInt($request->query['copyid'] ?? null);
        if ($bibId === null || $copyId === null) {
            return new Response('Informe identificadores válidos para o exemplar.', 400);
        }
        $repository = $this->repository();
        $bibliography = $repository->findBibliography($bibId);
        $copy = $repository->findCopy($bibId, $copyId);
        $blockers = $repository->copyDeletionBlockers($bibId, $copyId);
        if ($bibliography === null || $copy === null || $blockers === null) {
            return new Response('Registro bibliográfico ou exemplar não encontrado.', 404);
        }

        return new Response($this->copyDeletePage($bibliography, $copy, $blockers), 200, [
            'Cache-Control' => 'no-store',
        ]);
    }

    public function deleteCopy(Request $request): Response
    {
        $access = $this->authorize('/catalog-admin/copies/delete');
        if ($access !== null) {
            return $access;
        }
        if (!$this->validCsrf($request)) {
            return new Response(
                'Token de formulário inválido. Recarregue a página e tente novamente.',
                400,
                ['Cache-Control' => 'no-store'],
            );
        }
        $bibId = self::positiveInt($request->body['bibid'] ?? null);
        $copyId = self::positiveInt($request->body['copyid'] ?? null);
        if ($bibId === null || $copyId === null) {
            return new Response('Informe identificadores válidos para o exemplar.', 400);
        }
        $repository = $this->repository();
        $bibliography = $repository->findBibliography($bibId);
        $copy = $repository->findCopy($bibId, $copyId);
        if ($bibliography === null || $copy === null) {
            return new Response('Registro bibliográfico ou exemplar não encontrado.', 404);
        }
        try {
            $repository->deleteCopy($bibId, $copyId);
        } catch (CatalogCopyRejected $error) {
            $blockers = $repository->copyDeletionBlockers($bibId, $copyId);
            if ($blockers === null) {
                return new Response('Exemplar não encontrado.', 404);
            }

            return new Response(
                $this->copyDeletePage($bibliography, $copy, $blockers, $error->getMessage()),
                409,
                ['Cache-Control' => 'no-store'],
            );
        }

        return new Response('', 303, ['Location' => '/catalog-admin/copies?bibid=' . $bibId]);
    }

    public function create(Request $request): Response
    {
        $access = $this->authorize('/catalog-admin/copies');
        if ($access !== null) {
            return $access;
        }
        $submittedToken = $request->body['_csrf'] ?? null;
        $expectedToken = $this->session->get('auth.csrf');
        if (
            !is_string($submittedToken)
            || !is_string($expectedToken)
            || !hash_equals($expectedToken, $submittedToken)
        ) {
            return new Response(
                'Token de formulário inválido. Recarregue a página e tente novamente.',
                400,
                ['Cache-Control' => 'no-store'],
            );
        }

        $bibId = self::positiveInt($request->body['bibid'] ?? null);
        $barcode = $request->body['barcode_nmbr'] ?? '';
        $description = $request->body['copy_desc'] ?? '';
        $autoBarcode = ($request->body['auto_barcode'] ?? null) === 'Y';
        if (
            $bibId === null
            || !is_string($barcode)
            || !is_string($description)
            || strlen($description) > 160
        ) {
            return new Response('Os dados do exemplar são inválidos.', 400);
        }
        $barcode = trim($barcode);
        $description = trim($description);
        if (!$autoBarcode && ($barcode === '' || strlen($barcode) > 20)) {
            return new Response('Informe um código de barras válido, com até 20 caracteres.', 400);
        }

        $repository = $this->repository();
        $bibliography = $repository->findBibliography($bibId);
        if ($bibliography === null) {
            return new Response('Registro bibliográfico não encontrado.', 404);
        }
        $definitions = $repository->customFieldDefinitions();
        $customFields = [];
        foreach ($definitions as $definition) {
            $code = $definition['code'] ?? null;
            if (!is_string($code) || $code === '' || strlen($code) > 16) {
                throw new \UnexpectedValueException('A configuração de campos personalizados está inválida.');
            }
            $value = $request->body['custom_' . $code] ?? '';
            if (!is_string($value) || preg_match('//u', $value) !== 1 || strlen($value) > 65535) {
                return new Response('Um campo personalizado do exemplar é inválido.', 400);
            }
            $customFields[$code] = $value;
        }
        try {
            $created = $repository->createCopy($bibId, $barcode, $description, $autoBarcode, $customFields);
        } catch (CatalogCopyRejected $error) {
            return new Response(
                $this->copiesPage(
                    $bibliography,
                    $repository->copies($bibId),
                    $error->getMessage(),
                    '',
                    $definitions,
                    $customFields,
                ),
                422,
                ['Cache-Control' => 'no-store'],
            );
        }

        return new Response(
            $this->copiesPage(
                $bibliography,
                $repository->copies($bibId),
                '',
                'Exemplar ' . $created['barcode_nmbr'] . ' cadastrado.',
                $definitions,
            ),
            200,
            ['Cache-Control' => 'no-store'],
        );
    }

    private function authorize(string $path): ?Response
    {
        return (new AccessGuard($this->session))->authorize('catalog', $path);
    }

    private function repository(): CatalogCopyRepository
    {
        $repository = ($this->repositoryFactory)();
        if (!$repository instanceof CatalogCopyRepository) {
            throw new \UnexpectedValueException('A fábrica não retornou um repositório de exemplares válido.');
        }

        return $repository;
    }

    /**
     * @param list<array<string, mixed>> $results
     */
    private function searchPage(array $results, string $term = '', string $error = ''): string
    {
        $content = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Busca administrativa — OpenBiblio</title><h1>Localizar registro bibliográfico</h1>';
        if ($error !== '') {
            $content .= '<p role="alert">' . self::escape($error) . '</p>';
        }
        $content .= '<form method="get" action="/catalog-admin/search">'
            . '<label for="q">Título, autor ou código de barras</label> '
            . '<input id="q" name="q" maxlength="256" value="' . self::escape($term) . '" required> '
            . '<button type="submit">Buscar</button></form>';
        if ($term !== '') {
            if ($results === []) {
                $content .= '<p>Nenhum registro encontrado.</p>';
            } else {
                $content .= '<ul>';
                foreach ($results as $row) {
                    $bibId = self::positiveInt($row['bibid'] ?? null);
                    if ($bibId === null) {
                        throw new \UnexpectedValueException('O banco retornou um identificador bibliográfico inválido.');
                    }
                    $title = self::escape($row['title'] ?? '');
                    $author = self::escape($row['author'] ?? '');
                    $content .= '<li><a href="/catalog-admin/copies?bibid=' . $bibId . '">'
                        . $title . '</a> — ' . $author
                        . ' <a href="/catalog-admin/bibliographies/edit?bibid=' . $bibId . '">Editar ficha</a></li>';
                }
                $content .= '</ul>';
            }
        }

        return $content . '</html>';
    }

    /**
     * @param array{bibid: int, title: string, author: string} $bibliography
     * @param list<array<string, mixed>> $copies
     * @param list<array{code: string, description: string}> $fieldDefinitions
     * @param array<string, string> $customValues
     */
    private function copiesPage(
        array $bibliography,
        array $copies,
        string $error = '',
        string $notice = '',
        array $fieldDefinitions = [],
        array $customValues = [],
    ): string {
        $bibId = $bibliography['bibid'];
        $title = self::escape($bibliography['title']);
        $author = self::escape($bibliography['author']);
        $token = self::escape($this->csrfToken());
        $content = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Exemplares — OpenBiblio</title><h1>Exemplares do registro</h1>'
            . '<h2>' . $title . ' — ' . $author . '</h2>'
            . '<p><a href="/catalog-admin/bibliographies/edit?bibid=' . $bibId . '">Editar ficha bibliográfica</a></p>';
        if ($error !== '') {
            $content .= '<p role="alert">' . self::escape($error) . '</p>';
        }
        if ($notice !== '') {
            $content .= '<p role="status">' . self::escape($notice) . '</p>';
        }
        if ($copies === []) {
            $content .= '<p>Este registro ainda não possui exemplares.</p>';
        } else {
            $content .= '<table><thead><tr><th>Código</th><th>Descrição</th><th>Status</th><th>Ação</th></tr></thead><tbody>';
            foreach ($copies as $copy) {
                $copyId = self::positiveInt($copy['copyid'] ?? null);
                if ($copyId === null) {
                    throw new \UnexpectedValueException('O banco retornou um identificador de exemplar inválido.');
                }
                $barcode = self::escape($copy['barcode_nmbr'] ?? '');
                $description = self::escape($copy['copy_desc'] ?? '');
                $status = self::escape($copy['status_cd'] ?? '');
                $content .= '<tr><td><a href="/catalog-admin/copies/edit?bibid=' . $bibId
                    . '&amp;copyid=' . $copyId . '">' . $barcode . '</a></td><td>'
                    . $description . '</td><td>' . $status . '</td><td><a href="/catalog-admin/copies/delete?bibid='
                    . $bibId . '&amp;copyid=' . $copyId . '">Remover</a></td></tr>';
            }
            $content .= '</tbody></table>';
        }
        $content .= '<h2>Cadastrar exemplar</h2>'
            . '<form method="post" action="/catalog-admin/copies">'
            . '<input type="hidden" name="_csrf" value="' . $token . '">'
            . '<input type="hidden" name="bibid" value="' . $bibId . '">'
            . '<label for="barcode_nmbr">Código de barras</label> '
            . '<input id="barcode_nmbr" name="barcode_nmbr" maxlength="20"> '
            . '<label><input type="checkbox" name="auto_barcode" value="Y"> Gerar código automaticamente</label> '
            . '<label for="copy_desc">Descrição</label> '
            . '<input id="copy_desc" name="copy_desc" maxlength="160"> ';
        foreach ($fieldDefinitions as $definition) {
            $code = $definition['code'] ?? null;
            if (!is_string($code) || $code === '' || strlen($code) > 16) {
                throw new \UnexpectedValueException('A configuração de campos personalizados está inválida.');
            }
            $fieldName = 'custom_' . $code;
            $content .= '<label for="' . self::escape($fieldName) . '">'
                . self::escape($definition['description'] ?? '') . '</label> '
                . '<textarea id="' . self::escape($fieldName) . '" name="' . self::escape($fieldName) . '">'
                . self::escape($customValues[$code] ?? '') . '</textarea> ';
        }
        $content .= '<button type="submit">Cadastrar exemplar</button></form>'
            . '<p><a href="/catalog-admin/search">Voltar à busca do catálogo</a></p></html>';

        return $content;
    }

    /**
     * @param array{bibid: int, title: string, author: string} $bibliography
     * @param array{copyid: int, barcode_nmbr: string, copy_desc: ?string, status_cd: string} $copy
     * @param list<array{code: string, description: string}> $definitions
     * @param array<string, string> $customValues
     */
    private function copyEditPage(
        array $bibliography,
        array $copy,
        array $definitions,
        array $customValues,
        string $error = '',
    ): string {
        $bibId = self::positiveInt($bibliography['bibid']);
        $copyId = self::positiveInt($copy['copyid']);
        if ($bibId === null || $copyId === null) {
            throw new \UnexpectedValueException('O banco retornou identificadores bibliográficos inválidos.');
        }
        $content = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Editar exemplar — OpenBiblio</title><h1>Editar exemplar</h1>'
            . '<p>' . self::escape($bibliography['title']) . ' — ' . self::escape($bibliography['author']) . '</p>';
        if ($error !== '') {
            $content .= '<p role="alert">' . self::escape($error) . '</p>';
        }
        $content .= '<form method="post" action="/catalog-admin/copies/edit">'
            . '<input type="hidden" name="_csrf" value="' . self::escape($this->csrfToken()) . '">'
            . '<input type="hidden" name="bibid" value="' . $bibId . '">'
            . '<input type="hidden" name="copyid" value="' . $copyId . '">'
            . '<label for="barcode_nmbr">Código de barras</label> '
            . '<input id="barcode_nmbr" name="barcode_nmbr" maxlength="20" required value="'
            . self::escape($copy['barcode_nmbr']) . '"> '
            . '<label for="copy_desc">Descrição</label> '
            . '<input id="copy_desc" name="copy_desc" maxlength="160" value="'
            . self::escape($copy['copy_desc'] ?? '') . '"> '
            . '<p>Status atual: ' . self::escape($copy['status_cd'])
            . ' (alterações de status são realizadas pelos fluxos de circulação).</p>';
        foreach ($definitions as $definition) {
            $code = $definition['code'] ?? null;
            if (!is_string($code) || $code === '' || strlen($code) > 16) {
                throw new \UnexpectedValueException('A configuração de campos personalizados está inválida.');
            }
            $fieldName = 'custom_' . $code;
            $content .= '<label for="' . self::escape($fieldName) . '">'
                . self::escape($definition['description'] ?? '') . '</label> '
                . '<textarea id="' . self::escape($fieldName) . '" name="' . self::escape($fieldName) . '">'
                . self::escape($customValues[$code] ?? '') . '</textarea> ';
        }

        return $content . '<button type="submit">Salvar alterações</button></form>'
            . '<p><a href="/catalog-admin/copies?bibid=' . $bibId . '">Voltar aos exemplares</a></p></html>';
    }

    /**
     * @param array{bibid: int, title: string, author: string} $bibliography
     * @param array{copyid: int, barcode_nmbr: string, copy_desc: ?string, status_cd: string} $copy
     * @param array{status_cd: string, holds: int} $blockers
     */
    private function copyDeletePage(
        array $bibliography,
        array $copy,
        array $blockers,
        string $error = '',
    ): string {
        $bibId = self::positiveInt($bibliography['bibid']);
        $copyId = self::positiveInt($copy['copyid']);
        if ($bibId === null || $copyId === null) {
            throw new \UnexpectedValueException('O banco retornou identificadores bibliográficos inválidos.');
        }
        $content = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Remover exemplar — OpenBiblio</title><h1>Remover exemplar</h1>'
            . '<p>Registro: ' . self::escape($bibliography['title']) . '</p>'
            . '<p>Código: ' . self::escape($copy['barcode_nmbr']) . ' — status atual: '
            . self::escape($blockers['status_cd']) . '</p>';
        if ($error !== '') {
            $content .= '<p role="alert">' . self::escape($error) . '</p>';
        }
        if (in_array($blockers['status_cd'], ['out', 'hld', 'crt'], true) || $blockers['holds'] > 0) {
            $content .= '<p role="alert">Remoção bloqueada: o exemplar está emprestado, reservado ou em processamento '
                . 'e possui ' . $blockers['holds'] . ' reserva(s) pendente(s).</p>';
        } else {
            $content .= '<p>Confirma a remoção do exemplar e de seu histórico?</p>'
                . '<form method="post" action="/catalog-admin/copies/delete">'
                . '<input type="hidden" name="_csrf" value="' . self::escape($this->csrfToken()) . '">'
                . '<input type="hidden" name="bibid" value="' . $bibId . '">'
                . '<input type="hidden" name="copyid" value="' . $copyId . '">'
                . '<button type="submit">Confirmar remoção</button></form>';
        }

        return $content . '<p><a href="/catalog-admin/copies?bibid=' . $bibId . '">Cancelar</a></p></html>';
    }

    private function validCsrf(Request $request): bool
    {
        $submittedToken = $request->body['_csrf'] ?? null;
        $expectedToken = $this->session->get('auth.csrf');

        return is_string($submittedToken)
            && is_string($expectedToken)
            && hash_equals($expectedToken, $submittedToken);
    }

    private function csrfToken(): string
    {
        $token = $this->session->get('auth.csrf');
        if (!is_string($token) || preg_match('/\A[a-f0-9]{64}\z/', $token) !== 1) {
            $token = bin2hex(random_bytes(32));
            $this->session->set('auth.csrf', $token);
        }

        return $token;
    }

    private static function positiveInt(mixed $value): ?int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }
        if (!is_string($value) || preg_match('/\A[0-9]+\z/', $value) !== 1) {
            return null;
        }
        $parsed = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $parsed === false ? null : $parsed;
    }

    private static function escape(mixed $value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new \UnexpectedValueException('O catálogo retornou um valor inválido para exibição.');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
