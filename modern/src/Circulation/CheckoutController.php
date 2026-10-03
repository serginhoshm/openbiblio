<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use Closure;
use OpenBiblio\Modern\Auth\AccessGuard;
use OpenBiblio\Modern\Auth\SessionStore;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;

final class CheckoutController
{
    /**
     * @param Closure(): CheckoutRepository $repositoryFactory
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

    public function checkout(Request $request): Response
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

        $memberBarcode = $request->body['member_barcode'] ?? null;
        $copyBarcode = $request->body['copy_barcode'] ?? null;
        if (
            !is_string($memberBarcode)
            || !is_string($copyBarcode)
            || ($memberBarcode = trim($memberBarcode)) === ''
            || ($copyBarcode = trim($copyBarcode)) === ''
            || strlen($memberBarcode) > 20
            || strlen($copyBarcode) > 20
        ) {
            return new Response(
                $this->page('Informe códigos de barras válidos, com até 20 caracteres.'),
                400,
                ['Cache-Control' => 'no-store'],
            );
        }

        $repository = ($this->repositoryFactory)();
        if (!$repository instanceof CheckoutRepository) {
            throw new \UnexpectedValueException('A fábrica não retornou um repositório de circulação válido.');
        }

        try {
            $receipt = $repository->checkout($memberBarcode, $copyBarcode);
        } catch (CheckoutRejected $error) {
            return new Response($this->page($error->getMessage()), 422, ['Cache-Control' => 'no-store']);
        }

        $memberName = self::escape($receipt['member']['first_name'] . ' ' . $receipt['member']['last_name']);
        $copyCode = self::escape($receipt['copy']['barcode_nmbr']);
        $dueDate = self::escape($receipt['due_back_dt']);
        $content = '<p role="status">Empréstimo registrado para ' . $memberName
            . '. Exemplar ' . $copyCode . '; devolução até ' . $dueDate . '.</p>';

        return new Response($this->page('', $content), 200, ['Cache-Control' => 'no-store']);
    }

    private function authorize(): ?Response
    {
        return (new AccessGuard($this->session))->authorize('circulation', '/circulation/checkout');
    }

    private function page(string $error = '', string $content = ''): string
    {
        $message = $error === '' ? '' : '<p role="alert">' . self::escape($error) . '</p>';
        $csrf = self::escape($this->csrfToken());

        return '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Empréstimo — OpenBiblio</title><h1>Registrar empréstimo</h1>'
            . $message . $content
            . '<form method="post" action="/circulation/checkout">'
            . '<input type="hidden" name="_csrf" value="' . $csrf . '">'
            . '<label for="member_barcode">Código de barras do membro</label> '
            . '<input id="member_barcode" name="member_barcode" maxlength="20" required> '
            . '<label for="copy_barcode">Código de barras do exemplar</label> '
            . '<input id="copy_barcode" name="copy_barcode" maxlength="20" required> '
            . '<button type="submit">Registrar empréstimo</button></form></html>';
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

    private static function escape(mixed $value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new \UnexpectedValueException('O resultado do empréstimo contém um valor inválido para exibição.');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
