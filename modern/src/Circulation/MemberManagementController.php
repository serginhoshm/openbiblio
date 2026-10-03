<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use Closure;
use OpenBiblio\Modern\Auth\AccessGuard;
use OpenBiblio\Modern\Auth\SessionStore;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;

final class MemberManagementController
{
    /**
     * @param Closure(): MemberRepository $repositoryFactory
     */
    public function __construct(
        private readonly Closure $repositoryFactory,
        private readonly SessionStore $session,
    ) {
    }

    public function form(): Response
    {
        $access = $this->authorize();
        if ($access !== null) {
            return $access;
        }

        return new Response($this->page(), 200, ['Cache-Control' => 'no-store']);
    }

    public function editForm(Request $request): Response
    {
        $access = $this->authorize('/circulation/members/edit');
        if ($access !== null) {
            return $access;
        }
        $memberId = self::requestPositiveInt($request->query['mbrid'] ?? null);
        if ($memberId === null) {
            return new Response('Informe um identificador de membro válido.', 400);
        }
        $repository = $this->repository();
        $member = $repository->findById($memberId);
        if ($member === null) {
            return new Response('Membro não encontrado.', 404);
        }
        $values = [];
        foreach ([
            'barcode_nmbr',
            'last_name',
            'first_name',
            'address',
            'home_phone',
            'work_phone',
            'email',
        ] as $field) {
            $value = $member[$field] ?? '';
            if (!is_string($value) && !is_int($value) && !is_float($value)) {
                throw new \UnexpectedValueException('O cadastro do membro contém um valor inválido.');
            }
            $values[$field] = (string) $value;
        }
        $values['classification'] = (string) $member['classification'];
        foreach ($repository->customFieldValues($memberId) as $code => $value) {
            $values['custom_' . $code] = $value;
        }

        return new Response($this->page($values, '', $memberId), 200, ['Cache-Control' => 'no-store']);
    }

