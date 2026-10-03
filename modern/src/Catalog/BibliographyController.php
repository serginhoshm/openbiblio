<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Catalog;

use Closure;
use OpenBiblio\Modern\Auth\AccessGuard;
use OpenBiblio\Modern\Auth\SessionStore;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;

final class BibliographyController
{
    private const CORE_FIELDS = [
        'title' => '245a',
        'title_remainder' => '245b',
        'responsibility_stmt' => '245c',
        'author' => '100a',
        'topic1' => '650a',
        'topic2' => '650a',
        'topic3' => '650a',
        'topic4' => '650a',
        'topic5' => '650a',
    ];

    /**
     * @param Closure(): BibliographyRepository $repositoryFactory
     */
    public function __construct(
        private readonly Closure $repositoryFactory,
        private readonly SessionStore $session,
    ) {
    }

    public function newForm(): Response
    {
        $access = $this->authorize('/catalog-admin/bibliographies/new');
        if ($access !== null) {
            return $access;
        }
        $repository = $this->repository();
        $record = [
            'bibid' => null,
            'material_cd' => 0,
            'collection_cd' => 0,
            'call_nmbr1' => '',
            'call_nmbr2' => '',
            'call_nmbr3' => '',
            'title' => '',
            'title_remainder' => '',
            'responsibility_stmt' => '',
            'author' => '',
            'topic1' => '',
            'topic2' => '',
            'topic3' => '',
            'topic4' => '',
            'topic5' => '',
            'opac_flg' => true,
            'fields' => [],
        ];
        $materials = $repository->materials();
        $collections = $repository->collections();
        $defaultMaterials = array_values(array_filter(
            $materials,
            static fn (array $material): bool => $material['default'],
        ));
        $defaultCollections = array_values(array_filter(
            $collections,
            static fn (array $collection): bool => $collection['default'],
        ));
        $record['material_cd'] = $defaultMaterials[0]['code'] ?? $materials[0]['code'] ?? 0;
        $record['collection_cd'] = $defaultCollections[0]['code'] ?? $collections[0]['code'] ?? 0;
        if ($record['material_cd'] > 0) {
            return new Response(
                $this->formPage($record, $materials, $collections, $repository->materialFields($record['material_cd'])),
                200,
                ['Cache-Control' => 'no-store'],
            );
        }

        return new Response($this->formPage($record, $materials, $collections), 200, ['Cache-Control' => 'no-store']);
    }

    public function editForm(Request $request): Response
    {
        $access = $this->authorize('/catalog-admin/bibliographies/edit');
        if ($access !== null) {
            return $access;
        }
        $bibId = self::positiveInt($request->query['bibid'] ?? null);
        if ($bibId === null) {
            return new Response('Informe um identificador bibliográfico válido.', 400);
        }
        $repository = $this->repository();
        $record = $repository->find($bibId);
        if ($record === null) {
            return new Response('Registro bibliográfico não encontrado.', 404);
        }
        $materials = $repository->materials();
        $collections = $repository->collections();

        return new Response(
            $this->formPage(
                $record,
                $materials,
                $collections,
                $repository->materialFields($record['material_cd']),
            ),
            200,
            ['Cache-Control' => 'no-store'],
        );
    }

    public function create(Request $request): Response
    {
        $access = $this->authorize('/catalog-admin/bibliographies/new');
        if ($access !== null) {
            return $access;
        }
        if (!$this->validCsrf($request)) {
            return new Response('Token de formulário inválido. Recarregue a página e tente novamente.', 400);
        }
        $parsed = $this->parseSubmission($request, false);
        if ($parsed instanceof Response) {
            return $parsed;
        }
        [$record, $fields] = $parsed;
        $repository = $this->repository();
        $errors = $this->requiredFieldErrors($repository, $record, $fields);
        if ($errors !== []) {
            return $this->invalidSubmission($repository, $record, $fields, $errors);
        }
        try {
            $bibId = $repository->create($record, $fields);
        } catch (BibliographyRejected $error) {
            return $this->invalidSubmission($repository, $record, $fields, [$error->getMessage()]);
        }

        return new Response('', 303, ['Location' => '/catalog-admin/bibliographies/edit?bibid=' . $bibId]);
    }

