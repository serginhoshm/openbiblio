<?php

declare(strict_types=1);

use OpenBiblio\Modern\Application;
use OpenBiblio\Modern\Auth\AuthenticationController;
use OpenBiblio\Modern\Auth\AccessGuard;
use OpenBiblio\Modern\Auth\SessionStore;
use OpenBiblio\Modern\Auth\StaffRepository;
use OpenBiblio\Modern\Catalog\BarcodeSearchRepository;
use OpenBiblio\Modern\Catalog\BibliographyController;
use OpenBiblio\Modern\Catalog\BibliographyRejected;
use OpenBiblio\Modern\Catalog\BibliographyRepository;
use OpenBiblio\Modern\Catalog\CatalogCopyController;
use OpenBiblio\Modern\Catalog\CatalogCopyRejected;
use OpenBiblio\Modern\Catalog\CatalogCopyRepository;
use OpenBiblio\Modern\Catalog\PublicCatalogSearchController;
use OpenBiblio\Modern\Catalog\SearchTerms;
use OpenBiblio\Modern\Database\ConnectionFactory;
use OpenBiblio\Modern\Database\DatabaseConfig;
use OpenBiblio\Modern\Circulation\MemberController;
use OpenBiblio\Modern\Circulation\MemberAccountController;
use OpenBiblio\Modern\Circulation\MemberAccountRejected;
use OpenBiblio\Modern\Circulation\MemberAccountRepository;
use OpenBiblio\Modern\Circulation\MemberHistoryController;
use OpenBiblio\Modern\Circulation\MemberHistoryRepository;
use OpenBiblio\Modern\Circulation\MemberManagementController;
use OpenBiblio\Modern\Circulation\MemberRejected;
use OpenBiblio\Modern\Circulation\MemberRepository;
use OpenBiblio\Modern\Circulation\CheckoutRejected;
use OpenBiblio\Modern\Circulation\CheckoutController;
use OpenBiblio\Modern\Circulation\CheckoutRepository;
use OpenBiblio\Modern\Circulation\CheckinController;
use OpenBiblio\Modern\Circulation\CheckinRejected;
use OpenBiblio\Modern\Circulation\CheckinRepository;
use OpenBiblio\Modern\Circulation\HoldController;
use OpenBiblio\Modern\Circulation\HoldRejected;
use OpenBiblio\Modern\Circulation\HoldRepository;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;
use OpenBiblio\Modern\Http\Router;

require_once dirname(__DIR__) . '/src/Http/Request.php';
require_once dirname(__DIR__) . '/src/Http/Response.php';
require_once dirname(__DIR__) . '/src/Http/Router.php';
require_once dirname(__DIR__) . '/src/Auth/SessionStore.php';
require_once dirname(__DIR__) . '/src/Auth/NativeSessionStore.php';
require_once dirname(__DIR__) . '/src/Auth/StaffRepository.php';
require_once dirname(__DIR__) . '/src/Auth/AuthenticationController.php';
require_once dirname(__DIR__) . '/src/Auth/AccessGuard.php';
require_once dirname(__DIR__) . '/src/Database/DatabaseConfig.php';
require_once dirname(__DIR__) . '/src/Database/ConnectionFactory.php';
require_once dirname(__DIR__) . '/src/Catalog/BarcodeSearchRepository.php';
require_once dirname(__DIR__) . '/src/Catalog/BibliographyRejected.php';
require_once dirname(__DIR__) . '/src/Catalog/BibliographyRepository.php';
require_once dirname(__DIR__) . '/src/Catalog/BibliographyController.php';
require_once dirname(__DIR__) . '/src/Catalog/CatalogCopyRejected.php';
require_once dirname(__DIR__) . '/src/Catalog/CatalogCopyRepository.php';
require_once dirname(__DIR__) . '/src/Catalog/CatalogCopyController.php';
require_once dirname(__DIR__) . '/src/Catalog/SearchTerms.php';
require_once dirname(__DIR__) . '/src/Catalog/PublicCatalogSearchController.php';
require_once dirname(__DIR__) . '/src/Circulation/MemberRepository.php';
require_once dirname(__DIR__) . '/src/Circulation/MemberController.php';
require_once dirname(__DIR__) . '/src/Circulation/MemberRejected.php';
require_once dirname(__DIR__) . '/src/Circulation/MemberManagementController.php';
require_once dirname(__DIR__) . '/src/Circulation/MemberAccountRejected.php';
require_once dirname(__DIR__) . '/src/Circulation/MemberAccountRepository.php';
require_once dirname(__DIR__) . '/src/Circulation/MemberAccountController.php';
require_once dirname(__DIR__) . '/src/Circulation/MemberHistoryRepository.php';
require_once dirname(__DIR__) . '/src/Circulation/MemberHistoryController.php';
require_once dirname(__DIR__) . '/src/Circulation/CheckoutRejected.php';
require_once dirname(__DIR__) . '/src/Circulation/CheckoutRepository.php';
require_once dirname(__DIR__) . '/src/Circulation/CheckoutController.php';
require_once dirname(__DIR__) . '/src/Circulation/CheckinRejected.php';
require_once dirname(__DIR__) . '/src/Circulation/CheckinRepository.php';
require_once dirname(__DIR__) . '/src/Circulation/CheckinController.php';
require_once dirname(__DIR__) . '/src/Circulation/HoldRejected.php';
require_once dirname(__DIR__) . '/src/Circulation/HoldRepository.php';
require_once dirname(__DIR__) . '/src/Circulation/HoldController.php';
require_once dirname(__DIR__) . '/src/Navigation.php';
require_once dirname(__DIR__) . '/src/Application.php';
require_once __DIR__ . '/TestSupport.php';

