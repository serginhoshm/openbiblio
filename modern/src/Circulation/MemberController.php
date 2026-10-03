<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use Closure;
use OpenBiblio\Modern\Auth\AccessGuard;
use OpenBiblio\Modern\Auth\SessionStore;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;

final class MemberController
{
    /**
     * @param Closure(): MemberRepository $repositoryFactory
     */
    public function __construct(
        private readonly Closure $repositoryFactory,
        private readonly SessionStore $session,
    ) {
    }

    public function view(Request $request): Response
    {
        $access = (new AccessGuard($this->session))->authorize('circulation', '/circulation/member');
        if ($access !== null) {
            return $access;
        }

        $barcode = $request->query['barcode'] ?? '';
        if (!is_string($barcode)) {
            return new Response($this->page('', '<p>Informe um único código de barras.</p>'), 400);
        }

        $barcode = trim($barcode);
        $content = '';
        if ($barcode !== '') {
            $repository = ($this->repositoryFactory)();
            if (!$repository instanceof MemberRepository) {
                throw new \UnexpectedValueException('A fábrica não retornou um repositório de membros válido.');
            }
            $member = $repository->findByBarcode($barcode);
            if ($member === null) {
                $content = '<p>Nenhum membro encontrado com esse código de barras.</p>';
            } else {
                $memberId = $member['mbrid'] ?? null;
                if (!is_int($memberId) && !is_string($memberId)) {
                    throw new \UnexpectedValueException('O cadastro retornou um identificador inválido de membro.');
                }
                $parsedMemberId = filter_var($memberId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($parsedMemberId === false) {
                    throw new \UnexpectedValueException('O cadastro retornou um identificador inválido de membro.');
                }
                $content = $this->renderMember(
                    $member,
                    $repository->customFieldDefinitions(),
                    $repository->customFieldValues($parsedMemberId),
                );
            }
        }

        return new Response($this->page($barcode, $content));
    }

    /**
     * @param array<string, mixed> $member
     * @param list<array{code: string, description: string}> $customDefinitions
     * @param array<string, string> $customValues
     */
    private function renderMember(array $member, array $customDefinitions, array $customValues): string
    {
        $firstName = self::escape($member['first_name'] ?? '');
        $lastName = self::escape($member['last_name'] ?? '');
        $barcode = self::escape($member['barcode_nmbr'] ?? '');
        $email = self::escape($member['email'] ?? '');
        $homePhone = self::escape($member['home_phone'] ?? '');
        $workPhone = self::escape($member['work_phone'] ?? '');
        $address = self::escape($member['address'] ?? '');
        $memberId = $member['mbrid'] ?? null;
        if (!is_int($memberId) && !is_string($memberId)) {
            throw new \UnexpectedValueException('O cadastro retornou um identificador inválido de membro.');
        }
        $holdsUrl = '/circulation/holds?member_barcode='
            . rawurlencode((string) ($member['barcode_nmbr'] ?? ''));
        $user = $this->session->get('auth.user');
        $permissions = is_array($user) ? ($user['permissions'] ?? null) : null;
        $editLink = is_array($permissions) && ($permissions['member_circulation'] ?? false) === true
            ? '<p><a href="/circulation/members/edit?mbrid=' . rawurlencode((string) $memberId)
                . '">Editar cadastro do membro</a></p>'
            : '';
        $deleteLink = is_array($permissions) && ($permissions['member_circulation'] ?? false) === true
            ? '<p><a href="/circulation/members/delete?mbrid=' . rawurlencode((string) $memberId)
                . '">Remover membro</a></p>'
            : '';
        $accountLink = '<p><a href="/circulation/account?mbrid='
            . rawurlencode((string) $memberId) . '">Consultar conta do membro</a></p>';
        $historyLink = '<p><a href="/circulation/member/history?mbrid='
            . rawurlencode((string) $memberId) . '">Consultar histórico de empréstimos</a></p>';
        $customContent = '';
        foreach ($customDefinitions as $definition) {
            $code = $definition['code'] ?? null;
            if (!is_string($code) || $code === '' || strlen($code) > 16) {
                throw new \UnexpectedValueException('A configuração dos campos adicionais está inválida.');
            }
            $value = $customValues[$code] ?? '';
            if ($value === '') {
                continue;
            }
            $customContent .= '<dt>' . self::escape($definition['description'] ?? '')
                . '</dt><dd>' . nl2br(self::escape($value), false) . '</dd>';
        }

        $content = '<section><h2>' . $firstName . ' ' . $lastName . '</h2>'
            . '<dl><dt>Código de barras</dt><dd>' . $barcode . '</dd>'
            . '<dt>E-mail</dt><dd>' . $email . '</dd>'
            . '<dt>Telefone residencial</dt><dd>' . $homePhone . '</dd>'
            . '<dt>Telefone comercial</dt><dd>' . $workPhone . '</dd>'
            . '<dt>Endereço</dt><dd>' . nl2br($address, false) . '</dd>'
            . $customContent . '</dl>'
            . '<p><a href="' . self::escape($holdsUrl) . '">Gerenciar reservas deste membro</a></p>'
            . $accountLink . $historyLink . $editLink . $deleteLink . '</section>';

        return $content;
    }

    private function page(string $barcode, string $content): string
    {
        return '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Consulta de membro — OpenBiblio</title><h1>Consulta de membro</h1>'
            . '<form method="get" action="/circulation/member">'
            . '<label for="barcode">Código de barras do membro</label> '
            . '<input id="barcode" name="barcode" value="' . self::escape($barcode) . '" required> '
            . '<button type="submit">Consultar</button></form>'
            . $content . '</html>';
    }

    private static function escape(mixed $value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new \UnexpectedValueException('O cadastro retornou um valor que não pode ser exibido como texto.');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
