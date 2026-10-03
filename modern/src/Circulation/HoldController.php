<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use Closure;
use OpenBiblio\Modern\Auth\AccessGuard;
use OpenBiblio\Modern\Auth\SessionStore;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;

final class HoldController
{
    /**
     * @param Closure(): HoldRepository $repositoryFactory
     */
    public function __construct(
        private readonly Closure $repositoryFactory,
        private readonly SessionStore $session,
    ) {
    }

    public function view(Request $request): Response
    {
        $access = $this->authorize();
        if ($access !== null) {
            return $access;
        }

        $barcode = $request->query['member_barcode'] ?? '';
        if (!is_string($barcode)) {
            return new Response($this->page(null, [], '', 'Informe um único código de membro.'), 400);
        }
        $barcode = trim($barcode);
        if ($barcode === '') {
            return new Response($this->page(null, []), 200, ['Cache-Control' => 'no-store']);
        }

        $repository = $this->repository();
        try {
            $member = $repository->findMember($barcode);
        } catch (HoldRejected $error) {
            return new Response($this->page(null, [], $barcode, $error->getMessage()), 404);
        }

        return new Response(
            $this->page($member, $repository->holdsForMember($member['mbrid'])),
            200,
            ['Cache-Control' => 'no-store'],
        );
    }

    public function change(Request $request): Response
    {
        $access = $this->authorize();
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

        $action = $request->body['action'] ?? null;
        $memberBarcode = $request->body['member_barcode'] ?? null;
        if (!is_string($action) || !is_string($memberBarcode)) {
            return new Response('Os dados da reserva são inválidos.', 400);
        }
        $memberBarcode = trim($memberBarcode);
        if ($memberBarcode === '' || strlen($memberBarcode) > 20) {
            return new Response('Informe um código de barras de membro válido.', 400);
        }

        $repository = $this->repository();
        try {
            $member = $repository->findMember($memberBarcode);
        } catch (HoldRejected $error) {
            return new Response(
                $this->page(null, [], $memberBarcode, $error->getMessage()),
                404,
                ['Cache-Control' => 'no-store'],
            );
        }

        try {
            if ($action === 'place') {
                $copyBarcode = $request->body['copy_barcode'] ?? null;
                if (!is_string($copyBarcode) || ($copyBarcode = trim($copyBarcode)) === '' || strlen($copyBarcode) > 20) {
                    return new Response(
                        $this->page($member, $repository->holdsForMember($member['mbrid']), '', 'Informe um código de exemplar válido.'),
                        400,
                        ['Cache-Control' => 'no-store'],
                    );
                }
                $repository->place($memberBarcode, $copyBarcode);
                $notice = 'A reserva foi registrada.';
            } elseif ($action === 'cancel') {
                $holdIdText = $request->body['holdid'] ?? null;
                if (!is_string($holdIdText) || preg_match('/\A[0-9]+\z/', $holdIdText) !== 1) {
                    return new Response('O identificador da reserva é inválido.', 400);
                }
                $holdId = filter_var($holdIdText, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($holdId === false) {
                    return new Response('O identificador da reserva é inválido.', 400);
                }
                $repository->cancel($member['mbrid'], $holdId);
                $notice = 'A reserva foi removida.';
            } else {
                return new Response('A operação de reserva é inválida.', 400);
            }
        } catch (HoldRejected $error) {
            return new Response(
                $this->page($member, $repository->holdsForMember($member['mbrid']), '', $error->getMessage()),
                422,
                ['Cache-Control' => 'no-store'],
            );
        }

        return new Response(
            $this->page($member, $repository->holdsForMember($member['mbrid']), '', '', $notice),
            200,
            ['Cache-Control' => 'no-store'],
        );
    }

    private function authorize(): ?Response
    {
        return (new AccessGuard($this->session))->authorize('circulation', '/circulation/holds');
    }

    private function repository(): HoldRepository
    {
        $repository = ($this->repositoryFactory)();
        if (!$repository instanceof HoldRepository) {
            throw new \UnexpectedValueException('A fábrica não retornou um repositório de reservas válido.');
        }

        return $repository;
    }

    private function validCsrf(Request $request): bool
    {
        $submitted = $request->body['_csrf'] ?? null;
        $expected = $this->session->get('auth.csrf');

        return is_string($submitted)
            && is_string($expected)
            && hash_equals($expected, $submitted);
    }

    /**
     * @param array{mbrid: int, barcode_nmbr: string, first_name: string, last_name: string}|null $member
     * @param list<array<string, mixed>> $holds
     */
    private function page(
        ?array $member,
        array $holds,
        string $lookupBarcode = '',
        string $error = '',
        string $notice = '',
    ): string {
        $content = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Reservas — OpenBiblio</title><h1>Reservas de exemplares</h1>';
        if ($error !== '') {
            $content .= '<p role="alert">' . self::escape($error) . '</p>';
        }
        if ($notice !== '') {
            $content .= '<p role="status">' . self::escape($notice) . '</p>';
        }

        $content .= '<form method="get" action="/circulation/holds">'
            . '<label for="member_barcode">Código de barras do membro</label> '
            . '<input id="member_barcode" name="member_barcode" maxlength="20" value="'
            . self::escape($member['barcode_nmbr'] ?? $lookupBarcode) . '" required> '
            . '<button type="submit">Consultar reservas</button></form>';
        if ($member !== null) {
            $name = self::escape($member['first_name'] . ' ' . $member['last_name']);
            $memberCode = self::escape($member['barcode_nmbr']);
            $token = self::escape($this->csrfToken());
            $content .= '<h2>' . $name . ' (' . $memberCode . ')</h2>'
                . '<form method="post" action="/circulation/holds">'
                . '<input type="hidden" name="_csrf" value="' . $token . '">'
                . '<input type="hidden" name="action" value="place">'
                . '<input type="hidden" name="member_barcode" value="' . $memberCode . '">'
                . '<label for="copy_barcode">Código de barras do exemplar</label> '
                . '<input id="copy_barcode" name="copy_barcode" maxlength="20" required> '
                . '<button type="submit">Criar reserva</button></form>'
                . '<h3>Reservas atuais</h3>';
            if ($holds === []) {
                $content .= '<p>Este membro não possui reservas.</p>';
            } else {
                $content .= '<ul>';
                foreach ($holds as $hold) {
                    $holdId = self::positiveInt($hold['holdid'] ?? null);
                    $barcode = self::escape($hold['barcode_nmbr'] ?? '');
                    $title = self::escape($hold['title'] ?? '');
                    $author = self::escape($hold['author'] ?? '');
                    $date = self::escape($hold['hold_begin_dt'] ?? '');
                    $content .= '<li>' . $title . ' — ' . $author . ' (' . $barcode . '), ' . $date
                        . '<form method="post" action="/circulation/holds">'
                        . '<input type="hidden" name="_csrf" value="' . $token . '">'
                        . '<input type="hidden" name="action" value="cancel">'
                        . '<input type="hidden" name="member_barcode" value="' . $memberCode . '">'
                        . '<input type="hidden" name="holdid" value="' . $holdId . '">'
                        . '<button type="submit">Remover reserva</button></form></li>';
                }
                $content .= '</ul>';
            }
        }

        return $content . '</html>';
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

        throw new \UnexpectedValueException('O banco retornou um identificador inválido de reserva.');
    }

    private static function escape(mixed $value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new \UnexpectedValueException('O banco retornou um valor inválido para exibição.');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
