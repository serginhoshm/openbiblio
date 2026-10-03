#!/usr/bin/env bash

set -Eeuo pipefail
umask 077

readonly WEB_ROOT='/var/www/html/openbiblio'
readonly APACHE_CONF='/etc/apache2/conf-available/openbiblio-local.conf'
readonly DB_CONFIG='/etc/openbiblio/database.json'
readonly LOCAL_PORT='8081'

SOURCE_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd -P)"
RELEASE_DIR=''
OLD_CURRENT=''
SWITCHED_CURRENT=0
DEPLOY_STARTED=0

rollback_on_failure() {
    local exit_code=$?
    if (( exit_code == 0 )); then
        return
    fi
    trap - ERR
    if (( DEPLOY_STARTED )); then
        printf 'Falha na atualização; restaurando a release anterior...\n' >&2
        if (( SWITCHED_CURRENT )); then
            local restore_link="${WEB_ROOT}/.current-rollback-$$"
            ln -s -- "$OLD_CURRENT" "$restore_link"
            mv -Tf -- "$restore_link" "${WEB_ROOT}/current"
            if apache2ctl configtest >/dev/null 2>&1; then
                systemctl reload apache2 >/dev/null 2>&1 || true
            fi
        fi
        if [[ -n "$RELEASE_DIR" && -d "$RELEASE_DIR" ]]; then
            rm -rf -- "$RELEASE_DIR"
        fi
    fi
    exit "$exit_code"
}

trap rollback_on_failure ERR

if (( EUID != 0 )); then
    printf 'Execute este script com sudo: sudo bash %s\n' "${BASH_SOURCE[0]}" >&2
    exit 1
fi
if [[ "$(git -c safe.directory="$SOURCE_ROOT" -C "$SOURCE_ROOT" branch --show-current)" != 'rewrite/php82' ]]; then
    printf 'A origem deve ser o worktree da branch rewrite/php82: %s\n' "$SOURCE_ROOT" >&2
    exit 1
fi
if [[ ! -f "$SOURCE_ROOT/vendor/autoload.php" || ! -f "$SOURCE_ROOT/modern/public/index.php" ]]; then
    printf 'A origem não contém o autoloader e a aplicação moderna esperados.\n' >&2
    exit 1
fi
if find "$SOURCE_ROOT/modern" "$SOURCE_ROOT/vendor" -type l -print -quit | grep -q .; then
    printf 'A origem contém links simbólicos; revise-a antes do deploy.\n' >&2
    exit 1
fi
for command_name in apache2ctl curl php rsync systemctl; do
    if ! command -v "$command_name" >/dev/null 2>&1; then
        printf 'Comando necessário não encontrado: %s\n' "$command_name" >&2
        exit 1
    fi
done
if ! php -r 'exit(PHP_VERSION_ID >= 80200 && extension_loaded("pdo_mysql") ? 0 : 1);'; then
    printf 'O PHP da linha de comando precisa ser 8.2+ e ter a extensão pdo_mysql.\n' >&2
    exit 1
fi
if [[ ! -d "$WEB_ROOT/releases" || ! -L "$WEB_ROOT/current" \
    || ! -f "$APACHE_CONF" || ! -f "$DB_CONFIG" ]]; then
    printf 'A instalação local existente não tem a estrutura esperada; nada foi alterado.\n' >&2
    exit 1
fi
if ! grep -Fqx '# Managed by the OpenBiblio rewrite local deployment script' "$APACHE_CONF"; then
    printf 'A configuração Apache não foi criada pelo deploy local; nada foi alterado.\n' >&2
    exit 1
fi
OLD_CURRENT="$(readlink -- "$WEB_ROOT/current")"
if [[ "$OLD_CURRENT" != "$WEB_ROOT/releases/"* || ! -d "$OLD_CURRENT" ]]; then
    printf 'O link current não aponta para uma release local válida; nada foi alterado.\n' >&2
    exit 1
fi
if ! systemctl is-active --quiet apache2; then
    printf 'O Apache2 precisa estar ativo antes da atualização.\n' >&2
    exit 1
fi

printf '%s\n' \
    'Esta operação publicará uma nova release da reescrita e manterá a release anterior.' \
    'A configuração e o conteúdo do banco de dados não serão alterados.' \
    'A suíte de testes e verificações HTTP serão executadas; falhas restauram o link anterior.'
printf '\nDigite exatamente ATUALIZAR OPENBIBLIO LOCAL para continuar: '
IFS= read -r confirmation
if [[ "$confirmation" != 'ATUALIZAR OPENBIBLIO LOCAL' ]]; then
    printf 'Confirmação não reconhecida; nada foi alterado.\n' >&2
    exit 1
fi

printf 'Executando a suíte antes da publicação...\n'
php "$SOURCE_ROOT/modern/tests/run.php"
OPENBIBLIO_DB_CONFIG="$DB_CONFIG" php -r '
require $argv[1] . "/vendor/autoload.php";
$config = OpenBiblio\Modern\Database\DatabaseConfig::fromEnvironment();
$pdo = (new OpenBiblio\Modern\Database\ConnectionFactory())->connect($config);
$row = $pdo->query("SELECT DATABASE() AS database_name")->fetch(PDO::FETCH_ASSOC);
if (($row["database_name"] ?? null) !== "openbiblio") {
    fwrite(STDERR, "A conexão PDO não abriu o banco esperado.\n");
    exit(1);
}
' "$SOURCE_ROOT"

DEPLOY_STARTED=1
release_id="$(date -u +%Y%m%dT%H%M%SZ)-$$"
RELEASE_DIR="${WEB_ROOT}/releases/${release_id}"
install -d -o root -g www-data -m 0750 "$RELEASE_DIR/modern"
rsync -a -- "$SOURCE_ROOT/modern/" "$RELEASE_DIR/modern/"
rsync -a -- "$SOURCE_ROOT/vendor/" "$RELEASE_DIR/vendor/"
chown -R root:www-data "$RELEASE_DIR"
find "$RELEASE_DIR" -type d -exec chmod 0750 {} +
find "$RELEASE_DIR" -type f -exec chmod 0640 {} +

new_link="${WEB_ROOT}/.current-new-$$"
ln -s -- "$RELEASE_DIR" "$new_link"
mv -Tf -- "$new_link" "${WEB_ROOT}/current"
SWITCHED_CURRENT=1

apache2ctl configtest
systemctl reload apache2
curl --fail --silent --show-error --max-time 10 \
    "http://127.0.0.1:${LOCAL_PORT}/health" | grep -Fq '"status":"ok"'
opac_response="$(curl --fail --silent --show-error --max-time 10 \
    "http://127.0.0.1:${LOCAL_PORT}/opac?barcode=__deployment_read_only_check__")"
grep -Fq 'name="barcode"' <<<"$opac_response"
grep -Fq 'Navegação principal' <<<"$opac_response"

DEPLOY_STARTED=0
printf '\nAtualização concluída.\n'
printf 'URL: http://127.0.0.1:%s/\n' "$LOCAL_PORT"
printf 'Release atual: %s\n' "$RELEASE_DIR"
printf 'Release anterior preservada em: %s\n' "$OLD_CURRENT"
