<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use Closure;
use OpenBiblio\Modern\Auth\AccessGuard;
use OpenBiblio\Modern\Auth\SessionStore;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;

final class CheckinController
{
    /**
     * @param Closure(): CheckinRepository $repositoryFactory
     */
    public function __construct(
        private readonly Closure $repositoryFactory,
        private readonly SessionStore $session,
    ) {
    }

    public function view(): Response
    {
        $access = $this->authorize();
        if ($access !== null) {
            return $access;
        }

        $repository = $this->repository();

        return new Response($this->page($repository->getShelvingCart()), 200, ['Cache-Control' => 'no-store']);
    }

    public function shelve(Request $request): Response
    {
        $access = $this->authorize();
        if ($access !== null) {
            return $access;
        }
        if (!$this->validCsrf($request)) {
            return $this->csrfError();
        }

        $barcode = $request->body['barcode'] ?? null;
        if (!is_string($barcode) || ($barcode = trim($barcode)) === '' || strlen($barcode) > 20) {
            return new Response(
                $this->page([], 'Informe um código de barras válido, com até 20 caracteres.'),
                400,
                ['Cache-Control' => 'no-store'],
            );
        }

        $user = $this->session->get('auth.user');
        $staffUserId = is_array($user) && is_int($user['userid'] ?? null) ? $user['userid'] : 0;
        if ($staffUserId < 1) {
            return new Response('A sessão de funcionário é inválida; entre novamente.', 401);
        }

        $repository = $this->repository();
        try {
            $receipt = $repository->shelve($barcode, $staffUserId);
        } catch (CheckinRejected $error) {
            return new Response(
                $this->page($repository->getShelvingCart(), $error->getMessage()),
                422,
                ['Cache-Control' => 'no-store'],
            );
        }

        $message = 'Devolução de ' . $receipt['barcode_nmbr'] . ' registrada.';
        if ($receipt['member'] !== null) {
            $message .= ' Membro: ' . $receipt['member']['first_name'] . ' ' . $receipt['member']['last_name'] . '.';
        }
        if ($receipt['late_days'] > 0) {
            $message .= ' Atraso: ' . $receipt['late_days'] . ' dia(s).';
        }
        if ($receipt['fee'] > 0) {
            $message .= ' Multa lançada: ' . number_format($receipt['fee'], 2, ',', '.') . '.';
        }
        if ($receipt['status_cd'] === 'hld') {
            $message .= ' O exemplar ficou separado para uma reserva.';
        } else {
            $message .= ' O exemplar foi colocado no carrinho para guardar na estante.';
        }

        return new Response(
            $this->page($repository->getShelvingCart(), '', $message),
            200,
            ['Cache-Control' => 'no-store'],
        );
    }