$tests = [
    'application registers checkout route behind circulation login' => static function (): void {
        $response = (new Application())->handle(new Request('GET', '/circulation/checkout'));

        assertSame(303, $response->statusCode);
        assertSame(
            '/login?return=%2Fcirculation%2Fcheckout',
            $response->headers['Location'],
        );
    },
    'application registers checkin and hold routes behind circulation login' => static function (): void {
        $application = new Application();
        $checkin = $application->handle(new Request('GET', '/circulation/checkin'));
        $holds = $application->handle(new Request('GET', '/circulation/holds'));

        assertSame(303, $checkin->statusCode);
        assertSame('/login?return=%2Fcirculation%2Fcheckin', $checkin->headers['Location']);
        assertSame(303, $holds->statusCode);
        assertSame('/login?return=%2Fcirculation%2Fholds', $holds->headers['Location']);
    },
    'application registers member account routes behind circulation login' => static function (): void {
        $response = (new Application())->handle(new Request('GET', '/circulation/account', ['mbrid' => '9']));

        assertSame(303, $response->statusCode);
        assertSame('/login?return=%2Fcirculation%2Faccount', $response->headers['Location']);
    },
    'application protects member removal routes behind circulation login' => static function (): void {
        $application = new Application();
        $form = $application->handle(new Request(
            'GET',
            '/circulation/members/delete',
            ['mbrid' => '9'],
        ));
        $delete = $application->handle(new Request('POST', '/circulation/members/delete'));

        assertSame(303, $form->statusCode);
        assertSame('/login?return=%2Fcirculation%2Fmembers%2Fdelete', $form->headers['Location']);
        assertSame(303, $delete->statusCode);
        assertSame('/login?return=%2Fcirculation%2Fmembers%2Fdelete', $delete->headers['Location']);
    },
    'application protects member history behind circulation login' => static function (): void {
        $response = (new Application())->handle(new Request(
            'GET',
            '/circulation/member/history',
            ['mbrid' => '9'],
        ));

        assertSame(303, $response->statusCode);
        assertSame('/login?return=%2Fcirculation%2Fmember%2Fhistory', $response->headers['Location']);
    },
    'application protects catalog copy editing with catalog permission' => static function (): void {
        $application = new Application();
        $response = $application->handle(new Request(
            'GET',
            '/catalog-admin/copies/edit',
            ['bibid' => '42', 'copyid' => '7'],
        ));
        $delete = $application->handle(new Request(
            'POST',
            '/catalog-admin/copies/delete',
            [],
            ['bibid' => '42', 'copyid' => '7'],
        ));

        assertSame(303, $response->statusCode);
        assertSame('/login?return=%2Fcatalog-admin%2Fcopies%2Fedit', $response->headers['Location']);
        assertSame(303, $delete->statusCode);
        assertSame('/login?return=%2Fcatalog-admin%2Fcopies%2Fdelete', $delete->headers['Location']);
    },
    'application protects bibliography create and edit routes with catalog permission' => static function (): void {
        $application = new Application();
        $newForm = $application->handle(new Request('GET', '/catalog-admin/bibliographies/new'));
        $editForm = $application->handle(new Request(
            'GET',
            '/catalog-admin/bibliographies/edit',
            ['bibid' => '42'],
        ));
        $create = $application->handle(new Request('POST', '/catalog-admin/bibliographies/new'));
        $delete = $application->handle(new Request(
            'POST',
            '/catalog-admin/bibliographies/delete',
            [],
            ['bibid' => '42'],
        ));

        assertSame(303, $newForm->statusCode);
        assertSame('/login?return=%2Fcatalog-admin%2Fbibliographies%2Fnew', $newForm->headers['Location']);
        assertSame(303, $editForm->statusCode);
        assertSame('/login?return=%2Fcatalog-admin%2Fbibliographies%2Fedit', $editForm->headers['Location']);
        assertSame(303, $create->statusCode);
        assertSame(303, $delete->statusCode);
    },
    'health endpoint returns JSON without database access' => static function (): void {
        $response = (new Application())->handle(new Request('GET', '/health'));

        assertSame(200, $response->statusCode);
        assertSame('application/json; charset=UTF-8', $response->headers['Content-Type']);
        assertSame(
            ['status' => 'ok', 'application' => 'openbiblio-modern'],
            json_decode($response->body, true, 512, JSON_THROW_ON_ERROR),
        );
    },
    'home page exposes available modules and labels migration boundaries' => static function (): void {
        $response = (new Application(new ArraySessionStore()))->handle(new Request('GET', '/'));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'href="/opac"'));
        assertSame(true, str_contains($response->body, 'href="/circulation"'));
        assertSame(true, str_contains($response->body, 'href="/catalog-admin"'));
        assertSame(true, str_contains($response->body, 'href="/login"'));
        assertSame(true, str_contains($response->body, 'Administração geral e relatórios ainda estão em migração.'));
    },
    'html pages share navigation and only show authorized modules' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => [
                'username' => 'cataloguer',
                'first_name' => '<Ana>',
                'last_name' => 'Silva',
                'permissions' => ['catalog' => true, 'circulation' => false],
            ],
        ]);
        $response = (new Application($session))->handle(new Request('GET', '/opac'));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, '<nav aria-label="Navegação principal">'));
        assertSame(true, str_contains($response->body, 'href="/">Início</a>'));
        assertSame(true, str_contains($response->body, 'href="/catalog-admin">Catálogo administrativo</a>'));
        assertSame(false, str_contains($response->body, 'href="/circulation">'));
        assertSame(true, str_contains($response->body, 'Conectado: &lt;Ana&gt; Silva'));
        assertSame(true, str_contains($response->body, 'href="/logout">Sair</a>'));

        $dashboard = (new Application($session))->handle(new Request('GET', '/'));
        assertSame(true, str_contains($dashboard->body, 'href="/catalog-admin">Catálogo administrativo</a>'));
        assertSame(false, str_contains($dashboard->body, 'href="/circulation">'));
        assertSame(true, str_contains($dashboard->body, 'Conectado: &lt;Ana&gt; Silva'));
    },
    'OPAC form is available without opening the database' => static function (): void {
        $response = (new Application())->handle(new Request('GET', '/opac'));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'name="barcode"'));
    },
    'unknown routes return not found' => static function (): void {
        $response = (new Application())->handle(new Request('GET', '/missing'));

        assertSame(404, $response->statusCode);
    },
    'known route rejects unsupported methods' => static function (): void {
        $response = (new Application())->handle(new Request('POST', '/health'));

        assertSame(405, $response->statusCode);
        assertSame('GET', $response->headers['Allow']);
    },
    'login form emits a CSRF token without connecting to the database' => static function (): void {
        $session = new ArraySessionStore();
        $controller = new AuthenticationController(
            static function (): StaffRepository {
                throw new RuntimeException('The login form must not access the database.');
            },
            $session,
        );

        $response = $controller->form(new Request('GET', '/login'));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'name="_csrf"'));
        assertSame(64, strlen((string) $session->get('auth.csrf')));
        assertSame(false, str_contains($response->body, 'name="password" value='));
    },
    'login rejects invalid CSRF token before accessing credentials' => static function (): void {
        $controller = new AuthenticationController(
            static function (): StaffRepository {
                throw new RuntimeException('Invalid CSRF tokens must not reach the repository.');
            },
            new ArraySessionStore(),
        );

        assertSame(400, $controller->login(new Request('POST', '/login'))->statusCode);
    },
    'successful login preserves legacy database credential check and regenerates session' => static function (): void {
        $connection = new CapturingPdo([[
            'userid' => 7,
            'username' => 'librarian',
            'first_name' => 'Ana',
            'last_name' => 'Silva',
            'suspended_flg' => 'N',
            'admin_flg' => 'N',
            'circ_flg' => 'Y',
            'circ_mbr_flg' => 'N',
            'catalog_flg' => 'Y',
            'reports_flg' => 'N',
        ]]);
        $session = new ArraySessionStore(['auth.csrf' => str_repeat('a', 64)]);
        $controller = new AuthenticationController(
            static fn (): StaffRepository => new StaffRepository($connection),
            $session,
        );

        $response = $controller->login(new Request('POST', '/login', [], [
            '_csrf' => str_repeat('a', 64),
            'username' => ' librarian ',
            'password' => 'SeCrEt',
            'return' => '/catalog',
        ]));

        assertSame(303, $response->statusCode);
        assertSame('/catalog', $response->headers['Location']);
        assertSame(true, $session->regenerated);
        assertSame('librarian', $session->get('auth.user')['username']);
        assertSame(true, $session->get('auth.user')['permissions']['circulation']);
        assertSame(false, $session->get('auth.user')['permissions']['admin']);
        assertSame(0, $session->get('auth.failed_attempts'));
        assertSame(
            'SELECT userid, username, first_name, last_name, suspended_flg, '
            . 'admin_flg, circ_flg, circ_mbr_flg, catalog_flg, reports_flg '
            . 'FROM staff WHERE username = LOWER(:username) AND pwd = MD5(LOWER(:password)) LIMIT 1',
            $connection->preparedSql,
        );
        assertSame([
            ':username' => ['librarian', PDO::PARAM_STR],
            ':password' => ['SeCrEt', PDO::PARAM_STR],
        ], $connection->statement->bindings);
    },
    'suspended account cannot create an authenticated session' => static function (): void {
        $connection = new CapturingPdo([[
            'userid' => 7,
            'username' => 'librarian',
            'suspended_flg' => 'Y',
        ]]);
        $session = new ArraySessionStore(['auth.csrf' => str_repeat('b', 64)]);
        $controller = new AuthenticationController(
            static fn (): StaffRepository => new StaffRepository($connection),
            $session,
        );

        $response = $controller->login(new Request('POST', '/login', [], [
            '_csrf' => str_repeat('b', 64),
            'username' => 'librarian',
            'password' => 'SeCrEt',
        ]));

        assertSame(303, $response->statusCode);
        assertSame('/login', $response->headers['Location']);
        assertSame(null, $session->get('auth.user'));
        assertSame('Esta conta está suspensa.', $session->get('auth.error'));
    },
    'external return URLs are replaced with local home path' => static function (): void {
        $session = new ArraySessionStore(['auth.csrf' => str_repeat('c', 64)]);
        $controller = new AuthenticationController(
            static fn (): StaffRepository => new StaffRepository(new CapturingPdo([[
                'userid' => 7,
                'username' => 'librarian',
                'suspended_flg' => 'N',
            ]])),
            $session,
        );

        $response = $controller->login(new Request('POST', '/login', [], [
            '_csrf' => str_repeat('c', 64),
            'username' => 'librarian',
            'password' => 'SeCrEt',
            'return' => '//attacker.invalid',
        ]));

        assertSame('/', $response->headers['Location']);
    },
    'logout requires CSRF validation and invalidates the session' => static function (): void {
        $session = new ArraySessionStore([
            'auth.csrf' => str_repeat('d', 64),
            'auth.user' => ['userid' => 7],
        ]);
        $controller = new AuthenticationController(
            static function (): StaffRepository {
                throw new RuntimeException('Logout does not require a database connection.');
            },
            $session,
        );

        assertSame(400, $controller->logout(new Request('POST', '/logout'))->statusCode);
        $response = $controller->logout(new Request('POST', '/logout', [], [
            '_csrf' => str_repeat('d', 64),
        ]));
        assertSame(303, $response->statusCode);
        assertSame('/', $response->headers['Location']);
        assertSame(true, $session->destroyed);
        assertSame(null, $session->get('auth.user'));
    },
    'logout form submits the session CSRF token only with POST' => static function (): void {
        $session = new ArraySessionStore();
        $controller = new AuthenticationController(
            static function (): StaffRepository {
                throw new RuntimeException('Logout form does not need a database connection.');
            },
            $session,
        );

        $response = $controller->logoutForm();

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'method="post" action="/logout"'));
        assertSame(true, str_contains($response->body, (string) $session->get('auth.csrf')));
    },
    'third failed credential attempt suspends the legacy account' => static function (): void {
        $connection = new CapturingPdo([]);
        $session = new ArraySessionStore([
            'auth.csrf' => str_repeat('e', 64),
            'auth.failed_attempts' => 2,
        ]);
        $controller = new AuthenticationController(
            static fn (): StaffRepository => new StaffRepository($connection),
            $session,
        );

        $response = $controller->login(new Request('POST', '/login', [], [
            '_csrf' => str_repeat('e', 64),
            'username' => 'unknown',
            'password' => 'bad',
        ]));

        assertSame(303, $response->statusCode);
        assertSame("UPDATE staff SET suspended_flg = 'Y' WHERE username = LOWER(:username)", $connection->preparedSql);
        assertSame([':username' => ['unknown', PDO::PARAM_STR]], $connection->statement->bindings);
        assertSame(0, $session->get('auth.failed_attempts'));
        assertSame('A conta foi suspensa após tentativas inválidas.', $session->get('auth.error'));
    },
    'access guard redirects anonymous users with a local return path' => static function (): void {
        $response = (new AccessGuard(new ArraySessionStore()))->authorize('admin', '/admin');

        assertSame(303, $response?->statusCode);
        assertSame('/login?return=%2Fadmin', $response?->headers['Location']);
    },
    'access guard blocks authenticated users without the required permission' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => ['permissions' => ['admin' => false, 'catalog' => true]],
        ]);

        $response = (new AccessGuard($session))->authorize('admin', '/admin');

        assertSame(403, $response?->statusCode);
    },
    'access guard grants the exact legacy permission and rejects unknown permissions' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => ['permissions' => ['catalog' => true, 'admin' => false]],
        ]);
        $guard = new AccessGuard($session);

        assertSame(null, $guard->authorize('catalog', '/catalog-admin'));
        try {
            $guard->authorize('root', '/admin');
        } catch (InvalidArgumentException) {
            return;
        }

        throw new RuntimeException('Expected unknown permission to be rejected.');
    },
    'checkout checks out an available copy and writes status history under a named lock' => static function (): void {
        $connection = checkoutFixture();
        $receipt = (new CheckoutRepository($connection))->checkout('M-9', 'B-7');

        assertSame('2026-10-16', $receipt['due_back_dt']);
        assertSame(0, $receipt['renewal_count']);
        assertSame(true, str_contains($connection->queries[0], 'GET_LOCK'));
        assertSame(true, str_contains($connection->queries[count($connection->queries) - 1], 'RELEASE_LOCK'));
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'UPDATE biblio_copy'),
        )) === 1);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO biblio_status_hist'),
        )) === 1);
    },
    'checkout rejects non-existent members and still releases the lock' => static function (): void {
        $connection = checkoutFixture(member: null);
        try {
            (new CheckoutRepository($connection))->checkout('M-404', 'B-7');
        } catch (CheckoutRejected) {
            assertSame(true, str_contains($connection->queries[count($connection->queries) - 1], 'RELEASE_LOCK'));

            return;
        }

        throw new RuntimeException('Expected a non-existent member to be rejected.');
    },
    'checkout blocks members whose overdue balance exceeds the configured policy' => static function (): void {
        $connection = checkoutFixture(
            settings: ['block_checkouts_when_fines_due' => 'Y', 'balance' => '0.01'],
        );
        try {
            (new CheckoutRepository($connection))->checkout('M-9', 'B-7');
        } catch (CheckoutRejected) {
            assertSame(false, count(array_filter(
                $connection->queries,
                static fn (string $query): bool => str_starts_with($query, 'UPDATE biblio_copy'),
            )) > 0);

            return;
        }

        throw new RuntimeException('Expected a positive fine balance to block checkout.');
    },
    'checkout enforces material and member-class checkout limits' => static function (): void {
        $connection = checkoutFixture(currentCheckoutCount: 10);
        try {
            (new CheckoutRepository($connection))->checkout('M-9', 'B-7');
        } catch (CheckoutRejected) {
            assertSame(false, count(array_filter(
                $connection->queries,
                static fn (string $query): bool => str_starts_with($query, 'UPDATE biblio_copy'),
            )) > 0);

            return;
        }

        throw new RuntimeException('Expected the configured checkout limit to block checkout.');
    },
    'checkout rejects copies held for another member' => static function (): void {
        $copy = [
            'bibid' => 42,
            'copyid' => 7,
            'barcode_nmbr' => 'B-7',
            'status_cd' => 'hld',
            'status_begin_dt' => '2020-01-01 00:00:00',
            'due_back_dt' => null,
            'mbrid' => null,
            'renewal_count' => 0,
            'material_cd' => 1,
            'days_due_back' => 14,
            'checkout_limit' => 10,
            'renewal_limit' => 2,
            'overdue' => 0,
        ];
        $connection = checkoutFixture(copy: $copy, hold: ['holdid' => 5, 'mbrid' => 88, 'hold_age' => 0]);
        try {
            (new CheckoutRepository($connection))->checkout('M-9', 'B-7');
        } catch (CheckoutRejected) {
            assertSame(false, count(array_filter(
                $connection->queries,
                static fn (string $query): bool => str_starts_with($query, 'UPDATE biblio_copy'),
            )) > 0);

            return;
        }

        throw new RuntimeException('Expected a hold for another member to block checkout.');
    },
    'checkout rejects overdue renewals and leaves the copy unchanged' => static function (): void {
        $copy = [
            'bibid' => 42,
            'copyid' => 7,
            'barcode_nmbr' => 'B-7',
            'status_cd' => 'out',
            'status_begin_dt' => '2026-09-01 00:00:00',
            'due_back_dt' => '2026-09-30',
            'mbrid' => 9,
            'renewal_count' => 0,
            'material_cd' => 1,
            'days_due_back' => 14,
            'checkout_limit' => 10,
            'renewal_limit' => 2,
            'overdue' => 1,
        ];
        $connection = checkoutFixture(copy: $copy);
        try {
            (new CheckoutRepository($connection))->checkout('M-9', 'B-7');
        } catch (CheckoutRejected) {
            assertSame(false, count(array_filter(
                $connection->queries,
                static fn (string $query): bool => str_starts_with($query, 'UPDATE biblio_copy'),
            )) > 0);

            return;
        }

        throw new RuntimeException('Expected an overdue renewal to be rejected.');
    },
    'checkout rejects renewals that have reached the configured maximum' => static function (): void {
        $copy = [
            'bibid' => 42,
            'copyid' => 7,
            'barcode_nmbr' => 'B-7',
            'status_cd' => 'out',
            'status_begin_dt' => '2026-09-01 00:00:00',
            'due_back_dt' => '2026-10-30',
            'mbrid' => 9,
            'renewal_count' => 2,
            'material_cd' => 1,
            'days_due_back' => 14,
            'checkout_limit' => 10,
            'renewal_limit' => 2,
            'overdue' => 0,
        ];
        $connection = checkoutFixture(copy: $copy);
        try {
            (new CheckoutRepository($connection))->checkout('M-9', 'B-7');
        } catch (CheckoutRejected) {
            assertSame(false, count(array_filter(
                $connection->queries,
                static fn (string $query): bool => str_starts_with($query, 'UPDATE biblio_copy'),
            )) > 0);

            return;
        }

        throw new RuntimeException('Expected the configured renewal limit to be enforced.');
    },
    'checkout reports both operation and lock-release failures without masking the cause' => static function (): void {
        $connection = new ScriptedPdo(static function (string $query): array {
            if (str_contains($query, 'GET_LOCK(')) {
                return ['rows' => [['acquired' => 1]]];
            }
            if (str_contains($query, 'RELEASE_LOCK(')) {
                throw new RuntimeException('release failed');
            }

            return ['rows' => []];
        });
        try {
            (new CheckoutRepository($connection))->checkout('M-404', 'B-7');
        } catch (RuntimeException $error) {
            assertSame(true, str_contains($error->getMessage(), 'liberado'));
            assertSame(true, str_contains($error->getMessage(), 'release failed'));
            assertSame(true, $error->getPrevious() instanceof CheckoutRejected);

            return;
        }

        throw new RuntimeException('Expected the missing member and failed lock release to be reported.');
    },
    'checkout permits another member to claim an expired hold and removes it' => static function (): void {
        $copy = [
            'bibid' => 42,
            'copyid' => 7,
            'barcode_nmbr' => 'B-7',
            'status_cd' => 'hld',
            'status_begin_dt' => '2020-01-01 00:00:00',
            'due_back_dt' => null,
            'mbrid' => null,
            'renewal_count' => 0,
            'material_cd' => 1,
            'days_due_back' => 14,
            'checkout_limit' => 10,
            'renewal_limit' => 2,
            'overdue' => 0,
        ];
        $connection = checkoutFixture(
            copy: $copy,
            settings: ['block_checkouts_when_fines_due' => 'N', 'balance' => 0, 'hold_max_days' => 14],
            hold: ['holdid' => 5, 'mbrid' => 88, 'hold_age' => 15],
        );
        (new CheckoutRepository($connection))->checkout('M-9', 'B-7');

        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'DELETE FROM biblio_hold'),
        )) === 1);
    },
    'checkout form requires circulation permission and emits a CSRF token' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => ['permissions' => ['circulation' => true]],
        ]);
        $controller = new CheckoutController(
            static function (): CheckoutRepository {
                throw new RuntimeException('The checkout form must not access the database.');
            },
            $session,
        );
        $response = $controller->form();

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'name="_csrf"'));
        assertSame(64, strlen((string) $session->get('auth.csrf')));
    },
    'checkout rejects invalid CSRF before connecting to the database' => static function (): void {
        $controller = new CheckoutController(
            static function (): CheckoutRepository {
                throw new RuntimeException('Invalid CSRF tokens must not reach the repository.');
            },
            new ArraySessionStore([
                'auth.user' => ['permissions' => ['circulation' => true]],
                'auth.csrf' => str_repeat('a', 64),
            ]),
        );
        $response = $controller->checkout(new Request('POST', '/circulation/checkout', [], [
            '_csrf' => str_repeat('b', 64),
            'member_barcode' => 'M-9',
            'copy_barcode' => 'B-7',
        ]));

        assertSame(400, $response->statusCode);
    },
    'checkout controller returns an escaped successful receipt' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => ['permissions' => ['circulation' => true]],
            'auth.csrf' => str_repeat('a', 64),
        ]);
        $connection = checkoutFixture();
        $controller = new CheckoutController(
            static fn (): CheckoutRepository => new CheckoutRepository($connection),
            $session,
        );
        $response = $controller->checkout(new Request('POST', '/circulation/checkout', [], [
            '_csrf' => str_repeat('a', 64),
            'member_barcode' => 'M-9',
            'copy_barcode' => 'B-7',
        ]));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'Ana Silva'));
        assertSame(true, str_contains($response->body, '2026-10-16'));
    },
    'checkin shelves returned copies, records history, and charges calculated late fees' => static function (): void {
        $connection = checkinFixture();
        $receipt = (new CheckinRepository($connection))->shelve('B-7', 3);

        assertSame('crt', $receipt['status_cd']);
        assertSame(2, $receipt['late_days']);
        assertSame(1.5, $receipt['fee']);
        assertSame('Ana', $receipt['member']['first_name']);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'UPDATE biblio_copy'),
        )) === 1);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO biblio_status_hist'),
        )) === 1);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO member_account'),
        )) === 1);
    },
    'checkin returns held copies to the hold shelf and does not charge without overdue fee' => static function (): void {
        $copy = [
            'bibid' => 42,
            'copyid' => 7,
            'barcode_nmbr' => 'B-7',
            'status_cd' => 'out',
            'status_begin_dt' => '2026-09-01 00:00:00',
            'due_back_dt' => '2026-10-02',
            'mbrid' => 9,
            'renewal_count' => 0,
            'late_days' => 0,
            'daily_late_fee' => '1.00',
            'first_name' => 'Ana',
            'last_name' => 'Silva',
        ];
        $connection = checkinFixture(copy: $copy, holds: [['holdid' => 10]]);
        $receipt = (new CheckinRepository($connection))->shelve('B-7', 3);

        assertSame('hld', $receipt['status_cd']);
        assertSame(0, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO member_account'),
        )));
    },
    'checkin rejects copies that are not currently checked out and releases the lock' => static function (): void {
        $copy = [
            'bibid' => 42,
            'copyid' => 7,
            'barcode_nmbr' => 'B-7',
            'status_cd' => 'in',
            'status_begin_dt' => '2026-09-01 00:00:00',
            'due_back_dt' => null,
            'mbrid' => null,
            'renewal_count' => 0,
            'late_days' => 0,
            'daily_late_fee' => '0.00',
        ];
        $connection = checkinFixture(copy: $copy);
        try {
            (new CheckinRepository($connection))->shelve('B-7', 3);
        } catch (CheckinRejected) {
            assertSame(true, str_contains($connection->queries[count($connection->queries) - 1], 'RELEASE_LOCK'));

            return;
        }

        throw new RuntimeException('Expected a non-checked-out copy to be rejected.');
    },
    'checkin completion returns selected shelving-cart copies to the shelf' => static function (): void {
        $connection = checkinFixture(cart: [['bibid' => 42, 'copyid' => 7]]);
        $changed = (new CheckinRepository($connection))->complete(
            [['bibid' => 42, 'copyid' => 7]],
            false,
        );

        assertSame(1, $changed);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'UPDATE biblio_copy'),
        )) === 1);
        assertSame(0, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO biblio_status_hist'),
        )));
    },
    'checkin form is access-controlled, csrf-protected, and renders queued copies' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => ['permissions' => ['circulation' => true]],
            'auth.csrf' => str_repeat('a', 64),
        ]);
        $connection = checkinFixture(cart: [[
            'bibid' => 42,
            'copyid' => 7,
            'barcode_nmbr' => 'B-7',
            'title' => 'Book',
            'author' => 'Author',
            'status_begin_dt' => '2026-10-02 12:00:00',
        ]]);
        $controller = new CheckinController(
            static fn (): CheckinRepository => new CheckinRepository($connection),
            $session,
        );
        $response = $controller->view();

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'B-7'));
        assertSame(true, str_contains($response->body, 'name="_csrf"'));
    },
    'checkin rejects invalid csrf before database access' => static function (): void {
        $controller = new CheckinController(
            static function (): CheckinRepository {
                throw new RuntimeException('Invalid CSRF tokens must not reach the repository.');
            },
            new ArraySessionStore([
                'auth.user' => ['permissions' => ['circulation' => true]],
                'auth.csrf' => str_repeat('a', 64),
            ]),
        );
        $response = $controller->shelve(new Request('POST', '/circulation/checkin', [], [
            '_csrf' => str_repeat('b', 64),
            'barcode' => 'B-7',
        ]));

        assertSame(400, $response->statusCode);
    },
    'holds can be placed for checked-out copies but not for copies in the library' => static function (): void {
        $connection = holdFixture();
        $result = (new HoldRepository($connection))->place('M-9', 'B-7');

        assertSame(9, $result['member']['mbrid']);
        assertSame(12, $result['holdid']);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO biblio_hold'),
        )) === 1);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_contains($query, 'RELEASE_LOCK'),
        )) === 1);
    },
    'holds cannot be placed on an item already checked out by the same member' => static function (): void {
        $copy = ['bibid' => 42, 'copyid' => 7, 'barcode_nmbr' => 'B-7', 'status_cd' => 'out', 'mbrid' => 9];
        $connection = holdFixture(copy: $copy);
        try {
            (new HoldRepository($connection))->place('M-9', 'B-7');
        } catch (HoldRejected) {
            assertSame(0, count(array_filter(
                $connection->queries,
                static fn (string $query): bool => str_starts_with($query, 'INSERT INTO biblio_hold'),
            )));

            return;
        }

        throw new RuntimeException('Expected a hold for the current borrower to be rejected.');
    },
    'hold removal is scoped to the requested member and hold' => static function (): void {
        $connection = holdFixture();
        (new HoldRepository($connection))->cancel(9, 12);

        $delete = array_values(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'DELETE FROM biblio_hold'),
        ));
        assertSame(1, count($delete));
        assertSame(true, str_contains($delete[0], 'mbrid = :mbrid AND holdid = :holdid'));
    },
    'hold controller displays member reservations and validates CSRF before changes' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => ['permissions' => ['circulation' => true]],
            'auth.csrf' => str_repeat('a', 64),
        ]);
        $connection = holdFixture(
            holds: [[
                'holdid' => 12,
                'bibid' => 42,
                'copyid' => 7,
                'hold_begin_dt' => '2026-10-02 12:00:00',
                'barcode_nmbr' => 'B-7',
                'status_cd' => 'out',
                'due_back_dt' => '2026-10-16',
                'title' => 'Title',
                'author' => 'Author',
            ]],
        );
        $controller = new HoldController(
            static fn (): HoldRepository => new HoldRepository($connection),
            $session,
        );
        $response = $controller->view(new Request('GET', '/circulation/holds', ['member_barcode' => 'M-9']));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'Title'));
        assertSame(true, str_contains($response->body, 'Remover reserva'));

        $csrfController = new HoldController(
            static function (): HoldRepository {
                throw new RuntimeException('Invalid CSRF tokens must not reach the repository.');
            },
            $session,
        );
        $csrfResponse = $csrfController->change(new Request('POST', '/circulation/holds', [], [
            '_csrf' => str_repeat('b', 64),
            'action' => 'place',
            'member_barcode' => 'M-9',
            'copy_barcode' => 'B-7',
        ]));

        assertSame(400, $csrfResponse->statusCode);
    },
    'hold controller creates a reservation using authenticated form data' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => ['permissions' => ['circulation' => true]],
            'auth.csrf' => str_repeat('a', 64),
        ]);
        $connection = holdFixture();
        $controller = new HoldController(
            static fn (): HoldRepository => new HoldRepository($connection),
            $session,
        );
        $response = $controller->change(new Request('POST', '/circulation/holds', [], [
            '_csrf' => str_repeat('a', 64),
            'action' => 'place',
            'member_barcode' => 'M-9',
            'copy_barcode' => 'B-7',
        ]));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'A reserva foi registrada.'));
    },
    'catalog administration lists matched records and escapes metadata' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => ['permissions' => ['catalog' => true]],
        ]);
        $connection = catalogCopyFixture(searchResults: [[
            'bibid' => 42,
            'title' => '<Book>',
            'author' => 'Author',
        ]]);
        $controller = new CatalogCopyController(
            static fn (): CatalogCopyRepository => new CatalogCopyRepository($connection),
            $session,
        );
        $response = $controller->search(new Request('GET', '/catalog-admin/search', ['q' => 'Book']));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, '&lt;Book&gt;'));
        assertSame(true, str_contains($response->body, '/catalog-admin/copies?bibid=42'));
    },
    'catalog copy creation supports automatic barcode generation and reports the new copy' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => ['permissions' => ['catalog' => true]],
            'auth.csrf' => str_repeat('a', 64),
        ]);
        $connection = catalogCopyFixture(
            customDefinitions: [['code' => 'condition', 'description' => 'Condição']],
        );
        $controller = new CatalogCopyController(
            static fn (): CatalogCopyRepository => new CatalogCopyRepository($connection),
            $session,
        );
        $response = $controller->create(new Request('POST', '/catalog-admin/copies', [], [
            '_csrf' => str_repeat('a', 64),
            'bibid' => '42',
            'barcode_nmbr' => '',
            'auto_barcode' => 'Y',
            'copy_desc' => 'Reference copy',
            'custom_condition' => 'Boa',
        ]));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'Exemplar 000421 cadastrado.'));
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO biblio_copy '),
        )) === 1);
        assertSame(true, in_array(
            'INSERT INTO biblio_copy_fields (bibid, copyid, code, data) '
                . 'VALUES (:bibid, :copyid, :code, :data)',
            $connection->queries,
            true,
        ));
    },
    'catalog copy creation rejects a duplicate barcode without insertion' => static function (): void {
        $connection = catalogCopyFixture(duplicates: [['bibid' => 1, 'copyid' => 2]]);
        try {
            (new CatalogCopyRepository($connection))->createCopy(42, 'B-7', '', false);
        } catch (CatalogCopyRejected) {
            assertSame(0, count(array_filter(
                $connection->queries,
                static fn (string $query): bool => str_starts_with($query, 'INSERT INTO biblio_copy '),
            )));

            return;
        }

        throw new RuntimeException('Expected duplicate catalog barcode to be rejected.');
    },
    'catalog copy list links to edit forms and preserves copy status' => static function (): void {
        $connection = catalogCopyFixture(
            copies: [[
                'copyid' => 7,
                'barcode_nmbr' => 'B-7',
                'copy_desc' => 'Reference',
                'status_cd' => 'out',
            ]],
            customDefinitions: [['code' => 'condition', 'description' => 'Condição']],
            customValues: ['condition' => 'Boa'],
        );
        $session = new ArraySessionStore([
            'auth.user' => ['permissions' => ['catalog' => true]],
            'auth.csrf' => str_repeat('a', 64),
        ]);
        $controller = new CatalogCopyController(
            static fn (): CatalogCopyRepository => new CatalogCopyRepository($connection),
            $session,
        );
        $listing = $controller->copies(new Request('GET', '/catalog-admin/copies', ['bibid' => '42']));
        $form = $controller->editForm(new Request(
            'GET',
            '/catalog-admin/copies/edit',
            ['bibid' => '42', 'copyid' => '7'],
        ));

        assertSame(200, $listing->statusCode);
        assertSame(true, str_contains($listing->body, '/catalog-admin/copies/edit?bibid=42&amp;copyid=7'));
        assertSame(true, str_contains($listing->body, 'name="custom_condition"'));
        assertSame(200, $form->statusCode);
        assertSame(true, str_contains($form->body, 'name="custom_condition"'));
        assertSame(true, str_contains($form->body, 'value="B-7"'));
        assertSame(true, str_contains($form->body, 'Status atual: out'));
        assertSame(true, str_contains($form->body, 'Boa'));
        assertSame(true, str_contains($form->body, 'alterações de status são realizadas pelos fluxos de circulação'));
    },
    'catalog copy editing validates csrf and updates metadata and custom fields without status mutation' => static function (): void {
        $connection = catalogCopyFixture(
            copies: [[
                'copyid' => 7,
                'barcode_nmbr' => 'B-7',
                'copy_desc' => 'Reference',
                'status_cd' => 'out',
            ]],
            customDefinitions: [['code' => 'condition', 'description' => 'Condição']],
        );
        $session = new ArraySessionStore([
            'auth.user' => ['permissions' => ['catalog' => true]],
            'auth.csrf' => str_repeat('a', 64),
        ]);
        $controller = new CatalogCopyController(
            static fn (): CatalogCopyRepository => new CatalogCopyRepository($connection),
            $session,
        );
        $response = $controller->updateCopy(new Request('POST', '/catalog-admin/copies/edit', [], [
            '_csrf' => str_repeat('a', 64),
            'bibid' => '42',
            'copyid' => '7',
            'barcode_nmbr' => 'B-8',
            'copy_desc' => 'Updated copy',
            'custom_condition' => 'Regular',
        ]));

        assertSame(303, $response->statusCode);
        assertSame('/catalog-admin/copies?bibid=42', $response->headers['Location']);
        assertSame(true, in_array(
            'UPDATE biblio_copy SET barcode_nmbr = :barcode, copy_desc = :copy_desc '
                . 'WHERE bibid = :bibid AND copyid = :copyid',
            $connection->queries,
            true,
        ));
        assertSame(true, in_array(
            'INSERT INTO biblio_copy_fields (bibid, copyid, code, data) '
                . 'VALUES (:bibid, :copyid, :code, :data)',
            $connection->queries,
            true,
        ));
        assertSame(false, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_contains($query, 'UPDATE biblio_copy SET status_cd'),
        )) > 0);
    },
    'catalog copy deletion blocks active copies and pending holds' => static function (): void {
        $connection = catalogCopyFixture(
            copies: [[
                'copyid' => 7,
                'barcode_nmbr' => 'B-7',
                'copy_desc' => 'Reference',
                'status_cd' => 'hld',
            ]],
            copyHolds: 1,
        );
        $controller = new CatalogCopyController(
            static fn (): CatalogCopyRepository => new CatalogCopyRepository($connection),
            new ArraySessionStore([
                'auth.user' => ['permissions' => ['catalog' => true]],
                'auth.csrf' => str_repeat('a', 64),
            ]),
        );

        $response = $controller->deleteConfirmation(new Request(
            'GET',
            '/catalog-admin/copies/delete',
            ['bibid' => '42', 'copyid' => '7'],
        ));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, '1 reserva(s) pendente(s)'));
        assertSame(false, str_contains($response->body, 'Confirmar remoção'));
    },
    'catalog copy deletion rechecks status under its lock' => static function (): void {
        $connection = catalogCopyFixture(
            copies: [[
                'copyid' => 7,
                'barcode_nmbr' => 'B-7',
                'copy_desc' => 'Reference',
                'status_cd' => 'out',
            ]],
        );
        $controller = new CatalogCopyController(
            static fn (): CatalogCopyRepository => new CatalogCopyRepository($connection),
            new ArraySessionStore([
                'auth.user' => ['permissions' => ['catalog' => true]],
                'auth.csrf' => str_repeat('a', 64),
            ]),
        );
        $response = $controller->deleteCopy(new Request('POST', '/catalog-admin/copies/delete', [], [
            '_csrf' => str_repeat('a', 64),
            'bibid' => '42',
            'copyid' => '7',
        ]));

        assertSame(409, $response->statusCode);
        assertSame(false, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'DELETE FROM'),
        )) > 0);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_contains($query, 'RELEASE_LOCK'),
        )) === 1);
    },
    'catalog copy deletion validates csrf and removes associated history and fields' => static function (): void {
        $connection = catalogCopyFixture(
            copies: [[
                'copyid' => 7,
                'barcode_nmbr' => 'B-7',
                'copy_desc' => 'Reference',
                'status_cd' => 'in',
            ]],
        );
        $controller = new CatalogCopyController(
            static fn (): CatalogCopyRepository => new CatalogCopyRepository($connection),
            new ArraySessionStore([
                'auth.user' => ['permissions' => ['catalog' => true]],
                'auth.csrf' => str_repeat('a', 64),
            ]),
        );
        $response = $controller->deleteCopy(new Request('POST', '/catalog-admin/copies/delete', [], [
            '_csrf' => str_repeat('a', 64),
            'bibid' => '42',
            'copyid' => '7',
        ]));

        assertSame(303, $response->statusCode);
        assertSame('/catalog-admin/copies?bibid=42', $response->headers['Location']);
        assertSame([
            'DELETE FROM biblio_copy_fields WHERE bibid = :bibid AND copyid = :copyid',
            'DELETE FROM biblio_status_hist WHERE bibid = :bibid AND copyid = :copyid',
            'DELETE FROM biblio_copy WHERE bibid = :bibid AND copyid = :copyid',
        ], array_values(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'DELETE FROM'),
        )));
    },
    'catalog copy forms require catalog permission and CSRF' => static function (): void {
        $anonymous = new CatalogCopyController(
            static function (): CatalogCopyRepository {
                throw new RuntimeException('Anonymous requests must not reach catalog data.');
            },
            new ArraySessionStore(),
        );
        $denied = $anonymous->copies(new Request('GET', '/catalog-admin/copies', ['bibid' => '42']));
        assertSame(303, $denied->statusCode);

        $authorized = new CatalogCopyController(
            static function (): CatalogCopyRepository {
                throw new RuntimeException('Invalid CSRF must not reach catalog data.');
            },
            new ArraySessionStore([
                'auth.user' => ['permissions' => ['catalog' => true]],
                'auth.csrf' => str_repeat('a', 64),
            ]),
        );
        $invalidCsrf = $authorized->create(new Request('POST', '/catalog-admin/copies', [], [
            '_csrf' => str_repeat('b', 64),
            'bibid' => '42',
            'barcode_nmbr' => 'B-7',
            'copy_desc' => '',
        ]));

        assertSame(400, $invalidCsrf->statusCode);
    },
    'member creation generates its barcode from the next member id under a circulation lock' => static function (): void {
        $connection = memberCreateFixture(
            nextMemberId: 10,
            customDefinitions: [['code' => 'guardian', 'description' => 'Responsável']],
        );
        $member = [
            'barcode_nmbr' => '',
            'last_name' => 'Silva',
            'first_name' => 'Ana',
            'address' => '',
            'home_phone' => '',
            'work_phone' => '',
            'email' => '',
            'classification' => 1,
        ];
        $created = (new MemberRepository($connection))->create($member, 3, true);

        assertSame('10', $created['barcode_nmbr']);
        assertSame(12, $created['mbrid']);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO member'),
        )) === 1);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_contains($query, 'RELEASE_LOCK'),
        )) === 1);
    },
    'member creation rejects duplicate barcodes without insertion' => static function (): void {
        $connection = memberCreateFixture(duplicate: ['mbrid' => 4]);
        $member = [
            'barcode_nmbr' => 'M-10',
            'last_name' => 'Silva',
            'first_name' => 'Ana',
            'address' => '',
            'home_phone' => '',
            'work_phone' => '',
            'email' => '',
            'classification' => 1,
        ];
        try {
            (new MemberRepository($connection))->create($member, 3, false);
        } catch (MemberRejected) {
            assertSame(0, count(array_filter(
                $connection->queries,
                static fn (string $query): bool => str_starts_with($query, 'INSERT INTO member'),
            )));

            return;
        }

        throw new RuntimeException('Expected a duplicate member barcode to be rejected.');
    },
    'member creation requires circulation and member-management permissions' => static function (): void {
        $denied = new MemberManagementController(
            static function (): MemberRepository {
                throw new RuntimeException('Users without member permission must not access the database.');
            },
            new ArraySessionStore([
                'auth.user' => ['permissions' => ['circulation' => true, 'member_circulation' => false]],
            ]),
        );
        assertSame(403, $denied->form()->statusCode);

        $session = new ArraySessionStore([
            'auth.user' => ['permissions' => ['circulation' => true, 'member_circulation' => true]],
        ]);
        $form = new MemberManagementController(
            static fn (): MemberRepository => new MemberRepository(memberCreateFixture(
                customDefinitions: [['code' => 'guardian', 'description' => 'Responsável']],
            )),
            $session,
        );
        $response = $form->form();
        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'name="_csrf"'));
        assertSame(true, str_contains($response->body, 'Gerar código automaticamente'));
        assertSame(true, str_contains($response->body, 'name="custom_guardian"'));
    },
    'member creation validates csrf and redirects to the created member' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => [
                'userid' => 3,
                'permissions' => ['circulation' => true, 'member_circulation' => true],
            ],
            'auth.csrf' => str_repeat('a', 64),
        ]);
        $connection = memberCreateFixture(
            nextMemberId: 10,
            customDefinitions: [['code' => 'guardian', 'description' => 'Responsável']],
        );
        $controller = new MemberManagementController(
            static fn (): MemberRepository => new MemberRepository($connection),
            $session,
        );
        $response = $controller->create(new Request('POST', '/circulation/members/new', [], [
            '_csrf' => str_repeat('a', 64),
            'auto_barcode' => 'Y',
            'barcode_nmbr' => '',
            'first_name' => 'Ana',
            'last_name' => 'Silva',
            'address' => '',
            'home_phone' => '',
            'work_phone' => '',
            'email' => '',
            'classification' => '1',
            'custom_guardian' => ' Maria Silva ',
        ]));

        assertSame(303, $response->statusCode);
        assertSame('/circulation/member?barcode=10', $response->headers['Location']);
        assertSame(true, in_array(
            'INSERT INTO member_fields (mbrid, code, data) VALUES (:mbrid, :code, :data)',
            $connection->queries,
            true,
        ));
        assertSame(
            [' Maria Silva ', PDO::PARAM_STR],
            $connection->bindings['INSERT INTO member_fields (mbrid, code, data) VALUES (:mbrid, :code, :data)'][':data'],
        );
    },
    'member editing loads the current record and saves changed personal data' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => [
                'userid' => 3,
                'permissions' => ['circulation' => true, 'member_circulation' => true],
            ],
            'auth.csrf' => str_repeat('a', 64),
        ]);
        $connection = memberCreateFixture(
            customDefinitions: [['code' => 'guardian', 'description' => 'Responsável']],
            customValues: ['guardian' => 'Valor anterior'],
        );
        $controller = new MemberManagementController(
            static fn (): MemberRepository => new MemberRepository($connection),
            $session,
        );
        $form = $controller->editForm(new Request('GET', '/circulation/members/edit', ['mbrid' => '9']));
        assertSame(200, $form->statusCode);
        assertSame(true, str_contains($form->body, 'value="M-9"'));
        assertSame(true, str_contains($form->body, 'action="/circulation/members/edit"'));
        assertSame(true, str_contains($form->body, 'Valor anterior'));

        $response = $controller->update(new Request('POST', '/circulation/members/edit', [], [
            '_csrf' => str_repeat('a', 64),
            'mbrid' => '9',
            'barcode_nmbr' => 'M-9',
            'first_name' => 'Ana',
            'last_name' => 'Pereira',
            'address' => 'Rua da Biblioteca',
            'home_phone' => '',
            'work_phone' => '',
            'email' => '',
            'classification' => '1',
            'custom_guardian' => 'Maria Silva',
        ]));

        assertSame(303, $response->statusCode);
        assertSame('/circulation/member?barcode=M-9', $response->headers['Location']);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'UPDATE member SET'),
        )) === 1);
        assertSame(true, in_array(
            'INSERT INTO member_fields (mbrid, code, data) VALUES (:mbrid, :code, :data)',
            $connection->queries,
            true,
        ));
    },
    'member lookup requires circulation permission before any database connection' => static function (): void {
        $controller = new MemberController(
            static function (): MemberRepository {
                throw new RuntimeException('Unauthenticated member lookups must not connect to the database.');
            },
            new ArraySessionStore(),
        );

        $response = $controller->view(new Request('GET', '/circulation/member', ['barcode' => 'M-1']));

        assertSame(303, $response->statusCode);
        assertSame('/login?return=%2Fcirculation%2Fmember', $response->headers['Location']);
    },
    'authorized member search form does not open the database until submitted' => static function (): void {
        $controller = new MemberController(
            static function (): MemberRepository {
                throw new RuntimeException('An empty member form must not connect to the database.');
            },
            new ArraySessionStore(['auth.user' => ['permissions' => ['circulation' => true]]]),
        );

        $response = $controller->view(new Request('GET', '/circulation/member'));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'Consulta de membro'));
    },
    'member repository performs an exact parameterized barcode lookup' => static function (): void {
        $connection = new CapturingPdo([]);
        $repository = new MemberRepository($connection);

        assertSame(null, $repository->findByBarcode("M%' OR 1=1 --"));
        assertSame(
            'SELECT mbrid, barcode_nmbr, last_name, first_name, address, home_phone, '
            . 'work_phone, email, classification FROM member '
            . 'WHERE barcode_nmbr = :barcode ORDER BY mbrid ASC LIMIT 2',
            $connection->preparedSql,
        );
        assertSame([':barcode' => ["M%' OR 1=1 --", PDO::PARAM_STR]], $connection->statement->bindings);
    },
    'member lookup escapes personal data before rendering' => static function (): void {
        $connection = new ScriptedPdo(static function (string $query): array {
            if (str_contains($query, 'SELECT code, description FROM member_fields_dm')) {
                return ['rows' => [['code' => 'guardian', 'description' => '<b>Responsável</b>']]];
            }
            if (str_contains($query, 'SELECT code, data FROM member_fields')) {
                return ['rows' => [['code' => 'guardian', 'data' => '<img src=x>']]];
            }

            return ['rows' => [[
                'mbrid' => 9,
                'barcode_nmbr' => 'M-9',
                'first_name' => '<script>Eva</script>',
                'last_name' => 'Silva & Souza',
                'address' => "Rua <img src=x>\nApto 2",
                'home_phone' => null,
                'work_phone' => '555-0100',
                'email' => 'eva@example.test',
                'classification' => 1,
            ]]];
        });
        $controller = new MemberController(
            static fn (): MemberRepository => new MemberRepository($connection),
            new ArraySessionStore([
                'auth.user' => ['permissions' => ['circulation' => true, 'member_circulation' => true]],
            ]),
        );

        $response = $controller->view(new Request('GET', '/circulation/member', ['barcode' => 'M-9']));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, '&lt;script&gt;Eva&lt;/script&gt;'));
        assertSame(true, str_contains($response->body, 'Silva &amp; Souza'));
        assertSame(true, str_contains($response->body, "Rua &lt;img src=x&gt;<br>\nApto 2"));
        assertSame(true, str_contains($response->body, '/circulation/account?mbrid=9'));
        assertSame(true, str_contains($response->body, '/circulation/member/history?mbrid=9'));
        assertSame(true, str_contains($response->body, '/circulation/members/delete?mbrid=9'));
        assertSame(true, str_contains($response->body, '&lt;b&gt;Responsável&lt;/b&gt;'));
        assertSame(true, str_contains($response->body, '&lt;img src=x&gt;'));
        assertSame(false, str_contains($response->body, '<script>'));
    },
    'member account displays escaped transactions and running balances' => static function (): void {
        $connection = memberAccountFixture(transactions: [
            [
                'transid' => 1,
                'create_dt' => '2026-10-01 09:30:00',
                'transaction_type_cd' => '+c',
                'transaction_type_desc' => 'Cobrança',
                'amount' => '2.50',
                'description' => '<script>taxa</script>',
            ],
            [
                'transid' => 2,
                'create_dt' => '2026-10-02 09:30:00',
                'transaction_type_cd' => '-p',
                'transaction_type_desc' => 'Pagamento',
                'amount' => '-0.50',
                'description' => 'Pagamento parcial',
            ],
        ]);
        $controller = new MemberAccountController(
            static fn (): MemberAccountRepository => new MemberAccountRepository($connection),
            new ArraySessionStore(['auth.user' => ['permissions' => ['circulation' => true]]]),
        );

        $response = $controller->view(new Request('GET', '/circulation/account', ['mbrid' => '9']));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'name="transaction_type_cd"'));
        assertSame(true, str_contains($response->body, '&lt;script&gt;taxa&lt;/script&gt;'));
        assertSame(false, str_contains($response->body, '<script>taxa</script>'));
        assertSame(true, str_contains($response->body, '2,50'));
        assertSame(true, str_contains($response->body, '2,00'));
        assertSame(true, str_contains($response->body, 'name="transid" value="1"'));
    },
    'member account adds transactions with legacy debit signs and normalized amounts' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => [
                'userid' => 3,
                'permissions' => ['circulation' => true],
            ],
            'auth.csrf' => str_repeat('a', 64),
        ]);
        $connection = memberAccountFixture();
        $controller = new MemberAccountController(
            static fn (): MemberAccountRepository => new MemberAccountRepository($connection),
            $session,
        );

        $response = $controller->add(new Request('POST', '/circulation/account/transactions/new', [], [
            '_csrf' => str_repeat('a', 64),
            'mbrid' => '9',
            'transaction_type_cd' => '-p',
            'amount' => '15.5',
            'description' => ' Pagamento ',
        ]));

        $insert = 'INSERT INTO member_account '
            . '(mbrid, create_dt, create_userid, transaction_type_cd, amount, description) '
            . 'VALUES (:mbrid, NOW(), :userid, :type, :amount, :description)';
        assertSame(303, $response->statusCode);
        assertSame('/circulation/account?mbrid=9', $response->headers['Location']);
        assertSame(['-15.50', PDO::PARAM_STR], $connection->bindings[$insert][':amount']);
        assertSame(['Pagamento', PDO::PARAM_STR], $connection->bindings[$insert][':description']);
        assertSame([3, PDO::PARAM_INT], $connection->bindings[$insert][':userid']);
    },
    'member account rejects invalid amounts without inserting' => static function (): void {
        $session = new ArraySessionStore([
            'auth.user' => ['userid' => 3, 'permissions' => ['circulation' => true]],
            'auth.csrf' => str_repeat('a', 64),
        ]);
        $connection = memberAccountFixture();
        $controller = new MemberAccountController(
            static fn (): MemberAccountRepository => new MemberAccountRepository($connection),
            $session,
        );

        $response = $controller->add(new Request('POST', '/circulation/account/transactions/new', [], [
            '_csrf' => str_repeat('a', 64),
            'mbrid' => '9',
            'transaction_type_cd' => '-p',
            'amount' => '1.001',
            'description' => 'Inválido',
        ]));

        assertSame(422, $response->statusCode);
        assertSame(false, in_array(
            'INSERT INTO member_account '
                . '(mbrid, create_dt, create_userid, transaction_type_cd, amount, description) '
                . 'VALUES (:mbrid, NOW(), :userid, :type, :amount, :description)',
            $connection->queries,
            true,
        ));
    },
    'member account deletes a transaction scoped to its member with csrf' => static function (): void {
        $connection = memberAccountFixture();
        $controller = new MemberAccountController(
            static fn (): MemberAccountRepository => new MemberAccountRepository($connection),
            new ArraySessionStore([
                'auth.user' => ['permissions' => ['circulation' => true]],
                'auth.csrf' => str_repeat('a', 64),
            ]),
        );

        $response = $controller->delete(new Request('POST', '/circulation/account/transactions/delete', [], [
            '_csrf' => str_repeat('a', 64),
            'mbrid' => '9',
            'transid' => '12',
        ]));

        assertSame(303, $response->statusCode);
        assertSame('/circulation/account?mbrid=9', $response->headers['Location']);
        assertSame(
            'DELETE FROM member_account WHERE mbrid = :mbrid AND transid = :transid',
            $connection->queries[0],
        );
    },
    'member deletion is blocked by current checkouts or pending holds' => static function (): void {
        $connection = memberCreateFixture(activeCheckouts: 1, pendingHolds: 2);
        $controller = new MemberManagementController(
            static fn (): MemberRepository => new MemberRepository($connection),
            new ArraySessionStore([
                'auth.user' => [
                    'permissions' => ['circulation' => true, 'member_circulation' => true],
                ],
                'auth.csrf' => str_repeat('a', 64),
            ]),
        );

        $response = $controller->deleteForm(
            new Request('GET', '/circulation/members/delete', ['mbrid' => '9']),
        );

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, '1 empréstimo(s) ativo(s)'));
        assertSame(true, str_contains($response->body, '2 reserva(s) pendente(s)'));
        assertSame(false, str_contains($response->body, 'Confirmar remoção'));
    },
    'member deletion rechecks blockers under its lock before deleting records' => static function (): void {
        $connection = memberCreateFixture(activeCheckouts: 1);
        $controller = new MemberManagementController(
            static fn (): MemberRepository => new MemberRepository($connection),
            new ArraySessionStore([
                'auth.user' => [
                    'permissions' => ['circulation' => true, 'member_circulation' => true],
                ],
                'auth.csrf' => str_repeat('a', 64),
            ]),
        );

        $response = $controller->delete(new Request('POST', '/circulation/members/delete', [], [
            '_csrf' => str_repeat('a', 64),
            'mbrid' => '9',
        ]));

        assertSame(409, $response->statusCode);
        assertSame(true, str_contains($response->body, 'Remoção bloqueada'));
        assertSame(false, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'DELETE FROM'),
        )) > 0);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_contains($query, 'RELEASE_LOCK'),
        )) === 1);
    },
    'member deletion requires csrf and removes only member-owned legacy rows' => static function (): void {
        $connection = memberCreateFixture();
        $controller = new MemberManagementController(
            static fn (): MemberRepository => new MemberRepository($connection),
            new ArraySessionStore([
                'auth.user' => [
                    'permissions' => ['circulation' => true, 'member_circulation' => true],
                ],
                'auth.csrf' => str_repeat('a', 64),
            ]),
        );
        $response = $controller->delete(new Request('POST', '/circulation/members/delete', [], [
            '_csrf' => str_repeat('a', 64),
            'mbrid' => '9',
        ]));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'Membro removido'));
        assertSame([
            'DELETE FROM biblio_status_hist WHERE mbrid = :mbrid',
            'DELETE FROM member_account WHERE mbrid = :mbrid',
            'DELETE FROM member_fields WHERE mbrid = :mbrid',
            'DELETE FROM member WHERE mbrid = :mbrid',
        ], array_values(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'DELETE FROM'),
        )));

        $invalidCsrf = $controller->delete(new Request('POST', '/circulation/members/delete', [], [
            '_csrf' => str_repeat('b', 64),
            'mbrid' => '9',
        ]));
        assertSame(400, $invalidCsrf->statusCode);
    },
    'member history renders escaped data and Brazilian dates' => static function (): void {
        $historyConnection = new ScriptedPdo(static function (string $query): array {
            if (str_contains($query, 'SELECT first_name, last_name FROM member')) {
                return ['rows' => [['first_name' => 'Ana', 'last_name' => 'Silva & Souza']]];
            }

            return ['rows' => [[
                'barcode_nmbr' => 'B-7',
                'title' => '<script>Livro</script>',
                'author' => 'Autora',
                'status_description' => 'devolvido',
                'status_begin_dt' => '2026-10-01 11:30:00',
                'due_back_dt' => '2026-10-10',
            ]]];
        });
        $controller = new MemberHistoryController(
            static fn (): MemberHistoryRepository => new MemberHistoryRepository($historyConnection),
            new ArraySessionStore(['auth.user' => ['permissions' => ['circulation' => true]]]),
        );

        $response = $controller->view(new Request(
            'GET',
            '/circulation/member/history',
            ['mbrid' => '9'],
        ));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'Ana Silva &amp; Souza'));
        assertSame(true, str_contains($response->body, '&lt;script&gt;Livro&lt;/script&gt;'));
        assertSame(false, str_contains($response->body, '<script>Livro</script>'));
        assertSame(true, str_contains($response->body, '01/10/2026'));
        assertSame(true, str_contains($response->body, '10/10/2026'));
        $historyQuery = 'SELECT copy.barcode_nmbr, biblio.title, biblio.author, status.description AS status_description, '
            . 'history.status_begin_dt, history.due_back_dt '
            . 'FROM biblio_status_hist AS history '
            . 'INNER JOIN biblio ON biblio.bibid = history.bibid '
            . 'INNER JOIN biblio_copy AS copy ON copy.bibid = history.bibid AND copy.copyid = history.copyid '
            . 'INNER JOIN member ON member.mbrid = history.mbrid '
            . 'INNER JOIN biblio_status_dm AS status ON status.code = history.status_cd '
            . 'WHERE history.mbrid = :mbrid ORDER BY history.status_begin_dt DESC';
        assertSame(
            [':mbrid' => [9, PDO::PARAM_INT]],
            $historyConnection->bindings[$historyQuery],
        );
    },
    'member history reports missing members and malformed identifiers' => static function (): void {
        $session = new ArraySessionStore(['auth.user' => ['permissions' => ['circulation' => true]]]);
        $notFound = new MemberHistoryController(
            static fn (): MemberHistoryRepository => new MemberHistoryRepository(new CapturingPdo([])),
            $session,
        );
        assertSame(404, $notFound->view(new Request(
            'GET',
            '/circulation/member/history',
            ['mbrid' => '9'],
        ))->statusCode);
        assertSame(400, $notFound->view(new Request(
            'GET',
            '/circulation/member/history',
            ['mbrid' => '0'],
        ))->statusCode);
    },
    'router dispatches registered GET route' => static function (): void {
        $router = new Router();
        $router->get('/example', static fn (Request $request): Response => new Response($request->method));

        assertSame('GET', $router->dispatch(new Request('GET', '/example'))->body);
    },
    'router rejects duplicate routes' => static function (): void {
        $router = new Router();
        $handler = static fn (Request $request): Response => new Response('ok');
        $router->get('/duplicate', $handler);

        try {
            $router->get('/duplicate', $handler);
        } catch (LogicException) {
            return;
        }

        throw new RuntimeException('Expected duplicate route registration to fail.');
    },
    'request rejects non-absolute paths' => static function (): void {
        try {
            new Request('GET', 'relative');
        } catch (InvalidArgumentException) {
            return;
        }

        throw new RuntimeException('Expected a relative request path to be rejected.');
    },
    'JSON responses reject invalid UTF-8 rather than hiding encoding errors' => static function (): void {
        try {
            Response::json(['value' => "\xB1"]);
        } catch (JsonException) {
            return;
        }

        throw new RuntimeException('Expected invalid JSON data to raise JsonException.');
    },
    'all HTTP responses include baseline browser security headers' => static function (): void {
        $response = new Response('ok');

        assertSame('nosniff', $response->headers['X-Content-Type-Options']);
        assertSame('DENY', $response->headers['X-Frame-Options']);
        assertSame('no-referrer', $response->headers['Referrer-Policy']);
        assertSame(
            "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'",
            $response->headers['Content-Security-Policy'],
        );
    },
    'database DSN targets existing schema using utf8mb4 connection encoding' => static function (): void {
        $config = new DatabaseConfig('127.0.0.1', 3307, 'OpenBiblio', 'app', 'secret');

        assertSame('mysql:host=127.0.0.1;port=3307;dbname=OpenBiblio;charset=utf8mb4', $config->dsn());
    },
    'database configuration rejects DSN option injection' => static function (): void {
        try {
            new DatabaseConfig('localhost;dbname=other', 3306, 'OpenBiblio', 'app', 'secret');
        } catch (InvalidArgumentException) {
            return;
        }

        throw new RuntimeException('Expected DSN option injection to be rejected.');
    },
    'database config supports a protected JSON file outside the application tree' => static function (): void {
        $path = tempnam(sys_get_temp_dir(), 'openbiblio-db-');
        if ($path === false) {
            throw new RuntimeException('Could not create a temporary database config fixture.');
        }
        $oldConfig = getenv('OPENBIBLIO_DB_CONFIG');
        try {
            file_put_contents($path, json_encode([
                'host' => 'localhost',
                'port' => 3306,
                'database' => 'openbiblio',
                'username' => 'app',
                'password' => 'fixture-secret',
            ], JSON_THROW_ON_ERROR));
            putenv('OPENBIBLIO_DB_CONFIG=' . $path);
            $config = DatabaseConfig::fromEnvironment();

            assertSame('mysql:host=localhost;port=3306;dbname=openbiblio;charset=utf8mb4', $config->dsn());
            assertSame('fixture-secret', $config->password);
        } finally {
            if ($oldConfig === false) {
                putenv('OPENBIBLIO_DB_CONFIG');
            } else {
                putenv('OPENBIBLIO_DB_CONFIG=' . $oldConfig);
            }
            unlink($path);
        }
    },
    'database connection failures are raised to the caller' => static function (): void {
        try {
            (new ConnectionFactory())->connect(
                new DatabaseConfig('127.0.0.1', 1, 'unused', 'unused', ''),
            );
        } catch (PDOException) {
            return;
        }

        throw new RuntimeException('Expected a refused database connection to throw PDOException.');
    },
    'barcode search uses exact prepared equality and keeps the submitted code literal' => static function (): void {
        $connection = new CapturingPdo();
        $repository = new BarcodeSearchRepository($connection);
        $barcode = "BK%_42' OR 1=1 --";

        $rows = $repository->findByExactBarcode($barcode);

        assertSame(
            "SELECT b.*, c.copyid, c.barcode_nmbr, c.status_cd, c.due_back_dt, c.mbrid, "
            . "status.description AS status_description "
            . "FROM biblio AS b INNER JOIN biblio_copy AS c ON c.bibid = b.bibid "
            . "LEFT JOIN biblio_status_dm AS status ON status.code = c.status_cd "
            . "WHERE c.barcode_nmbr = :barcode ORDER BY c.barcode_nmbr ASC",
            $connection->preparedSql,
        );
        assertSame([':barcode' => [$barcode, PDO::PARAM_STR]], $connection->statement->bindings);
        assertSame(true, $connection->statement->executed);
        assertSame([['barcode_nmbr' => 'BK-42']], $rows);
    },
    'public barcode search excludes nonpublic bibliographies' => static function (): void {
        $connection = new CapturingPdo();
        $repository = new BarcodeSearchRepository($connection);

        $repository->findByExactBarcode('BK-42', true);

        assertSame(true, str_contains($connection->preparedSql, "AND b.opac_flg = 'Y'"));
        assertSame(false, str_contains($connection->preparedSql, 'due_back_dt'));
        assertSame(false, str_contains($connection->preparedSql, 'mbrid'));
    },
    'public barcode page escapes catalog data and trims search input' => static function (): void {
        $connection = new CapturingPdo([[
            'bibid' => 12,
            'title' => '<img src=x onerror=alert(1)>',
            'author' => 'Autor & autora',
            'copyid' => 21,
            'barcode_nmbr' => 'BK-42',
            'status_cd' => 'in',
            'status_description' => 'devolvido',
        ]]);
        $controller = new PublicCatalogSearchController(
            static fn (): BarcodeSearchRepository => new BarcodeSearchRepository($connection),
        );

        $response = $controller->show(new Request('GET', '/opac', ['barcode' => ' BK-42 ']));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'value="BK-42"'));
        assertSame(true, str_contains($response->body, '&lt;img src=x onerror=alert(1)&gt;'));
        assertSame(true, str_contains($response->body, 'Autor &amp; autora'));
        assertSame(true, str_contains($response->body, 'situação devolvido'));
        assertSame(false, str_contains($response->body, '<img src=x'));
        assertSame(true, str_contains($connection->preparedSql, "AND b.opac_flg = 'Y'"));
    },
    'public barcode page does not connect until a code is submitted' => static function (): void {
        $controller = new PublicCatalogSearchController(
            static function (): BarcodeSearchRepository {
                throw new RuntimeException('The database must not be opened for an empty search form.');
            },
        );

        $response = $controller->show(new Request('GET', '/opac'));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'name="barcode"'));
    },
    'public barcode page rejects array-valued codes' => static function (): void {
        $controller = new PublicCatalogSearchController(
            static function (): BarcodeSearchRepository {
                throw new RuntimeException('Invalid query values must be rejected before opening the database.');
            },
        );

        $response = $controller->show(new Request('GET', '/opac', ['barcode' => ['BK-42']]));

        assertSame(400, $response->statusCode);
    },
    'bibliography creation stores core columns and MARC fields under the catalog lock' => static function (): void {
        $connection = bibliographyFixture();
        $connection->insertId = '42';
        $repository = new BibliographyRepository($connection);
        $bibId = $repository->create([
            'material_cd' => 1,
            'collection_cd' => 2,
            'last_change_userid' => 5,
            'call_nmbr1' => 'QA76',
            'call_nmbr2' => '',
            'call_nmbr3' => '',
            'title' => 'Modern PHP',
            'title_remainder' => '',
            'responsibility_stmt' => '',
            'author' => 'Ana Example',
            'topic1' => '',
            'topic2' => '',
            'topic3' => '',
            'topic4' => '',
            'topic5' => '',
            'opac_flg' => true,
        ], [[
            'fieldid' => null,
            'tag' => 520,
            'ind1_cd' => '',
            'ind2_cd' => '',
            'subfield_cd' => 'a',
            'field_data' => 'A description',
        ]]);

        assertSame(42, $bibId);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO biblio '),
        )) === 1);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO biblio_field '),
        )) === 1);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_contains($query, 'RELEASE_LOCK'),
        )) === 1);
        $insertQuery = array_values(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO biblio '),
        ))[0];
        assertSame('Modern PHP', $connection->bindings[$insertQuery][':title'][0]);
        assertSame('Ana Example', $connection->bindings[$insertQuery][':author'][0]);
    },
    'bibliography update rejects a MARC field owned by another record before changing the record' => static function (): void {
        $connection = bibliographyFixture(existingFields: []);
        $repository = new BibliographyRepository($connection);
        $record = [
            'material_cd' => 1,
            'collection_cd' => 2,
            'last_change_userid' => 5,
            'call_nmbr1' => 'QA76',
            'call_nmbr2' => '',
            'call_nmbr3' => '',
            'title' => 'Changed title',
            'title_remainder' => '',
            'responsibility_stmt' => '',
            'author' => '',
            'topic1' => '',
            'topic2' => '',
            'topic3' => '',
            'topic4' => '',
            'topic5' => '',
            'opac_flg' => true,
        ];
        $rejected = false;
        try {
            $repository->update(42, $record, [[
                'fieldid' => 999,
                'tag' => 520,
                'ind1_cd' => '',
                'ind2_cd' => '',
                'subfield_cd' => 'a',
                'field_data' => 'Tampered field id',
            ]]);
        } catch (BibliographyRejected) {
            $rejected = true;
        }

        assertSame(true, $rejected);
        assertSame(false, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'UPDATE biblio SET'),
        )) > 0);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_contains($query, 'RELEASE_LOCK'),
        )) === 1);
    },
    'bibliography update saves fields only after confirming ownership' => static function (): void {
        $connection = bibliographyFixture();
        $repository = new BibliographyRepository($connection);
        $repository->update(42, [
            'material_cd' => 1,
            'collection_cd' => 2,
            'last_change_userid' => 5,
            'call_nmbr1' => 'QA76',
            'call_nmbr2' => '',
            'call_nmbr3' => '',
            'title' => 'Updated',
            'title_remainder' => '',
            'responsibility_stmt' => '',
            'author' => '',
            'topic1' => '',
            'topic2' => '',
            'topic3' => '',
            'topic4' => '',
            'topic5' => '',
            'opac_flg' => false,
        ], [[
            'fieldid' => 8,
            'tag' => 520,
            'ind1_cd' => '',
            'ind2_cd' => '',
            'subfield_cd' => 'a',
            'field_data' => 'Updated description',
        ]]);

        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'UPDATE biblio SET'),
        )) === 1);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'UPDATE biblio_field SET'),
        )) === 1);
    },
    'bibliography repository enforces material-required subfields before inserting' => static function (): void {
        $connection = bibliographyFixture(
            requiredFields: [['tag' => 520, 'subfieldCd' => 'a', 'descr' => 'Resumo', 'required' => 'Y']],
        );
        $repository = new BibliographyRepository($connection);
        $rejected = false;
        try {
            $repository->create([
                'material_cd' => 1,
                'collection_cd' => 2,
                'last_change_userid' => 5,
                'call_nmbr1' => 'QA76',
                'call_nmbr2' => '',
                'call_nmbr3' => '',
                'title' => 'Needs a summary',
                'title_remainder' => '',
                'responsibility_stmt' => '',
                'author' => '',
                'topic1' => '',
                'topic2' => '',
                'topic3' => '',
                'topic4' => '',
                'topic5' => '',
                'opac_flg' => true,
            ], []);
        } catch (BibliographyRejected) {
            $rejected = true;
        }

        assertSame(true, $rejected);
        assertSame(false, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO biblio '),
        )) > 0);
    },
    'bibliography deletion rechecks copies and holds under the catalog lock' => static function (): void {
        $connection = bibliographyFixture(copyCount: 1);
        $repository = new BibliographyRepository($connection);
        $rejected = false;
        try {
            $repository->delete(42);
        } catch (BibliographyRejected) {
            $rejected = true;
        }

        assertSame(true, $rejected);
        $lockPosition = array_search(
            'SELECT GET_LOCK(:lock_name, :timeout) AS acquired',
            $connection->queries,
            true,
        );
        $copyCountPosition = array_search(
            'SELECT COUNT(*) AS row_count FROM biblio_copy WHERE bibid = :bibid',
            $connection->queries,
            true,
        );
        assertSame(true, is_int($lockPosition) && is_int($copyCountPosition) && $lockPosition < $copyCountPosition);
        assertSame(false, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'DELETE FROM'),
        )) > 0);
    },
    'bibliography deletion removes the record and MARC fields when no copies or holds remain' => static function (): void {
        $connection = bibliographyFixture();
        (new BibliographyRepository($connection))->delete(42);

        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => $query === 'DELETE FROM biblio_field WHERE bibid = :bibid',
        )) === 1);
        assertSame(true, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => $query === 'DELETE FROM biblio WHERE bibid = :bibid',
        )) === 1);
    },
    'bibliography removal requires CSRF and redirects after successful deletion' => static function (): void {
        $connection = bibliographyFixture();
        $controller = new BibliographyController(
            static fn (): BibliographyRepository => new BibliographyRepository($connection),
            new ArraySessionStore([
                'auth.user' => ['userid' => 5, 'permissions' => ['catalog' => true]],
                'auth.csrf' => str_repeat('d', 64),
            ]),
        );
        $rejected = $controller->delete(new Request('POST', '/catalog-admin/bibliographies/delete', [], [
            'bibid' => '42',
        ]));
        $deleted = $controller->delete(new Request('POST', '/catalog-admin/bibliographies/delete', [], [
            '_csrf' => str_repeat('d', 64),
            'bibid' => '42',
        ]));

        assertSame(400, $rejected->statusCode);
        assertSame(303, $deleted->statusCode);
        assertSame('/catalog-admin/search', $deleted->headers['Location']);
    },
    'bibliography editor creates records with CSRF and redirects to the new record' => static function (): void {
        $connection = bibliographyFixture();
        $connection->insertId = '55';
        $controller = new BibliographyController(
            static fn (): BibliographyRepository => new BibliographyRepository($connection),
            new ArraySessionStore([
                'auth.user' => ['userid' => 5, 'permissions' => ['catalog' => true]],
                'auth.csrf' => str_repeat('b', 64),
            ]),
        );
        $response = $controller->create(new Request('POST', '/catalog-admin/bibliographies/new', [], [
            '_csrf' => str_repeat('b', 64),
            'material_cd' => '1',
            'collection_cd' => '2',
            'call_nmbr1' => 'QA76',
            'title' => 'New title',
            'marc_fields' => [],
        ]));

        assertSame(303, $response->statusCode);
        assertSame('/catalog-admin/bibliographies/edit?bibid=55', $response->headers['Location']);
    },
    'bibliography update rejects a missing MARC field list rather than clearing the record' => static function (): void {
        $connection = bibliographyFixture();
        $controller = new BibliographyController(
            static fn (): BibliographyRepository => new BibliographyRepository($connection),
            new ArraySessionStore([
                'auth.user' => ['userid' => 5, 'permissions' => ['catalog' => true]],
                'auth.csrf' => str_repeat('e', 64),
            ]),
        );
        $response = $controller->update(new Request('POST', '/catalog-admin/bibliographies/edit', [], [
            '_csrf' => str_repeat('e', 64),
            'bibid' => '42',
            'material_cd' => '1',
            'collection_cd' => '2',
            'call_nmbr1' => 'QA76',
        ]));

        assertSame(400, $response->statusCode);
        assertSame([], $connection->queries);
    },
    'bibliography edit form loads existing MARC fields and escapes their values' => static function (): void {
        $connection = bibliographyFixture(
            bibliography: [
                'bibid' => 42,
                'material_cd' => 1,
                'collection_cd' => 2,
                'title' => 'Title',
                'author' => 'Author',
                'opac_flg' => 'Y',
            ],
            existingFields: [[
                'fieldid' => 8,
                'tag' => 520,
                'ind1_cd' => null,
                'ind2_cd' => null,
                'subfield_cd' => 'a',
                'field_data' => '<unsafe>',
            ]],
        );
        $controller = new BibliographyController(
            static fn (): BibliographyRepository => new BibliographyRepository($connection),
            new ArraySessionStore([
                'auth.user' => ['userid' => 5, 'permissions' => ['catalog' => true]],
                'auth.csrf' => str_repeat('c', 64),
            ]),
        );
        $response = $controller->editForm(new Request(
            'GET',
            '/catalog-admin/bibliographies/edit',
            ['bibid' => '42'],
        ));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, 'name="marc_fields[0][fieldid]" value="8"'));
        assertSame(true, str_contains($response->body, '&lt;unsafe&gt;'));
    },
    'bibliography editor validates required material fields and escapes submitted MARC values' => static function (): void {
        $connection = bibliographyFixture(
            requiredFields: [['tag' => 520, 'subfieldCd' => 'a', 'descr' => 'Resumo <texto>', 'required' => 'Y']],
        );
        $session = new ArraySessionStore([
            'auth.user' => ['userid' => 5, 'permissions' => ['catalog' => true]],
            'auth.csrf' => str_repeat('a', 64),
        ]);
        $controller = new BibliographyController(
            static fn (): BibliographyRepository => new BibliographyRepository($connection),
            $session,
        );
        $response = $controller->create(new Request('POST', '/catalog-admin/bibliographies/new', [], [
            '_csrf' => str_repeat('a', 64),
            'material_cd' => '1',
            'collection_cd' => '2',
            'call_nmbr1' => 'QA76',
            'title' => '<script>alert(1)</script>',
            'marc_fields' => [],
        ]));

        assertSame(422, $response->statusCode);
        assertSame(true, str_contains($response->body, 'Resumo &lt;texto&gt;'));
        assertSame(false, str_contains($response->body, '<script>alert(1)</script>'));
        assertSame(false, count(array_filter(
            $connection->queries,
            static fn (string $query): bool => str_starts_with($query, 'INSERT INTO biblio '),
        )) > 0);
    },
    'barcode search rejects an empty code' => static function (): void {
        try {
            (new BarcodeSearchRepository(new CapturingPdo()))->findByExactBarcode('');
        } catch (InvalidArgumentException) {
            return;
        }

        throw new RuntimeException('Expected an empty barcode to be rejected.');
    },
    'title search parses quoted terms and removes duplicate words' => static function (): void {
        assertSame(
            ['fundo especial', 'biblioteca'],
            SearchTerms::fromInput(' "Fundo Especial" biblioteca BIBLIOTECA '),
        );
    },
    'public title search requires every term and binds unique placeholders' => static function (): void {
        $connection = new CapturingPdo();
        $repository = new BarcodeSearchRepository($connection);

        $repository->searchPublicByTitle('"Fundo Especial" OBRA');

        assertSame(
            "SELECT b.bibid, b.title, b.title_remainder, b.author, "
            . "c.copyid, c.barcode_nmbr, c.status_cd, status.description AS status_description "
            . "FROM biblio AS b LEFT JOIN biblio_copy AS c ON c.bibid = b.bibid "
            . "LEFT JOIN biblio_status_dm AS status ON status.code = c.status_cd "
            . "WHERE b.opac_flg = 'Y' AND (b.title LIKE :title0 OR b.title_remainder LIKE :remainder0) "
            . "AND (b.title LIKE :title1 OR b.title_remainder LIKE :remainder1) ORDER BY b.title ASC",
            $connection->preparedSql,
        );
        assertSame(
            [
                ':title0' => ['%fundo especial%', PDO::PARAM_STR],
                ':remainder0' => ['%fundo especial%', PDO::PARAM_STR],
                ':title1' => ['%obra%', PDO::PARAM_STR],
                ':remainder1' => ['%obra%', PDO::PARAM_STR],
            ],
            $connection->statement->bindings,
        );
    },
    'public title page escapes returned data' => static function (): void {
        $connection = new CapturingPdo([[
            'bibid' => 42,
            'title' => '<script>alert(1)</script>',
            'author' => 'Autora',
            'copyid' => 5,
            'barcode_nmbr' => 'B-1',
            'status_description' => 'devolvido',
        ], [
            'bibid' => 42,
            'title' => '<script>alert(1)</script>',
            'author' => 'Autora',
            'copyid' => 6,
            'barcode_nmbr' => 'B-2',
            'status_description' => 'emprestado',
        ]]);
        $controller = new PublicCatalogSearchController(
            static fn (): BarcodeSearchRepository => new BarcodeSearchRepository($connection),
        );

        $response = $controller->show(new Request('GET', '/opac', ['title' => 'Livro']));

        assertSame(200, $response->statusCode);
        assertSame(true, str_contains($response->body, '&lt;script&gt;alert(1)&lt;/script&gt;'));
        assertSame(false, str_contains($response->body, '<script>'));
        assertSame(true, str_contains($connection->preparedSql, "WHERE b.opac_flg = 'Y'"));
        assertSame(1, substr_count($response->body, '<strong>'));
        assertSame(true, str_contains($response->body, 'B-1'));
        assertSame(true, str_contains($response->body, 'B-2'));
    },
    'title search rejects an empty search string' => static function (): void {
        try {
            (new BarcodeSearchRepository(new CapturingPdo()))->searchPublicByTitle('  ""  ');
        } catch (InvalidArgumentException) {
            return;
        }

        throw new RuntimeException('Expected an empty title search to be rejected.');
    },
    'request factory extracts path and ignores query string' => static function (): void {
        $_SERVER['REQUEST_METHOD'] = 'get';
        $_SERVER['REQUEST_URI'] = '/health?probe=1';
        $request = Request::fromGlobals();

        assertSame('GET', $request->method);
        assertSame('/health', $request->path);
    },
];

$failures = 0;
foreach ($tests as $name => $test) {
    try {
        $test();
        fwrite(STDOUT, "PASS {$name}\n");
    } catch (Throwable $error) {
        $failures++;
        fwrite(STDERR, "FAIL {$name}: {$error->getMessage()}\n");
    }
}

fwrite(STDOUT, sprintf("%d tests, %d failures\n", count($tests), $failures));

exit($failures === 0 ? 0 : 1);