    public function update(Request $request): Response
    {
        $access = $this->authorize('/catalog-admin/bibliographies/edit');
        if ($access !== null) {
            return $access;
        }
        if (!$this->validCsrf($request)) {
            return new Response('Token de formulário inválido. Recarregue a página e tente novamente.', 400);
        }
        $bibId = self::positiveInt($request->body['bibid'] ?? null);
        if ($bibId === null) {
            return new Response('Informe um identificador bibliográfico válido.', 400);
        }
        $parsed = $this->parseSubmission($request, true);
        if ($parsed instanceof Response) {
            return $parsed;
        }
        [$record, $fields] = $parsed;
        $repository = $this->repository();
        $current = $repository->find($bibId);
        if ($current === null) {
            return new Response('Registro bibliográfico não encontrado.', 404);
        }
        $errors = $this->requiredFieldErrors($repository, $record, $fields);
        if ($errors !== []) {
            $record['bibid'] = $bibId;

            return $this->invalidSubmission($repository, $record, $fields, $errors);
        }
        try {
            $repository->update($bibId, $record, $fields);
        } catch (BibliographyRejected $error) {
            $record['bibid'] = $bibId;

            return $this->invalidSubmission($repository, $record, $fields, [$error->getMessage()]);
        }

        return new Response('', 303, ['Location' => '/catalog-admin/copies?bibid=' . $bibId]);
    }

    public function deleteConfirmation(Request $request): Response
    {
        $access = $this->authorize('/catalog-admin/bibliographies/delete');
        if ($access !== null) {
            return $access;
        }
        $bibId = self::positiveInt($request->query['bibid'] ?? null);
        if ($bibId === null) {
            return new Response('Informe um identificador bibliográfico válido.', 400);
        }
        $repository = $this->repository();
        $record = $repository->find($bibId);
        $blockers = $repository->deletionBlockers($bibId);
        if ($record === null || $blockers === null) {
            return new Response('Registro bibliográfico não encontrado.', 404);
        }

        return new Response(
            $this->deletePage($record, $blockers),
            200,
            ['Cache-Control' => 'no-store'],
        );
    }

    public function delete(Request $request): Response
    {
        $access = $this->authorize('/catalog-admin/bibliographies/delete');
        if ($access !== null) {
            return $access;
        }
        if (!$this->validCsrf($request)) {
            return new Response('Token de formulário inválido. Recarregue a página e tente novamente.', 400);
        }
        $bibId = self::positiveInt($request->body['bibid'] ?? null);
        if ($bibId === null) {
            return new Response('Informe um identificador bibliográfico válido.', 400);
        }
        $repository = $this->repository();
        try {
            $repository->delete($bibId);
        } catch (BibliographyRejected $error) {
            $record = $repository->find($bibId);
            $blockers = $repository->deletionBlockers($bibId);
            if ($record === null || $blockers === null) {
                return new Response('Registro bibliográfico não encontrado.', 404);
            }

            return new Response(
                $this->deletePage($record, $blockers, $error->getMessage()),
                409,
                ['Cache-Control' => 'no-store'],
            );
        }

        return new Response('', 303, ['Location' => '/catalog-admin/search']);
    }