    public function complete(Request $request): Response
    {
        $access = $this->authorize();
        if ($access !== null) {
            return $access;
        }
        if (!$this->validCsrf($request)) {
            return $this->csrfError();
        }

        $allValue = $request->body['all'] ?? 'N';
        if (!is_string($allValue) || !in_array($allValue, ['Y', 'N'], true)) {
            return new Response('A opção de devolução em lote é inválida.', 400);
        }
        $all = $allValue === 'Y';
        $submittedCopies = $request->body['copies'] ?? [];
        if (!is_array($submittedCopies)) {
            return new Response('A lista de exemplares selecionados é inválida.', 400);
        }

        $copies = [];
        foreach ($submittedCopies as $copy) {
            if (
                !is_array($copy)
                || !is_string($copy['bibid'] ?? null)
                || preg_match('/\A[0-9]+\z/', $copy['bibid']) !== 1
                || !is_string($copy['copyid'] ?? null)
                || preg_match('/\A[0-9]+\z/', $copy['copyid']) !== 1
            ) {
                return new Response('A lista de exemplares selecionados é inválida.', 400);
            }
            if (($copy['selected'] ?? null) !== 'Y') {
                continue;
            }
            $bibId = filter_var($copy['bibid'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $copyId = filter_var($copy['copyid'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($bibId === false || $copyId === false) {
                return new Response('A lista de exemplares selecionados é inválida.', 400);
            }
            $copies[] = ['bibid' => $bibId, 'copyid' => $copyId];
        }

        $repository = $this->repository();
        try {
            $changed = $repository->complete($copies, $all);
        } catch (CheckinRejected $error) {
            return new Response(
                $this->page($repository->getShelvingCart(), $error->getMessage()),
                422,
                ['Cache-Control' => 'no-store'],
            );
        }

        return new Response(
            $this->page($repository->getShelvingCart(), '', $changed . ' exemplar(es) guardado(s) na estante.'),
            200,
            ['Cache-Control' => 'no-store'],
        );
    }

    private function authorize(): ?Response
    {
        return (new AccessGuard($this->session))->authorize('circulation', '/circulation/checkin');
    }

    private function repository(): CheckinRepository
    {
        $repository = ($this->repositoryFactory)();
        if (!$repository instanceof CheckinRepository) {
            throw new \UnexpectedValueException('A fábrica não retornou um repositório de devoluções válido.');
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

    private function csrfError(): Response
    {
        return new Response(
            'Token de formulário inválido. Recarregue a página e tente novamente.',
            400,
            ['Cache-Control' => 'no-store'],
        );
    }

    /**
     * @param list<array<string, mixed>> $cart
     */
    private function page(array $cart, string $error = '', string $notice = ''): string
    {
        $token = self::escape($this->csrfToken());
        $messages = '';
        if ($error !== '') {
            $messages .= '<p role="alert">' . self::escape($error) . '</p>';
        }
        if ($notice !== '') {
            $messages .= '<p role="status">' . self::escape($notice) . '</p>';
        }

        $tableRows = '';
        foreach ($cart as $index => $copy) {
            $bibId = self::positiveInt($copy['bibid'] ?? null);
            $copyId = self::positiveInt($copy['copyid'] ?? null);
            $date = self::escape($copy['status_begin_dt'] ?? '');
            $barcode = self::escape($copy['barcode_nmbr'] ?? '');
            $title = self::escape($copy['title'] ?? '');
            $author = self::escape($copy['author'] ?? '');
            $tableRows .= '<tr><td><input type="checkbox" name="copies[' . $index . '][selected]" value="Y"'
                . ' aria-label="Selecionar ' . $barcode . '">'
                . '<input type="hidden" name="copies[' . $index . '][bibid]" value="' . $bibId . '">'
                . '<input type="hidden" name="copies[' . $index . '][copyid]" value="' . $copyId . '"></td>'
                . '<td>' . $date . '</td><td>' . $barcode . '</td><td>' . $title . '</td><td>' . $author . '</td></tr>';
        }
        if ($tableRows === '') {
            $tableRows = '<tr><td colspan="5">O carrinho de devoluções está vazio.</td></tr>';
        }

        return '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Devolução — OpenBiblio</title><h1>Registrar devolução</h1>'
            . $messages
            . '<form method="post" action="/circulation/checkin">'
            . '<input type="hidden" name="_csrf" value="' . $token . '">'
            . '<label for="barcode">Código de barras do exemplar</label> '
            . '<input id="barcode" name="barcode" maxlength="20" required> '
            . '<button type="submit">Registrar devolução</button></form>'
            . '<h2>Exemplares para guardar na estante</h2>'
            . '<form method="post" action="/circulation/checkin/complete">'
            . '<input type="hidden" name="_csrf" value="' . $token . '">'
            . '<table><thead><tr><th>Selecionar</th><th>Data</th><th>Barcode</th><th>Título</th><th>Autor</th></tr></thead>'
            . '<tbody>' . $tableRows . '</tbody></table>'
            . '<button type="submit" name="all" value="N">Guardar selecionados na estante</button> '
            . '<button type="submit" name="all" value="Y">Guardar todos na estante</button></form></html>';
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

        throw new \UnexpectedValueException('O banco retornou um identificador inválido de exemplar.');
    }

    private static function escape(mixed $value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new \UnexpectedValueException('O banco retornou um valor inválido para exibição.');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
