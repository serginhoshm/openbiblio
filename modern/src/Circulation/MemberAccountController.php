<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Circulation;

use Closure;
use OpenBiblio\Modern\Auth\AccessGuard;
use OpenBiblio\Modern\Auth\SessionStore;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;

final class MemberAccountController
{
    /**
     * @param Closure(): MemberAccountRepository $repositoryFactory
     */
    public function __construct(
        private readonly Closure $repositoryFactory,
        private readonly SessionStore $session,
    ) {
    }

    public function view(Request $request): Response
    {
        $access = (new AccessGuard($this->session))->authorize('circulation', '/circulation/account');
        if ($access !== null) {
            return $access;
        }
        $memberId = self::positiveInt($request->query['mbrid'] ?? null);
        if ($memberId === null) {
            return new Response('Informe um identificador válido de membro.', 400);
        }
        $repository = $this->repository();
        if (!$repository->memberExists($memberId)) {
            return new Response('Membro não encontrado.', 404);
        }

        return new Response($this->page(
            $repository,
            $memberId,
            $repository->transactions($memberId),
        ));
    }

    public function add(Request $request): Response
    {
        $access = (new AccessGuard($this->session))->authorize('circulation', '/circulation/account');
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
        $memberId = self::positiveInt($request->body['mbrid'] ?? null);
        if ($memberId === null) {
            return new Response('Informe um identificador válido de membro.', 400);
        }
        $user = $this->session->get('auth.user');
        $staffUserId = is_array($user) ? self::positiveInt($user['userid'] ?? null) : null;
        if ($staffUserId === null) {
            return new Response('A sessão de funcionário é inválida; entre novamente.', 401);
        }

        $type = $request->body['transaction_type_cd'] ?? null;
        $amount = $request->body['amount'] ?? null;
        $description = $request->body['description'] ?? null;
        if (!is_string($type) || !is_string($amount) || !is_string($description)) {
            return new Response('Os dados da transação são inválidos.', 400);
        }
        $repository = $this->repository();
        try {
            $repository->addTransaction($memberId, $staffUserId, $type, $amount, $description);
        } catch (MemberAccountRejected $error) {
            return new Response(
                $this->page(
                    $repository,
                    $memberId,
                    $repository->transactions($memberId),
                    $error->getMessage(),
                    ['transaction_type_cd' => $type, 'amount' => $amount, 'description' => $description],
                ),
                422,
                ['Cache-Control' => 'no-store'],
            );
        }

        return new Response('', 303, ['Location' => '/circulation/account?mbrid=' . $memberId]);
    }

    public function delete(Request $request): Response
    {
        $access = (new AccessGuard($this->session))->authorize('circulation', '/circulation/account');
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
        $memberId = self::positiveInt($request->body['mbrid'] ?? null);
        $transactionId = self::positiveInt($request->body['transid'] ?? null);
        if ($memberId === null || $transactionId === null) {
            return new Response('Informe identificadores válidos de membro e transação.', 400);
        }
        try {
            $this->repository()->deleteTransaction($memberId, $transactionId);
        } catch (MemberAccountRejected $error) {
            return new Response(self::escape($error->getMessage()), 404);
        }

        return new Response('', 303, ['Location' => '/circulation/account?mbrid=' . $memberId]);
    }

    private function repository(): MemberAccountRepository
    {
        $repository = ($this->repositoryFactory)();
        if (!$repository instanceof MemberAccountRepository) {
            throw new \UnexpectedValueException('A fábrica não retornou um repositório de conta válido.');
        }

        return $repository;
    }

