<?php

declare(strict_types=1);

namespace OpenBiblio\Modern;

use OpenBiblio\Modern\Catalog\BarcodeSearchRepository;
use OpenBiblio\Modern\Catalog\BibliographyController;
use OpenBiblio\Modern\Catalog\BibliographyRepository;
use OpenBiblio\Modern\Catalog\CatalogCopyController;
use OpenBiblio\Modern\Catalog\CatalogCopyRepository;
use OpenBiblio\Modern\Catalog\PublicCatalogSearchController;
use OpenBiblio\Modern\Circulation\MemberController;
use OpenBiblio\Modern\Circulation\MemberAccountController;
use OpenBiblio\Modern\Circulation\MemberAccountRepository;
use OpenBiblio\Modern\Circulation\MemberHistoryController;
use OpenBiblio\Modern\Circulation\MemberHistoryRepository;
use OpenBiblio\Modern\Circulation\MemberRepository;
use OpenBiblio\Modern\Circulation\MemberManagementController;
use OpenBiblio\Modern\Circulation\CheckoutController;
use OpenBiblio\Modern\Circulation\CheckoutRepository;
use OpenBiblio\Modern\Circulation\CheckinController;
use OpenBiblio\Modern\Circulation\CheckinRepository;
use OpenBiblio\Modern\Circulation\HoldController;
use OpenBiblio\Modern\Circulation\HoldRepository;
use OpenBiblio\Modern\Auth\AuthenticationController;
use OpenBiblio\Modern\Auth\AccessGuard;
use OpenBiblio\Modern\Auth\NativeSessionStore;
use OpenBiblio\Modern\Auth\SessionStore;
use OpenBiblio\Modern\Auth\StaffRepository;
use OpenBiblio\Modern\Database\ConnectionFactory;
use OpenBiblio\Modern\Database\DatabaseConfig;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;
use OpenBiblio\Modern\Http\Router;

final class Application
{
    private Router $router;

    private ?SessionStore $navigationSession;

