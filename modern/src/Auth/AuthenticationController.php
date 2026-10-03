<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Auth;

use Closure;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;

final class AuthenticationController
{
    /**
     * @param Closure(): StaffRepository $staffRepositoryFactory
     */
    public function __construct(
        private readonly Closure $staffRepositoryFactory,
        private readonly SessionStore $session,
    ) {
    }

    public function form(Request $request): Response
    {
        $csrfToken = $this->csrfToken();
        $returnTo = self::safeReturnPath($request->query['return'] ?? '/');
        $error = $this->session->get('auth.error');
        $this->session->remove('auth.error');

        $message = is_string($error) ? '<p role="alert">' . self::escape($error) . '</p>' : '';
        $body = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Entrar — OpenBiblio</title><h1>Entrar</h1>' . $message
            . '<form method="post" action="/login">'
            . '<input type="hidden" name="_csrf" value="' . self::escape($csrfToken) . '">'
            . '<input type="hidden" name="return" value="' . self::escape($returnTo) . '">'
            . '<label for="username">Usuário</label>'
            . '<input id="username" name="username" autocomplete="username" maxlength="20" required>'
            . '<label for="password">Senha</label>'
            . '<input id="password" name="password" type="password" autocomplete="current-password" required>'
            . '<button type="submit">Entrar</button></form></html>';

        return new Response($body, 200, ['Cache-Control' => 'no-store']);
    }

    public function logoutForm(): Response
    {
        $body = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Sair — OpenBiblio</title><h1>Sair</h1>'
            . '<form method="post" action="/logout">'
            . '<input type="hidden" name="_csrf" value="' . self::escape($this->csrfToken()) . '">'
            . '<button type="submit">Encerrar sessão</button></form></html>';

        return new Response($body, 200, ['Cache-Control' => 'no-store']);
    }

    public function login(Request $request): Response
    {
        if (!$this->validCsrf($request->body['_csrf'] ?? null)) {
            return new Response('Token de formulário inválido. Recarregue a página e tente novamente.', 400);
        }

        $username = $request->body['username'] ?? null;
        $password = $request->body['password'] ?? null;
        $returnTo = self::safeReturnPath($request->body['return'] ?? '/');
        if (!is_string($username) || !is_string($password) || trim($username) === '' || $password === '') {
            $this->session->set('auth.error', 'Usuário e senha são obrigatórios.');

            return new Response('', 303, ['Location' => '/login']);
        }

        $username = trim($username);
        $repository = $this->staffRepository();
        $staff = $repository->authenticate($username, $password);
        if ($staff === null) {
            $attempts = $this->session->get('auth.failed_attempts');
            $attempts = is_int($attempts) ? $attempts + 1 : 1;
            $this->session->set('auth.failed_attempts', $attempts);
            if ($attempts >= 3) {
                $repository->suspendByUsername($username);
                $this->session->set('auth.failed_attempts', 0);
                $this->session->set('auth.error', 'A conta foi suspensa após tentativas inválidas.');
            } else {
                $this->session->set('auth.error', 'Usuário ou senha inválidos.');
            }

            return new Response('', 303, ['Location' => '/login']);
        }

        if (self::isEnabled($staff['suspended_flg'] ?? null)) {
            $this->session->set('auth.error', 'Esta conta está suspensa.');

            return new Response('', 303, ['Location' => '/login']);
        }

        $this->session->regenerate();
        $this->session->set('auth.user', [
            'userid' => (int) $staff['userid'],
            'username' => (string) $staff['username'],
            'first_name' => (string) ($staff['first_name'] ?? ''),
            'last_name' => (string) ($staff['last_name'] ?? ''),
            'permissions' => [
                'admin' => self::isEnabled($staff['admin_flg'] ?? null),
                'circulation' => self::isEnabled($staff['circ_flg'] ?? null),
                'member_circulation' => self::isEnabled($staff['circ_mbr_flg'] ?? null),
                'catalog' => self::isEnabled($staff['catalog_flg'] ?? null),
                'reports' => self::isEnabled($staff['reports_flg'] ?? null),
            ],
        ]);
        $this->session->set('auth.failed_attempts', 0);
        $this->session->remove('auth.error');
        $this->session->set('auth.csrf', bin2hex(random_bytes(32)));

        return new Response('', 303, ['Location' => $returnTo]);
    }

    public function logout(Request $request): Response
    {
        if (!$this->validCsrf($request->body['_csrf'] ?? null)) {
            return new Response('Token de formulário inválido. Recarregue a página e tente novamente.', 400);
        }

        $this->session->destroy();

        return new Response('', 303, ['Location' => '/']);
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

    private function staffRepository(): StaffRepository
    {
        $repository = ($this->staffRepositoryFactory)();
        if (!$repository instanceof StaffRepository) {
            throw new \UnexpectedValueException('A fábrica não retornou um repositório de staff válido.');
        }

        return $repository;
    }

    private function validCsrf(mixed $submitted): bool
    {
        $expected = $this->session->get('auth.csrf');

        return is_string($submitted)
            && is_string($expected)
            && hash_equals($expected, $submitted);
    }

    private static function safeReturnPath(mixed $path): string
    {
        if (
            !is_string($path)
            || $path === ''
            || $path[0] !== '/'
            || str_starts_with($path, '//')
            || str_contains($path, '\\')
            || preg_match('/[\r\n]/', $path) === 1
        ) {
            return '/';
        }

        return $path;
    }

    private static function isEnabled(mixed $flag): bool
    {
        return $flag === 'Y' || $flag === 'y' || $flag === 1 || $flag === '1';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
