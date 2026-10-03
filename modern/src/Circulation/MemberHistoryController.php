<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use Closure;
use OpenBiblio\Modern\Auth\AccessGuard;
use OpenBiblio\Modern\Auth\SessionStore;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;

final class MemberHistoryController
{
    /**
     * @param Closure(): MemberHistoryRepository $historyRepositoryFactory
     */
    public function __construct(
        private readonly Closure $historyRepositoryFactory,
        private readonly SessionStore $session,
    ) {
    }

    public function view(Request $request): Response
    {
        $access = (new AccessGuard($this->session))->authorize(
            'circulation',
            '/circulation/member/history',
        );
        if ($access !== null) {
            return $access;
        }
        $memberId = self::positiveInt($request->query['mbrid'] ?? null);
        if ($memberId === null) {
            return new Response('Informe um identificador válido de membro.', 400);
        }
        $historyRepository = ($this->historyRepositoryFactory)();
        if (!$historyRepository instanceof MemberHistoryRepository) {
            throw new \UnexpectedValueException('A fábrica não retornou um repositório de histórico válido.');
        }
        $member = $historyRepository->findMember($memberId);
        if ($member === null) {
            return new Response('Membro não encontrado.', 404);
        }
        $firstName = self::escape($member['first_name'] ?? '');
        $lastName = self::escape($member['last_name'] ?? '');
        $content = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Histórico do membro — OpenBiblio</title><h1>Histórico de empréstimos</h1>'
            . '<p>Membro: ' . $firstName . ' ' . $lastName . '</p>'
            . '<table><thead><tr><th>Código de barras</th><th>Título</th><th>Autor</th>'
            . '<th>Status</th><th>Data da alteração</th><th>Data de devolução</th></tr></thead><tbody>';
        $history = $historyRepository->forMember($memberId);
        if ($history === []) {
            $content .= '<tr><td colspan="6">Histórico não encontrado.</td></tr>';
        } else {
            foreach ($history as $entry) {
                $content .= '<tr><td>' . self::escape($entry['barcode_nmbr'] ?? '')
                    . '</td><td>' . self::escape($entry['title'] ?? '')
                    . '</td><td>' . self::escape($entry['author'] ?? '')
                    . '</td><td>' . self::escape($entry['status_description'] ?? '')
                    . '</td><td>' . self::formatDate($entry['status_begin_dt'] ?? null)
                    . '</td><td>' . self::formatDate($entry['due_back_dt'] ?? null)
                    . '</td></tr>';
            }
        }

        return new Response($content . '</tbody></table></html>');
    }

    private static function positiveInt(mixed $value): ?int
    {
        if (!is_int($value) && !is_string($value)) {
            return null;
        }
        $parsed = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $parsed === false ? null : $parsed;
    }

    private static function formatDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (!is_string($value)) {
            throw new \UnexpectedValueException('O histórico contém uma data inválida.');
        }
        try {
            return (new \DateTimeImmutable($value))->format('d/m/Y');
        } catch (\Exception $error) {
            throw new \UnexpectedValueException('O histórico contém uma data inválida.', 0, $error);
        }
    }

    private static function escape(mixed $value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new \UnexpectedValueException('O histórico contém um valor inválido.');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
