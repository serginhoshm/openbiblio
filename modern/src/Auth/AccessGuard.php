<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Auth;

use OpenBiblio\Modern\Http\Response;

final class AccessGuard
{
    private const PERMISSION_KEYS = [
        'admin' => 'admin',
        'circulation' => 'circulation',
        'member_circulation' => 'member_circulation',
        'catalog' => 'catalog',
        'reports' => 'reports',
    ];

    public function __construct(private readonly SessionStore $session)
    {
    }

    public function authorize(string $permission, string $returnPath): ?Response
    {
        $key = self::PERMISSION_KEYS[$permission] ?? null;
        if ($key === null) {
            throw new \InvalidArgumentException('Permissão de acesso desconhecida.');
        }

        $user = $this->session->get('auth.user');
        if (!is_array($user)) {
            return new Response(
                '',
                303,
                ['Location' => '/login?return=' . rawurlencode($returnPath)],
            );
        }

        $permissions = $user['permissions'] ?? null;
        if (!is_array($permissions) || ($permissions[$key] ?? false) !== true) {
            return new Response('Você não tem permissão para acessar esta área.', 403);
        }

        return null;
    }
}