    public function __construct(?SessionStore $navigationSession = null)
    {
        $this->navigationSession = $navigationSession;
        $this->router = new Router();
        $this->router->get('/', fn (Request $request): Response => new Response(
            '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
            . '<title>OpenBiblio — reescrita</title><h1>OpenBiblio</h1>'
            . '<p>Ambiente isolado da reescrita PHP. A versão legada permanece inalterada.</p>'
            . Navigation::dashboard($this->currentUser())
            . '<p>Administração geral e relatórios ainda estão em migração.</p>'
            . '</html>',
        ));
        $this->router->get('/health', static fn (Request $request): Response => Response::json([
            'status' => 'ok',
            'application' => 'openbiblio-modern',
        ]));
        $this->router->get('/opac', static function (Request $request): Response {
            $controller = new PublicCatalogSearchController(
                static fn (): BarcodeSearchRepository => new BarcodeSearchRepository(
                    (new ConnectionFactory())->connect(DatabaseConfig::fromEnvironment()),
                ),
            );

            return $controller->show($request);
        });
        $this->router->get('/login', static function (Request $request): Response {
            return self::authenticationController()->form($request);
        });
        $this->router->post('/login', static function (Request $request): Response {
            return self::authenticationController()->login($request);
        });
        $this->router->post('/logout', static function (Request $request): Response {
            return self::authenticationController()->logout($request);
        });
        $this->router->get('/logout', static function (Request $request): Response {
            return self::authenticationController()->logoutForm();
        });
        $this->router->get('/admin', static function (Request $request): Response {
            $access = (new AccessGuard(new NativeSessionStore()))->authorize('admin', '/admin');

            return $access ?? new Response('O módulo de administração está em migração.', 501);
        });
        $this->router->get('/catalog-admin', static function (Request $request): Response {
            $access = (new AccessGuard(new NativeSessionStore()))->authorize('catalog', '/catalog-admin');

            return $access ?? new Response(
                '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
                . '<title>Catálogo administrativo — OpenBiblio</title><h1>Catálogo administrativo</h1>'
                . '<a href="/catalog-admin/search">Localizar registro e administrar exemplares</a> '
                . '<a href="/catalog-admin/bibliographies/new">Cadastrar registro bibliográfico</a></html>',
            );
        });
        $this->router->get('/catalog-admin/bibliographies/new', static function (Request $request): Response {
            return self::bibliographyController()->newForm();
        });
        $this->router->post('/catalog-admin/bibliographies/new', static function (Request $request): Response {
            return self::bibliographyController()->create($request);
        });
        $this->router->get('/catalog-admin/bibliographies/edit', static function (Request $request): Response {
            return self::bibliographyController()->editForm($request);
        });
        $this->router->post('/catalog-admin/bibliographies/edit', static function (Request $request): Response {
            return self::bibliographyController()->update($request);
        });
        $this->router->get('/catalog-admin/bibliographies/delete', static function (Request $request): Response {
            return self::bibliographyController()->deleteConfirmation($request);
        });
        $this->router->post('/catalog-admin/bibliographies/delete', static function (Request $request): Response {
            return self::bibliographyController()->delete($request);
        });
        $this->router->get('/catalog-admin/search', static function (Request $request): Response {
            return self::catalogCopyController()->search($request);
        });
        $this->router->get('/catalog-admin/copies', static function (Request $request): Response {
            return self::catalogCopyController()->copies($request);
        });
        $this->router->post('/catalog-admin/copies', static function (Request $request): Response {
            return self::catalogCopyController()->create($request);
        });
        $this->router->get('/catalog-admin/copies/edit', static function (Request $request): Response {
            return self::catalogCopyController()->editForm($request);
        });
        $this->router->post('/catalog-admin/copies/edit', static function (Request $request): Response {
            return self::catalogCopyController()->updateCopy($request);
        });
        $this->router->get('/catalog-admin/copies/delete', static function (Request $request): Response {
            return self::catalogCopyController()->deleteConfirmation($request);
        });
        $this->router->post('/catalog-admin/copies/delete', static function (Request $request): Response {
            return self::catalogCopyController()->deleteCopy($request);
        });
        $this->router->get('/circulation', static function (Request $request): Response {
            $access = (new AccessGuard(new NativeSessionStore()))->authorize('circulation', '/circulation');

            return $access ?? new Response(
                '<!doctype html><html lang="pt-BR"><meta charset="utf-8">'
                . '<title>Circulação — OpenBiblio</title><h1>Circulação</h1>'
                . '<ul><li><a href="/circulation/member">Consultar membro</a></li>'
                . '<li><a href="/circulation/checkout">Registrar empréstimo</a></li>'
                . '<li><a href="/circulation/checkin">Registrar devolução</a></li>'
                . '<li><a href="/circulation/holds">Gerenciar reservas</a></li>'
                . '<li><a href="/circulation/members/new">Cadastrar membro</a></li></ul></html>',
            );
        });
        $this->router->get('/circulation/member', static function (Request $request): Response {
            $controller = new MemberController(
                static fn (): MemberRepository => new MemberRepository(
                    (new ConnectionFactory())->connect(DatabaseConfig::fromEnvironment()),
                ),
                new NativeSessionStore(),
            );

            return $controller->view($request);
        });
        $this->router->get('/circulation/account', static function (Request $request): Response {
            return self::memberAccountController()->view($request);
        });
        $this->router->get('/circulation/member/history', static function (Request $request): Response {
            return self::memberHistoryController()->view($request);
        });
        $this->router->post('/circulation/account/transactions/new', static function (Request $request): Response {
            return self::memberAccountController()->add($request);
        });
        $this->router->post('/circulation/account/transactions/delete', static function (Request $request): Response {
            return self::memberAccountController()->delete($request);
        });
        $this->router->get('/circulation/members/new', static function (Request $request): Response {
            return self::memberManagementController()->form();
        });
        $this->router->post('/circulation/members/new', static function (Request $request): Response {
            return self::memberManagementController()->create($request);
        });
        $this->router->get('/circulation/members/edit', static function (Request $request): Response {
            return self::memberManagementController()->editForm($request);
        });
        $this->router->post('/circulation/members/edit', static function (Request $request): Response {
            return self::memberManagementController()->update($request);
        });
        $this->router->get('/circulation/members/delete', static function (Request $request): Response {
            return self::memberManagementController()->deleteForm($request);
        });
        $this->router->post('/circulation/members/delete', static function (Request $request): Response {
            return self::memberManagementController()->delete($request);
        });
        $this->router->get('/circulation/checkout', static function (Request $request): Response {
            return self::checkoutController()->form();
        });
        $this->router->post('/circulation/checkout', static function (Request $request): Response {
            return self::checkoutController()->checkout($request);
        });
        $this->router->get('/circulation/checkin', static function (Request $request): Response {
            return self::checkinController()->view();
        });
        $this->router->post('/circulation/checkin', static function (Request $request): Response {
            return self::checkinController()->shelve($request);
        });
        $this->router->post('/circulation/checkin/complete', static function (Request $request): Response {
            return self::checkinController()->complete($request);
        });
        $this->router->get('/circulation/holds', static function (Request $request): Response {
            return self::holdController()->view($request);
        });
        $this->router->post('/circulation/holds', static function (Request $request): Response {
            return self::holdController()->change($request);
        });
        $this->router->get('/reports', static function (Request $request): Response {
            $access = (new AccessGuard(new NativeSessionStore()))->authorize('reports', '/reports');

            return $access ?? new Response('O módulo de relatórios está em migração.', 501);
        });
    }