    private function page(
        MemberAccountRepository $repository,
        int $memberId,
        array $transactions,
        string $error = '',
        array $values = [],
    ): string {
        $csrf = self::escape($this->csrfToken());
        $content = '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>Conta do membro — OpenBiblio</title><h1>Conta do membro</h1>';
        if ($error !== '') {
            $content .= '<p role="alert">' . self::escape($error) . '</p>';
        }
        $content .= '<form method="post" action="/circulation/account/transactions/new">'
            . '<input type="hidden" name="_csrf" value="' . $csrf . '">'
            . '<input type="hidden" name="mbrid" value="' . $memberId . '">'
            . '<label for="transaction_type_cd">Tipo de transação</label> '
            . '<select id="transaction_type_cd" name="transaction_type_cd" required>';
        foreach ($repository->transactionTypes() as $type) {
            if (!is_string($type['code'] ?? null) || !is_string($type['description'] ?? null)) {
                throw new \UnexpectedValueException('O banco retornou um tipo de transação inválido.');
            }
            $selected = ($values['transaction_type_cd'] ?? '') === $type['code'] ? ' selected' : '';
            $content .= '<option value="' . self::escape($type['code']) . '"' . $selected . '>'
                . self::escape($type['description']) . '</option>';
        }
        $content .= '</select> <label for="description">Descrição</label> '
            . '<input id="description" name="description" maxlength="128" required value="'
            . self::escape($values['description'] ?? '') . '"> '
            . '<label for="amount">Valor</label> '
            . '<input id="amount" name="amount" inputmode="decimal" maxlength="9" required value="'
            . self::escape($values['amount'] ?? '') . '"> '
            . '<button type="submit">Adicionar transação</button></form>'
            . '<h2>Transações do membro</h2><table><thead><tr>'
            . '<th>Ação</th><th>Data</th><th>Tipo</th><th>Descrição</th><th>Valor</th><th>Saldo</th>'
            . '</tr></thead><tbody>';
        $balance = 0;
        if ($transactions === []) {
            $content .= '<tr><td colspan="6">Nenhuma transação encontrada.</td></tr>';
        } else {
            $content .= '<tr><td colspan="5">Saldo inicial</td><td>' . self::formatCents($balance) . '</td></tr>';
            foreach ($transactions as $transaction) {
                $transactionId = self::positiveInt($transaction['transid'] ?? null);
                if ($transactionId === null) {
                    throw new \UnexpectedValueException('O banco retornou um identificador de transação inválido.');
                }
                $amount = self::amountCents($transaction['amount'] ?? null);
                if (($amount > 0 && $balance > PHP_INT_MAX - $amount)
                    || ($amount < 0 && $balance < PHP_INT_MIN - $amount)) {
                    throw new \UnexpectedValueException('O saldo acumulado excede o intervalo numérico permitido.');
                }
                $balance += $amount;
                $content .= '<tr><td><form method="post" action="/circulation/account/transactions/delete">'
                    . '<input type="hidden" name="_csrf" value="' . $csrf . '">'
                    . '<input type="hidden" name="mbrid" value="' . $memberId . '">'
                    . '<input type="hidden" name="transid" value="' . $transactionId . '">'
                    . '<button type="submit">Remover</button></form></td>'
                    . '<td>' . self::escape($transaction['create_dt'] ?? '') . '</td>'
                    . '<td>' . self::escape($transaction['transaction_type_desc'] ?? '') . '</td>'
                    . '<td>' . self::escape($transaction['description'] ?? '') . '</td>'
                    . '<td>' . self::formatCents($amount) . '</td>'
                    . '<td>' . self::formatCents($balance) . '</td></tr>';
            }
        }

        return $content . '</tbody></table></html>';
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

    private function validCsrf(Request $request): bool
    {
        $submittedToken = $request->body['_csrf'] ?? null;
        $expectedToken = $this->session->get('auth.csrf');

        return is_string($submittedToken)
            && is_string($expectedToken)
            && hash_equals($expectedToken, $submittedToken);
    }

    private static function positiveInt(mixed $value): ?int
    {
        if (!is_int($value) && !is_string($value)) {
            return null;
        }
        $parsed = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $parsed === false ? null : $parsed;
    }

    private static function amountCents(mixed $value): int
    {
        if (!is_string($value)
            || preg_match('/\A(-?)([0-9]+)(?:\.([0-9]{1,2}))?\z/', $value, $matches) !== 1) {
            throw new \UnexpectedValueException('O banco retornou um valor monetário inválido.');
        }
        $whole = (int) $matches[2];
        if ($whole > 999999) {
            throw new \UnexpectedValueException('O valor monetário excede o intervalo permitido.');
        }
        $fraction = (int) str_pad($matches[3] ?? '', 2, '0');
        $cents = $whole * 100 + $fraction;

        return ($matches[1] ?? '') === '-' ? -$cents : $cents;
    }

    private static function formatCents(int $cents): string
    {
        if ($cents === PHP_INT_MIN) {
            throw new \UnexpectedValueException('O saldo excede o intervalo numérico permitido.');
        }
        $negative = $cents < 0;
        $absolute = abs($cents);
        $whole = intdiv($absolute, 100);
        $fraction = str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);

        return ($negative ? '-' : '') . number_format($whole, 0, ',', '.') . ',' . $fraction;
    }

    private static function escape(mixed $value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new \UnexpectedValueException('A conta retornou um valor que não pode ser exibido como texto.');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