    /**
     * @return array{array<string, mixed>, list<array{fieldid: ?int, tag: int, ind1_cd: string, ind2_cd: string, subfield_cd: string, field_data: string}>}|Response
     */
    private function parseSubmission(Request $request, bool $editing): array|Response
    {
        $body = $request->body;
        if (array_key_exists('opac_flg', $body)
            && (!is_string($body['opac_flg']) || $body['opac_flg'] !== 'Y')) {
            return new Response('O valor de visibilidade no OPAC é inválido.', 400);
        }
        $record = [
            'material_cd' => self::positiveInt($body['material_cd'] ?? null),
            'collection_cd' => self::positiveInt($body['collection_cd'] ?? null),
            'last_change_userid' => self::positiveInt(
                (is_array($this->session->get('auth.user')) ? $this->session->get('auth.user')['userid'] ?? null : null),
            ),
            'call_nmbr1' => $body['call_nmbr1'] ?? '',
            'call_nmbr2' => $body['call_nmbr2'] ?? '',
            'call_nmbr3' => $body['call_nmbr3'] ?? '',
            'opac_flg' => ($body['opac_flg'] ?? null) === 'Y',
        ];
        foreach ([
            'title', 'title_remainder', 'responsibility_stmt', 'author',
            'topic1', 'topic2', 'topic3', 'topic4', 'topic5',
        ] as $key) {
            $record[$key] = $body[$key] ?? '';
        }
        foreach (['call_nmbr1', 'call_nmbr2', 'call_nmbr3'] as $key) {
            if (!is_string($record[$key])) {
                return new Response('Os dados bibliográficos são inválidos.', 400);
            }
            $record[$key] = trim($record[$key]);
        }
        foreach ([
            'title', 'title_remainder', 'responsibility_stmt', 'author',
            'topic1', 'topic2', 'topic3', 'topic4', 'topic5',
        ] as $key) {
            if (!is_string($record[$key]) || preg_match('//u', $record[$key]) !== 1
                || strlen($record[$key]) > 65000) {
                return new Response('Um campo bibliográfico contém texto inválido ou excede o limite.', 400);
            }
            $record[$key] = trim($record[$key]);
        }
        if ($record['material_cd'] === null || $record['collection_cd'] === null
            || $record['last_change_userid'] === null) {
            return new Response('Selecione um tipo de material e uma coleção válidos.', 400);
        }

        $submittedFields = $body['marc_fields'] ?? null;
        if (!is_array($submittedFields) || count($submittedFields) > 100) {
            return new Response('A lista de campos MARC é inválida.', 400);
        }
        $fields = [];
        $totalFieldBytes = 0;
        foreach ($submittedFields as $submitted) {
            if (!is_array($submitted)) {
                return new Response('Um campo MARC foi enviado em formato inválido.', 400);
            }
            $tag = $submitted['tag'] ?? '';
            $subfield = $submitted['subfield_cd'] ?? '';
            $data = $submitted['field_data'] ?? '';
            $fieldIdValue = $submitted['fieldid'] ?? '';
            $fieldId = $fieldIdValue === '' ? null : self::positiveInt($fieldIdValue);
            $ind1 = $submitted['ind1_cd'] ?? '';
            $ind2 = $submitted['ind2_cd'] ?? '';
            if (!is_string($tag) || !is_string($subfield) || !is_string($data)
                || (!is_string($fieldIdValue) && !is_int($fieldIdValue))
                || ($fieldIdValue !== '' && $fieldId === null)
                || !is_string($ind1) || !is_string($ind2)) {
                return new Response('Um campo MARC foi enviado com tipos inválidos.', 400);
            }
            if ($tag === '' && $subfield === '' && trim($data) === '') {
                continue;
            }
            if (preg_match('/\A[0-9]{1,3}\z/', $tag) !== 1 || strlen($subfield) !== 1
                || strlen($ind1) > 1 || strlen($ind2) > 1 || strlen($data) > 65000
                || preg_match('//u', $data) !== 1) {
                return new Response('Informe tag, subcampo e conteúdo MARC válidos.', 400);
            }
            $totalFieldBytes += strlen($data);
            if ($totalFieldBytes > 5_000_000) {
                return new Response('O conjunto de campos MARC excede o limite permitido.', 413);
            }
            $fields[] = [
                'fieldid' => $fieldId,
                'tag' => (int) $tag,
                'ind1_cd' => $ind1,
                'ind2_cd' => $ind2,
                'subfield_cd' => $subfield,
                'field_data' => trim($data),
            ];
        }
        if (!$editing && array_filter($fields, static fn (array $field): bool => $field['fieldid'] !== null) !== []) {
            return new Response('Um novo registro não pode reutilizar identificadores de campos existentes.', 400);
        }

        return [$record, $fields];
    }