    private static function authenticationController(): AuthenticationController
    {
        return new AuthenticationController(
            static fn (): StaffRepository => new StaffRepository(
                (new ConnectionFactory())->connect(DatabaseConfig::fromEnvironment()),
            ),
            new NativeSessionStore(),
        );
    }

    private static function checkoutController(): CheckoutController
    {
        return new CheckoutController(
            static fn (): CheckoutRepository => new CheckoutRepository(
                (new ConnectionFactory())->connect(DatabaseConfig::fromEnvironment()),
            ),
            new NativeSessionStore(),
        );
    }

    private static function memberManagementController(): MemberManagementController
    {
        return new MemberManagementController(
            static fn (): MemberRepository => new MemberRepository(
                (new ConnectionFactory())->connect(DatabaseConfig::fromEnvironment()),
            ),
            new NativeSessionStore(),
        );
    }

    private static function memberAccountController(): MemberAccountController
    {
        return new MemberAccountController(
            static fn (): MemberAccountRepository => new MemberAccountRepository(
                (new ConnectionFactory())->connect(DatabaseConfig::fromEnvironment()),
            ),
            new NativeSessionStore(),
        );
    }

    private static function memberHistoryController(): MemberHistoryController
    {
        return new MemberHistoryController(
            static fn (): MemberHistoryRepository => new MemberHistoryRepository(
                (new ConnectionFactory())->connect(DatabaseConfig::fromEnvironment()),
            ),
            new NativeSessionStore(),
        );
    }

    private static function checkinController(): CheckinController
    {
        return new CheckinController(
            static fn (): CheckinRepository => new CheckinRepository(
                (new ConnectionFactory())->connect(DatabaseConfig::fromEnvironment()),
            ),
            new NativeSessionStore(),
        );
    }

    private static function holdController(): HoldController
    {
        return new HoldController(
            static fn (): HoldRepository => new HoldRepository(
                (new ConnectionFactory())->connect(DatabaseConfig::fromEnvironment()),
            ),
            new NativeSessionStore(),
        );
    }

    private static function catalogCopyController(): CatalogCopyController
    {
        return new CatalogCopyController(
            static fn (): CatalogCopyRepository => new CatalogCopyRepository(
                (new ConnectionFactory())->connect(DatabaseConfig::fromEnvironment()),
            ),
            new NativeSessionStore(),
        );
    }

    private static function bibliographyController(): BibliographyController
    {
        return new BibliographyController(
            static fn (): BibliographyRepository => new BibliographyRepository(
                (new ConnectionFactory())->connect(DatabaseConfig::fromEnvironment()),
            ),
            new NativeSessionStore(),
        );
    }

    public function handle(Request $request): Response
    {
        $response = $this->router->dispatch($request);
        if ($response->statusCode !== 200
            || !str_contains(strtolower($response->headers['Content-Type'] ?? ''), 'text/html')) {
            return $response;
        }

        $menu = Navigation::menu($this->currentUser());
        $body = preg_replace_callback(
            '~</title>~i',
            static fn (array $match): string => $match[0] . $menu,
            $response->body,
            1,
            $count,
        );
        if ($count === 0 || $body === null) {
            return $response;
        }

        return new Response($body, $response->statusCode, $response->headers);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function currentUser(): ?array
    {
        $user = $this->session()->get('auth.user');

        return is_array($user) ? $user : null;
    }

    private function session(): SessionStore
    {
        return $this->navigationSession ??= new NativeSessionStore();
    }
}