    public function create(Request $request): Response
    {
        $access = $this->authorize();
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

        $fields = [
            'barcode_nmbr' => $request->body['barcode_nmbr'] ?? '',
            'last_name' => $request->body['last_name'] ?? '',
            'first_name' => $request->body['first_name'] ?? '',
            'address' => $request->body['address'] ?? '',
            'home_phone' => $request->body['home_phone'] ?? '',
            'work_phone' => $request->body['work_phone'] ?? '',
            'email' => $request->body['email'] ?? '',
        ];
        foreach ($fields as $name => $value) {
            if (!is_string($value)) {
                return new Response('Os dados do membro são inválidos.', 400);
            }
            $fields[$name] = trim($value);
            if (preg_match('//u', $fields[$name]) !== 1) {
                return new Response('Os campos do membro precisam conter texto UTF-8 válido.', 400);
            }
        }
        $lengths = [
            'barcode_nmbr' => 20,
            'last_name' => 50,
            'first_name' => 50,
            'home_phone' => 15,
            'work_phone' => 15,
            'email' => 128,
        ];
        foreach ($lengths as $name => $limit) {
            $length = preg_match_all('/./us', $fields[$name]);
            if ($length === false) {
                return new Response('Não foi possível validar o tamanho dos campos do membro.', 400);
            }
            if ($length > $limit) {
                return new Response(
                    $this->page($fields, 'O campo ' . $name . ' excede o limite de ' . $limit . ' caracteres.'),
                    400,
                    ['Cache-Control' => 'no-store'],
                );
            }
        }
        if (strlen($fields['address']) > 65535) {
            return new Response($this->page($fields, 'O endereço excede o limite permitido.'), 400);
        }
        $autoBarcode = ($request->body['auto_barcode'] ?? null) === 'Y';
        if ((!$autoBarcode && $fields['barcode_nmbr'] === '') || $fields['last_name'] === '' || $fields['first_name'] === '') {
            return new Response(
                $this->page($fields, 'Código de barras, nome e sobrenome são obrigatórios.'),
                400,
                ['Cache-Control' => 'no-store'],
            );
        }

        $classificationText = $request->body['classification'] ?? null;
        if (!is_string($classificationText) || preg_match('/\A[0-9]+\z/', $classificationText) !== 1) {
            return new Response($this->page($fields, 'Selecione uma classificação válida.'), 400);
        }
        $classification = filter_var(
            $classificationText,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 32767]],
        );
        if ($classification === false) {
            return new Response($this->page($fields, 'Selecione uma classificação válida.'), 400);
        }

        $user = $this->session->get('auth.user');
        $staffUserId = is_array($user) && is_int($user['userid'] ?? null) ? $user['userid'] : 0;
        if ($staffUserId < 1) {
            return new Response('A sessão de funcionário é inválida; entre novamente.', 401);
        }

        $member = $fields;
        $member['classification'] = $classification;
        $repository = $this->repository();
        $customDefinitions = $repository->customFieldDefinitions();
        try {
            $customFields = $this->customFieldInput($request, $customDefinitions);
            $created = $repository->create($member, $staffUserId, $autoBarcode, $customFields);
        } catch (MemberRejected $error) {
            return new Response(
                $this->page(
                    $fields + $this->customFieldDisplayValues($request, $customDefinitions),
                    $error->getMessage(),
                ),
                422,
                ['Cache-Control' => 'no-store'],
            );
        }

        return new Response(
            '',
            303,
            ['Location' => '/circulation/member?barcode=' . rawurlencode($created['barcode_nmbr'])],
        );
    }

    public function update(Request $request): Response
    {
        $access = $this->authorize('/circulation/members/edit');
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
        $memberId = self::requestPositiveInt($request->body['mbrid'] ?? null);
        if ($memberId === null) {
            return new Response('O identificador do membro é inválido.', 400);
        }
        $fields = [
            'barcode_nmbr' => $request->body['barcode_nmbr'] ?? '',
            'last_name' => $request->body['last_name'] ?? '',
            'first_name' => $request->body['first_name'] ?? '',
            'address' => $request->body['address'] ?? '',
            'home_phone' => $request->body['home_phone'] ?? '',
            'work_phone' => $request->body['work_phone'] ?? '',
            'email' => $request->body['email'] ?? '',
        ];
        foreach ($fields as $name => $value) {
            if (!is_string($value) || preg_match('//u', $value) !== 1) {
                return new Response('Os dados do membro são inválidos.', 400);
            }
            $fields[$name] = trim($value);
        }
        $lengths = [
            'barcode_nmbr' => 20,
            'last_name' => 50,
            'first_name' => 50,
            'home_phone' => 15,
            'work_phone' => 15,
            'email' => 128,
        ];
        foreach ($lengths as $name => $limit) {
            $length = preg_match_all('/./us', $fields[$name]);
            if ($length === false || $length > $limit) {
                return new Response(
                    $this->page($fields, 'O campo ' . $name . ' excede o limite de ' . $limit . ' caracteres.', $memberId),
                    400,
                    ['Cache-Control' => 'no-store'],
                );
            }
        }
        if (
            $fields['barcode_nmbr'] === ''
            || $fields['last_name'] === ''
            || $fields['first_name'] === ''
            || strlen($fields['address']) > 65535
        ) {
            return new Response(
                $this->page($fields, 'Código de barras, nome, sobrenome e endereço válidos são obrigatórios.', $memberId),
                400,
                ['Cache-Control' => 'no-store'],
            );
        }
        $classificationText = $request->body['classification'] ?? null;
        if (!is_string($classificationText) || preg_match('/\A[0-9]+\z/', $classificationText) !== 1) {
            return new Response($this->page($fields, 'Selecione uma classificação válida.', $memberId), 400);
        }
        $classification = filter_var(
            $classificationText,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 32767]],
        );
        if ($classification === false) {
            return new Response($this->page($fields, 'Selecione uma classificação válida.', $memberId), 400);
        }
        $user = $this->session->get('auth.user');
        $staffUserId = is_array($user) && is_int($user['userid'] ?? null) ? $user['userid'] : 0;
        if ($staffUserId < 1) {
            return new Response('A sessão de funcionário é inválida; entre novamente.', 401);
        }

        $member = $fields;
        $member['classification'] = $classification;
        $repository = $this->repository();
        $customDefinitions = $repository->customFieldDefinitions();
        try {
            $customFields = $this->customFieldInput($request, $customDefinitions);
            $repository->update($member, $memberId, $staffUserId, $customFields);
        } catch (MemberRejected $error) {
            return new Response(
                $this->page(
                    $fields + $this->customFieldDisplayValues($request, $customDefinitions),
                    $error->getMessage(),
                    $memberId,
                ),
                422,
                ['Cache-Control' => 'no-store'],
            );
        }

        return new Response(
            '',
            303,
            ['Location' => '/circulation/member?barcode=' . rawurlencode($fields['barcode_nmbr'])],
        );
    }

    public function deleteForm(Request $request): Response
    {
        $access = $this->authorize('/circulation/members/delete');
        if ($access !== null) {
            return $access;
        }
        $memberId = self::requestPositiveInt($request->query['mbrid'] ?? null);
        if ($memberId === null) {
            return new Response('Informe um identificador de membro válido.', 400);
        }
        $repository = $this->repository();
        $member = $repository->findById($memberId);
        if ($member === null) {
            return new Response('Membro não encontrado.', 404);
        }
        $name = self::escape($member['first_name'] ?? '') . ' ' . self::escape($member['last_name'] ?? '');

        return new Response(
            $this->deletionPage($memberId, $name, $repository->deletionBlockers($memberId)),
            200,
            ['Cache-Control' => 'no-store'],
        );
    }

    public function delete(Request $request): Response
    {
        $access = $this->authorize('/circulation/members/delete');
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
        $memberId = self::requestPositiveInt($request->body['mbrid'] ?? null);
        if ($memberId === null) {
            return new Response('Informe um identificador de membro válido.', 400);
        }
        $repository = $this->repository();
        $member = $repository->findById($memberId);
        if ($member === null) {
            return new Response('Membro não encontrado.', 404);
        }
        $name = self::escape($member['first_name'] ?? '') . ' ' . self::escape($member['last_name'] ?? '');
        try {
            $repository->delete($memberId);
        } catch (MemberRejected $error) {
            if ($repository->findById($memberId) === null) {
                return new Response('Membro não encontrado.', 404);
            }
            return new Response(
                $this->deletionPage(
                    $memberId,
                    $name,
                    $repository->deletionBlockers($memberId),
                    $error->getMessage(),
                ),
                409,
                ['Cache-Control' => 'no-store'],
            );
        }

        return new Response(
            '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Membro removido — OpenBiblio</title><h1>Membro removido</h1>'
            . '<p>O cadastro de ' . $name . ' e seu histórico foram removidos.</p>'
            . '<a href="/circulation">Voltar à circulação</a></html>',
            200,
            ['Cache-Control' => 'no-store'],
        );
    }

    private function authorize(string $path = '/circulation/members/new'): ?Response
    {
        $guard = new AccessGuard($this->session);
        $circulationAccess = $guard->authorize('circulation', $path);
        if ($circulationAccess !== null) {
            return $circulationAccess;
        }

        return $guard->authorize('member_circulation', $path);
    }

    private function repository(): MemberRepository
    {
        $repository = ($this->repositoryFactory)();
        if (!$repository instanceof MemberRepository) {
            throw new \UnexpectedValueException('A fábrica não retornou um repositório de membros válido.');
        }

        return $repository;
    }

    /**
     * @param array{checkouts: int, holds: int} $blockers
     */
    private function deletionPage(
        int $memberId,
        string $name,
        array $blockers,
        string $error = '',
    ): string {
        $csrf = self::escape($this->csrfToken());
        $content = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Remover membro — OpenBiblio</title><h1>Remover membro</h1>';
        if ($error !== '') {
            $content .= '<p role="alert">' . self::escape($error) . '</p>';
        }
        $content .= '<p>Confirma a remoção de ' . $name . '? O histórico de empréstimos e os dados da conta '
            . 'também serão removidos.</p>';
        if ($blockers['checkouts'] > 0 || $blockers['holds'] > 0) {
            $content .= '<p role="alert">Remoção bloqueada: ' . $blockers['checkouts']
                . ' empréstimo(s) ativo(s) e ' . $blockers['holds']
                . ' reserva(s) pendente(s). Regularize-os antes de remover o membro.</p>';
        } else {
            $content .= '<form method="post" action="/circulation/members/delete">'
                . '<input type="hidden" name="_csrf" value="' . $csrf . '">'
                . '<input type="hidden" name="mbrid" value="' . $memberId . '">'
                . '<button type="submit">Confirmar remoção</button></form>';
        }

        return $content . '<p><a href="/circulation/member">Cancelar</a></p></html>';
    }

    private function validCsrf(Request $request): bool
    {
        $submittedToken = $request->body['_csrf'] ?? null;
        $expectedToken = $this->session->get('auth.csrf');

        return is_string($submittedToken)
            && is_string($expectedToken)
            && hash_equals($expectedToken, $submittedToken);
    }

    private static function requestPositiveInt(mixed $value): ?int
    {
        if (!is_int($value) && !is_string($value)) {
            return null;
        }
        $parsed = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $parsed === false ? null : $parsed;
    }

    /**
     * @param list<array{code: string, description: string}> $definitions
     * @return array<string, string>
     */
    private function customFieldInput(Request $request, array $definitions): array
    {
        $values = [];
        foreach ($definitions as $definition) {
            $code = $definition['code'] ?? null;
            if (!is_string($code) || $code === '' || strlen($code) > 16) {
                throw new \UnexpectedValueException('A configuração dos campos adicionais está inválida.');
            }
            $value = $request->body['custom_' . $code] ?? '';
            if (!is_string($value) || preg_match('//u', $value) !== 1 || strlen($value) > 65535) {
                throw new MemberRejected('O valor de um campo adicional é inválido ou excede o limite permitido.');
            }
            $values[$code] = $value;
        }

        return $values;
    }

    /**
     * @param list<array{code: string, description: string}> $definitions
     * @return array<string, string>
     */
    private function customFieldDisplayValues(Request $request, array $definitions): array
    {
        $values = [];
        foreach ($definitions as $definition) {
            $code = $definition['code'] ?? null;
            if (!is_string($code) || $code === '' || strlen($code) > 16) {
                throw new \UnexpectedValueException('A configuração dos campos adicionais está inválida.');
            }
            $value = $request->body['custom_' . $code] ?? '';
            if (is_string($value)) {
                $values['custom_' . $code] = $value;
            }
        }

        return $values;
    }

    /**
     * @param array<string, string> $values
     */
    private function page(array $values = [], string $error = '', ?int $memberId = null): string
    {
        $repository = $this->repository();
        $classifications = $repository->classifications();
        if ($classifications === []) {
            throw new \RuntimeException('Nenhuma classificação de membro está configurada no banco.');
        }
        $csrf = self::escape($this->csrfToken());
        $editing = $memberId !== null;
        $content = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>' . ($editing ? 'Editar membro' : 'Novo membro') . ' — OpenBiblio</title><h1>'
            . ($editing ? 'Editar membro' : 'Cadastrar membro') . '</h1>';
        if ($error !== '') {
            $content .= '<p role="alert">' . self::escape($error) . '</p>';
        }
        $content .= '<form method="post" action="'
            . ($editing ? '/circulation/members/edit' : '/circulation/members/new') . '">'
            . '<input type="hidden" name="_csrf" value="' . $csrf . '">'
            . ($editing ? '<input type="hidden" name="mbrid" value="' . $memberId . '">' : '')
            . '<label for="barcode_nmbr">Código de barras</label> '
            . '<input id="barcode_nmbr" name="barcode_nmbr" maxlength="20" value="'
            . self::escape($values['barcode_nmbr'] ?? '') . '" required> '
            . ($editing ? '' : '<label><input type="checkbox" name="auto_barcode" value="Y" checked> Gerar código automaticamente</label> ')
            . '<label for="first_name">Nome</label> '
            . '<input id="first_name" name="first_name" maxlength="50" required value="'
            . self::escape($values['first_name'] ?? '') . '"> '
            . '<label for="last_name">Sobrenome</label> '
            . '<input id="last_name" name="last_name" maxlength="50" required value="'
            . self::escape($values['last_name'] ?? '') . '"> '
            . '<label for="address">Endereço</label> '
            . '<textarea id="address" name="address">' . self::escape($values['address'] ?? '') . '</textarea> '
            . '<label for="home_phone">Telefone residencial</label> '
            . '<input id="home_phone" name="home_phone" maxlength="15" value="'
            . self::escape($values['home_phone'] ?? '') . '"> '
            . '<label for="work_phone">Telefone comercial</label> '
            . '<input id="work_phone" name="work_phone" maxlength="15" value="'
            . self::escape($values['work_phone'] ?? '') . '"> '
            . '<label for="email">E-mail</label> '
            . '<input id="email" name="email" maxlength="128" value="'
            . self::escape($values['email'] ?? '') . '"> '
            . '<label for="classification">Classificação</label> '
            . '<select id="classification" name="classification" required>';
        foreach ($classifications as $classification) {
            $code = self::positiveInt($classification['code'] ?? null);
            $description = self::escape($classification['description'] ?? '');
            $selected = isset($values['classification'])
                && $values['classification'] === (string) $code ? ' selected' : '';
            $content .= '<option value="' . $code . '"' . $selected . '>' . $description . '</option>';
        }
        $content .= '</select> ';
        foreach ($repository->customFieldDefinitions() as $definition) {
            $code = $definition['code'] ?? null;
            if (!is_string($code) || $code === '' || strlen($code) > 16) {
                throw new \UnexpectedValueException('A configuração dos campos adicionais está inválida.');
            }
            $label = self::escape($definition['description'] ?? '');
            $fieldName = 'custom_' . $code;
            $content .= '<label for="' . self::escape($fieldName) . '">' . $label . '</label> '
                . '<textarea id="' . self::escape($fieldName) . '" name="' . self::escape($fieldName) . '">'
                . self::escape($values[$fieldName] ?? '') . '</textarea> ';
        }

        return $content . '<button type="submit">'
            . ($editing ? 'Salvar alterações' : 'Cadastrar membro') . '</button></form></html>';
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

    private static function positiveInt(mixed $value): int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }
        if (is_string($value) && preg_match('/\A[0-9]+\z/', $value) === 1) {
            $parsed = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($parsed !== false) {
                return $parsed;
            }
        }

        throw new \UnexpectedValueException('O banco retornou uma classificação inválida.');
    }

    private static function escape(mixed $value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new \UnexpectedValueException('O cadastro de membro retornou um valor inválido.');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