    /**
     * @param array<string, mixed> $record
     * @param list<array<string, mixed>> $fields
     * @return list<string>
     */
    private function requiredFieldErrors(BibliographyRepository $repository, array $record, array $fields): array
    {
        $errors = [];
        if ($record['call_nmbr1'] === '') {
            $errors[] = 'Informe a primeira parte da classificação do registro.';
        }
        $present = [];
        foreach (self::CORE_FIELDS as $key => $marcKey) {
            if ($record[$key] !== '') {
                $present[$marcKey] = true;
            }
        }
        foreach ($fields as $field) {
            $key = sprintf('%03d%s', $field['tag'], $field['subfield_cd']);
            if ($field['field_data'] !== '' && !in_array($key, self::CORE_FIELDS, true)) {
                $present[$key] = true;
            }
        }
        foreach ($repository->materialFields($record['material_cd']) as $field) {
            $key = sprintf('%03d%s', $field['tag'], $field['subfield']);
            if ($field['required'] && !isset($present[$key])) {
                $errors[] = 'Preencha o campo MARC obrigatório: ' . $field['description'] . '.';
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $record
     * @param list<array<string, mixed>> $fields
     * @param list<string> $errors
     */
    private function invalidSubmission(
        BibliographyRepository $repository,
        array $record,
        array $fields,
        array $errors,
    ): Response {
        $record['fields'] = $fields;
        $materials = $repository->materials();
        $collections = $repository->collections();
        $fieldDefinitions = $record['material_cd'] > 0
            ? $repository->materialFields($record['material_cd'])
            : [];

        return new Response(
            $this->formPage($record, $materials, $collections, $fieldDefinitions, $errors),
            422,
            ['Cache-Control' => 'no-store'],
        );
    }

    /**
     * @param array<string, mixed> $record
     * @param list<array{code: int, description: string, default: bool}> $materials
     * @param list<array{code: int, description: string, default: bool}> $collections
     * @param list<array{tag: int, subfield: string, description: string, required: bool}> $fieldDefinitions
     * @param list<string> $errors
     */
    private function formPage(
        array $record,
        array $materials,
        array $collections,
        array $fieldDefinitions = [],
        array $errors = [],
    ): string {
        $editing = is_int($record['bibid'] ?? null);
        $bibId = $editing ? $record['bibid'] : null;
        $content = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>' . ($editing ? 'Editar registro' : 'Novo registro') . ' — OpenBiblio</title>'
            . '<h1>' . ($editing ? 'Editar registro bibliográfico' : 'Novo registro bibliográfico') . '</h1>';
        foreach ($errors as $error) {
            $content .= '<p role="alert">' . self::escape($error) . '</p>';
        }
        $content .= '<form method="post" action="' . ($editing
            ? '/catalog-admin/bibliographies/edit'
            : '/catalog-admin/bibliographies/new') . '">'
            . '<input type="hidden" name="_csrf" value="' . self::escape($this->csrfToken()) . '">';
        if ($editing) {
            $content .= '<input type="hidden" name="bibid" value="' . $bibId . '">';
        }
        $content .= '<label for="material_cd">Tipo de material</label> '
            . '<select id="material_cd" name="material_cd" required>';
        foreach ($materials as $material) {
            $content .= '<option value="' . $material['code'] . '"'
                . ($material['code'] === $record['material_cd'] ? ' selected' : '') . '>'
                . self::escape($material['description']) . '</option>';
        }
        $content .= '</select> <label for="collection_cd">Coleção</label> '
            . '<select id="collection_cd" name="collection_cd" required>';
        foreach ($collections as $collection) {
            $content .= '<option value="' . $collection['code'] . '"'
                . ($collection['code'] === $record['collection_cd'] ? ' selected' : '') . '>'
                . self::escape($collection['description']) . '</option>';
        }
        $content .= '</select>';
        foreach ([
            'call_nmbr1' => 'Classificação (parte 1)',
            'call_nmbr2' => 'Classificação (parte 2)',
            'call_nmbr3' => 'Classificação (parte 3)',
            'title' => 'Título (245$a)',
            'title_remainder' => 'Complemento do título (245$b)',
            'responsibility_stmt' => 'Responsabilidade (245$c)',
            'author' => 'Autor principal (100$a)',
            'topic1' => 'Assunto 1 (650$a)',
            'topic2' => 'Assunto 2 (650$a)',
            'topic3' => 'Assunto 3 (650$a)',
            'topic4' => 'Assunto 4 (650$a)',
            'topic5' => 'Assunto 5 (650$a)',
        ] as $key => $label) {
            $maxLength = str_starts_with($key, 'call_nmbr') ? 20 : 65000;
            $content .= '<p><label for="' . $key . '">' . self::escape($label) . '</label> '
                . '<input id="' . $key . '" name="' . $key . '" maxlength="' . $maxLength . '"'
                . ($key === 'call_nmbr1' ? ' required' : '') . ' value="'
                . self::escape($record[$key] ?? '') . '"></p>';
        }
        $content .= '<label><input type="checkbox" name="opac_flg" value="Y"'
            . (($record['opac_flg'] ?? false) ? ' checked' : '') . '> Exibir no catálogo público</label>'
            . '<h2>Campos MARC adicionais</h2>'
            . '<p>Para remover um campo MARC existente, deixe Tag, Subcampo e Conteúdo vazios.</p>';
        foreach ($fieldDefinitions as $definition) {
            if ($definition['required']) {
                $content .= '<p>Obrigatório: ' . self::escape($definition['description']) . ' ('
                    . sprintf('%03d', $definition['tag']) . '$' . self::escape($definition['subfield']) . ')</p>';
            }
        }
        $fields = $record['fields'] ?? [];
        for ($index = 0; $index < count($fields) + 5; $index++) {
            $field = $fields[$index] ?? [
                'fieldid' => null,
                'tag' => '',
                'ind1_cd' => '',
                'ind2_cd' => '',
                'subfield_cd' => '',
                'field_data' => '',
            ];
            $prefix = 'marc_fields[' . $index . ']';
            $content .= '<fieldset><legend>Campo ' . ($index + 1) . '</legend>';
            $fieldId = $field['fieldid'] ?? null;
            if ($fieldId !== null) {
                $content .= '<input type="hidden" name="' . $prefix . '[fieldid]" value="'
                    . self::escape($fieldId) . '">';
            }
            foreach ([
                'tag' => 'Tag',
                'ind1_cd' => 'Indicador 1',
                'ind2_cd' => 'Indicador 2',
                'subfield_cd' => 'Subcampo',
            ] as $key => $label) {
                $value = $field[$key] ?? '';
                $content .= '<label>' . self::escape($label) . ' <input name="' . $prefix . '[' . $key
                    . ']" maxlength="' . ($key === 'tag' ? '3' : '1') . '" value="'
                    . self::escape($value) . '"></label> ';
            }
            $content .= '<label>Conteúdo <textarea name="' . $prefix
                . '[field_data]" maxlength="65000">' . self::escape($field['field_data'] ?? '') . '</textarea></label>'
                . '</fieldset>';
        }

        $content .= '<button type="submit">' . ($editing ? 'Salvar registro' : 'Cadastrar registro') . '</button></form>';
        if ($editing) {
            $content .= '<p><a href="/catalog-admin/bibliographies/delete?bibid=' . $bibId
                . '">Remover registro bibliográfico</a></p>';
        }

        return $content . '<p><a href="/catalog-admin/search">Voltar à busca do catálogo</a></p></html>';
    }

    /**
     * @param array<string, mixed> $record
     * @param array{copies: int, holds: int} $blockers
     */
    private function deletePage(array $record, array $blockers, string $error = ''): string
    {
        $bibId = self::positiveInt($record['bibid'] ?? null);
        if ($bibId === null) {
            throw new \UnexpectedValueException('O banco retornou um identificador bibliográfico inválido.');
        }
        $content = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Remover registro — OpenBiblio</title><h1>Remover registro bibliográfico</h1>'
            . '<p>Confirma a remoção de “' . self::escape($record['title'] ?? '') . '”?</p>';
        if ($error !== '') {
            $content .= '<p role="alert">' . self::escape($error) . '</p>';
        }
        if ($blockers['copies'] > 0 || $blockers['holds'] > 0) {
            $content .= '<p role="alert">Remoção bloqueada: há ' . $blockers['copies']
                . ' exemplar(es) e ' . $blockers['holds'] . ' reserva(s) associada(s).</p>';
        } else {
            $content .= '<form method="post" action="/catalog-admin/bibliographies/delete">'
                . '<input type="hidden" name="_csrf" value="' . self::escape($this->csrfToken()) . '">'
                . '<input type="hidden" name="bibid" value="' . $bibId . '">'
                . '<button type="submit">Confirmar remoção</button></form>';
        }

        return $content . '<p><a href="/catalog-admin/bibliographies/edit?bibid=' . $bibId
            . '">Cancelar</a></p></html>';
    }

    private function authorize(string $path): ?Response
    {
        return (new AccessGuard($this->session))->authorize('catalog', $path);
    }

    private function repository(): BibliographyRepository
    {
        $repository = ($this->repositoryFactory)();
        if (!$repository instanceof BibliographyRepository) {
            throw new \UnexpectedValueException('A fábrica não retornou um repositório bibliográfico válido.');
        }

        return $repository;
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
            throw new \UnexpectedValueException('O registro bibliográfico contém um valor inválido para exibição.');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
