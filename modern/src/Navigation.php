<?php

declare(strict_types=1);

namespace OpenBiblio\Modern;

final class Navigation
{
    /**
     * @param array<string, mixed>|null $user
     */
    public static function menu(?array $user): string
    {
        $links = [
            '<a href="/">Início</a>',
            '<a href="/opac">Catálogo público</a>',
        ];
        if ($user === null) {
            $links[] = '<a href="/circulation">Circulação</a>';
            $links[] = '<a href="/catalog-admin">Catálogo administrativo</a>';
            $links[] = '<a href="/login">Entrar</a>';
        } else {
            $permissions = $user['permissions'] ?? [];
            if (!is_array($permissions)) {
                $permissions = [];
            }
            if (($permissions['circulation'] ?? false) === true) {
                $links[] = '<a href="/circulation">Circulação</a>';
            }
            if (($permissions['catalog'] ?? false) === true) {
                $links[] = '<a href="/catalog-admin">Catálogo administrativo</a>';
            }
            $name = trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? ''));
            if ($name === '') {
                $name = (string) ($user['username'] ?? 'Funcionário');
            }
            $links[] = '<span>Conectado: ' . self::escape($name) . '</span>';
            $links[] = '<a href="/logout">Sair</a>';
        }

        return '<nav aria-label="Navegação principal"><ul><li>'
            . implode('</li><li>', $links)
            . '</li></ul></nav>';
    }

    /**
     * @param array<string, mixed>|null $user
     */
    public static function dashboard(?array $user): string
    {
        $content = '<section><h2>Áreas do sistema</h2><ul>'
            . '<li><a href="/opac">Catálogo público</a> — pesquisar o acervo.</li>';
        if ($user === null) {
            $content .= '<li><a href="/login">Entrar como funcionário</a> para acessar circulação e catalogação.</li>';
        } else {
            $permissions = $user['permissions'] ?? [];
            if (!is_array($permissions)) {
                $permissions = [];
            }
            if (($permissions['circulation'] ?? false) === true) {
                $content .= '<li><a href="/circulation">Circulação</a> — membros, empréstimos, devoluções e reservas.</li>';
            }
            if (($permissions['catalog'] ?? false) === true) {
                $content .= '<li><a href="/catalog-admin">Catálogo administrativo</a> — registros bibliográficos e exemplares.</li>';
            }
            if (($permissions['admin'] ?? false) === true) {
                $content .= '<li>Administração geral — <span>em migração</span>.</li>';
            }
            if (($permissions['reports'] ?? false) === true) {
                $content .= '<li>Relatórios — <span>em migração</span>.</li>';
            }
            if (($permissions['circulation'] ?? false) !== true
                && ($permissions['catalog'] ?? false) !== true
                && ($permissions['admin'] ?? false) !== true
                && ($permissions['reports'] ?? false) !== true) {
                $content .= '<li>Seu usuário ainda não possui permissões para módulos migrados.</li>';
            }
        }

        return $content . '</ul><p>Os módulos em migração ainda não estão disponíveis nesta versão.</p></section>';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
