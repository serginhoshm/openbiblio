#!/usr/bin/env bash

set -Eeuo pipefail
umask 077

readonly WEB_ROOT='/var/www/html/openbiblio'
readonly APACHE_CONF='/etc/apache2/conf-available/openbiblio-local.conf'
readonly APACHE_ENABLED='/etc/apache2/conf-enabled/openbiblio-local.conf'
readonly DB_CONFIG='/etc/openbiblio/database.json'
readonly DB_CONFIG_DIR='/etc/openbiblio'
readonly DB_NAME='openbiblio'
readonly DB_USER='app'
readonly LOCAL_PORT='8081'
readonly BACKUP_DIR='/var/backups/openbiblio'
readonly CONF_MARKER='# Managed by the OpenBiblio rewrite local deployment script'

SOURCE_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd -P)"
CLIENT_CNF=''
DB_PASSWORD=''
RELEASE_DIR=''
CONFIG_TEMP=''
BACKUP_TEMP=''
DEPLOY_STARTED=0
SWITCHED_CURRENT=0
HAD_CURRENT=0
OLD_CURRENT=''
CREATED_WEB_ROOT=0
CREATED_DB_CONFIG=0
CREATED_DB_CONFIG_DIR=0
CREATED_APACHE_CONF=0
CREATED_APACHE_ENABLED=0
CREATED_REWRITE=0

rollback_on_failure() {
    local exit_code=$?
    if (( exit_code == 0 )); then
        return
    fi
    trap - ERR
    if (( DEPLOY_STARTED )); then
        printf 'Falha durante a publicação; tentando restaurar o estado anterior...\n' >&2
        if (( SWITCHED_CURRENT )); then
            if (( HAD_CURRENT )); then
                local restore_link="${WEB_ROOT}/.current-rollback-$$"
                ln -s -- "$OLD_CURRENT" "$restore_link"
                mv -Tf -- "$restore_link" "${WEB_ROOT}/current"
            else
                rm -f -- "${WEB_ROOT}/current"
            fi
        fi
        if (( CREATED_APACHE_ENABLED )); then
            rm -f -- "$APACHE_ENABLED"
        fi
        if (( CREATED_APACHE_CONF )); then
            rm -f -- "$APACHE_CONF"
        fi
        if (( CREATED_DB_CONFIG )); then
            rm -f -- "$DB_CONFIG"
        fi
        if (( CREATED_DB_CONFIG_DIR )); then
            rmdir -- "$DB_CONFIG_DIR" 2>/dev/null || true
        fi
        if [[ -n "$RELEASE_DIR" ]]; then
            rm -rf -- "$RELEASE_DIR"
        fi
        if (( CREATED_WEB_ROOT )); then
            rmdir -- "${WEB_ROOT}/releases" "$WEB_ROOT" 2>/dev/null || true
        fi
        if (( CREATED_REWRITE )); then
            a2dismod rewrite >/dev/null 2>&1 || true
        fi
        if (( CREATED_APACHE_CONF || CREATED_APACHE_ENABLED )); then
            if apache2ctl configtest >/dev/null 2>&1; then
                systemctl reload apache2 >/dev/null 2>&1 || true
            fi
        fi
    fi
    exit "$exit_code"
}

cleanup() {
    if [[ -n "$CLIENT_CNF" ]]; then
        rm -f -- "$CLIENT_CNF"
    fi
    if [[ -n "$CONFIG_TEMP" ]]; then
        rm -f -- "$CONFIG_TEMP"
    fi
    if [[ -n "$BACKUP_TEMP" ]]; then
        rm -f -- "$BACKUP_TEMP"
    fi
    unset DB_PASSWORD
}

trap rollback_on_failure ERR
trap cleanup EXIT

if (( EUID != 0 )); then
    printf 'Execute este script com sudo: sudo bash %s\n' "${BASH_SOURCE[0]}" >&2
    exit 1
fi
if [[ ! -t 0 ]]; then
    printf 'Este script requer um terminal interativo para a confirmação e a senha do banco.\n' >&2
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
if ! php -r 'exit(PHP_VERSION_ID >= 80200 && extension_loaded("pdo_mysql") ? 0 : 1);'; then
    printf 'O PHP da linha de comando precisa ser 8.2+ e ter a extensão pdo_mysql.\n' >&2
    exit 1
fi
for command_name in apache2ctl a2enconf a2enmod a2dismod curl gzip mariadb mariadb-dump rsync systemctl; do
    if ! command -v "$command_name" >/dev/null 2>&1; then
        printf 'Comando necessário não encontrado: %s\n' "$command_name" >&2
        exit 1
    fi
done
if [[ -e "$WEB_ROOT" || -L "$WEB_ROOT" ]]; then
    printf 'O destino já existe; para não sobrescrever dados, não foi alterado: %s\n' "$WEB_ROOT" >&2
    exit 1
fi
if [[ -e "$DB_CONFIG_DIR" || -L "$DB_CONFIG_DIR" || -e "$DB_CONFIG" || -L "$DB_CONFIG" \
    || -e "$APACHE_CONF" || -L "$APACHE_CONF" || -e "$APACHE_ENABLED" || -L "$APACHE_ENABLED" ]]; then
    printf 'Já existe configuração OpenBiblio no sistema; revise-a manualmente antes de continuar.\n' >&2
    exit 1
fi
if [[ -e "$BACKUP_DIR" || -L "$BACKUP_DIR" ]]; then
    printf 'O diretório de backup já existe; revise-o manualmente antes de continuar: %s\n' "$BACKUP_DIR" >&2
    exit 1
fi
if [[ -e /etc/apache2/ports.conf ]] && grep -Eq '^[[:space:]]*Listen[[:space:]]+127\.0\.0\.1:8081([[:space:]]|$)' /etc/apache2/ports.conf; then
    printf 'A porta local 8081 já está configurada no Apache; o deploy foi interrompido.\n' >&2
    exit 1
fi
if ! systemctl is-active --quiet apache2; then
    printf 'O Apache2 precisa estar ativo antes do deploy.\n' >&2
    exit 1
fi
if ! apache2ctl -M 2>/dev/null | grep -q 'env_module'; then
    printf 'O módulo Apache mod_env é necessário para carregar o caminho da configuração protegida.\n' >&2
    exit 1
fi

printf '%s\n' \
    'ATENÇÃO: esta reescrita ainda está incompleta e não foi validada neste banco.' \
    'Os módulos publicados podem alterar registros reais da base openbiblio.' \
    'O script criará um dump local protegido antes da publicação; ele não substitui um teste de restauração.' \
    'Use uma senha do MariaDB rotacionada após ter sido compartilhada no chat.' \
    'O Apache será configurado apenas em 127.0.0.1:8081; a instalação atual não será substituída.'
printf '\nVocê já testou a restauração de um backup da base operacional em um ambiente separado? [s/N] '
IFS= read -r restore_tested
if [[ "$restore_tested" != 's' && "$restore_tested" != 'S' ]]; then
    printf 'Faça e teste um backup restaurável antes do deploy; nada foi alterado.\n' >&2
    exit 1
fi
printf '\nDigite exatamente AUTORIZO O DEPLOY LOCAL COM BANCO OPERACIONAL para continuar: '
IFS= read -r confirmation
if [[ "$confirmation" != 'AUTORIZO O DEPLOY LOCAL COM BANCO OPERACIONAL' ]]; then
    printf 'Confirmação não reconhecida; nada foi alterado.\n' >&2
    exit 1
fi

printf 'Senha MariaDB do usuário %s (entrada oculta): ' "$DB_USER"
IFS= read -r -s DB_PASSWORD
printf '\n'
if [[ -z "$DB_PASSWORD" || "$DB_PASSWORD" == *$'\n'* || "$DB_PASSWORD" == *$'\r'* ]]; then
    printf 'A senha está vazia ou contém quebra de linha; nada foi alterado.\n' >&2
    exit 1
fi

CLIENT_CNF="$(mktemp /run/openbiblio-mariadb.XXXXXX)"
OPENBIBLIO_DEPLOY_DB_PASSWORD="$DB_PASSWORD" php -r '
$password = getenv("OPENBIBLIO_DEPLOY_DB_PASSWORD");
if (!is_string($password) || $password === "" || str_contains($password, "\n") || str_contains($password, "\r")) {
    fwrite(STDERR, "Senha de banco inválida.\n");
    exit(1);
}
$escaped = str_replace(["\\", "\""], ["\\\\", "\\\""], $password);
$contents = "[client]\nuser=app\npassword=\"" . $escaped . "\"\nhost=localhost\n";
if (file_put_contents($argv[1], $contents, LOCK_EX) === false || !chmod($argv[1], 0600)) {
    fwrite(STDERR, "Não foi possível criar a configuração temporária do cliente MariaDB.\n");
    exit(1);
}
' "$CLIENT_CNF"

printf 'Verificando acesso e esquema do banco (somente leitura)...\n'
table_list="$(mariadb --defaults-extra-file="$CLIENT_CNF" --protocol=socket --database="$DB_NAME" \
    --batch --skip-column-names \
    --execute='SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()')"
required_tables=(
    biblio biblio_copy biblio_copy_fields biblio_copy_fields_dm biblio_field
    biblio_hold biblio_status_hist biblio_status_dm checkout_privs collection_dm
    material_type_dm material_usmarc_xref member member_account mbr_classify_dm
    member_fields settings staff transaction_type_dm
)
for table_name in "${required_tables[@]}"; do
    if ! grep -Fxq -- "$table_name" <<<"$table_list"; then
        printf 'Tabela necessária ausente no banco %s: %s\n' "$DB_NAME" "$table_name" >&2
        exit 1
    fi
done

mkdir -p -- "$BACKUP_DIR"
chmod 0700 "$BACKUP_DIR"
backup_path="${BACKUP_DIR}/${DB_NAME}_$(date -u +%Y%m%dT%H%M%SZ)_$$.sql.gz"
BACKUP_TEMP="$(mktemp "${BACKUP_DIR}/${DB_NAME}_backup.partial.XXXXXX")"
printf 'Criando dump MariaDB em %s (tabelas MyISAM podem ser bloqueadas durante a cópia)...\n' "$backup_path"
mariadb-dump --defaults-extra-file="$CLIENT_CNF" --protocol=socket --databases "$DB_NAME" \
    | gzip -n -c >"$BACKUP_TEMP"
gzip -t -- "$BACKUP_TEMP"
if [[ ! -s "$BACKUP_TEMP" ]]; then
    printf 'O dump ficou vazio; publicação cancelada.\n' >&2
    exit 1
fi
mv -- "$BACKUP_TEMP" "$backup_path"
BACKUP_TEMP=''
chmod 0600 "$backup_path"
rm -f -- "$CLIENT_CNF"
CLIENT_CNF=''

DEPLOY_STARTED=1
mkdir -p -- "$DB_CONFIG_DIR"
CREATED_DB_CONFIG_DIR=1
chmod 0750 "$DB_CONFIG_DIR"
chown root:www-data "$DB_CONFIG_DIR"
CONFIG_TEMP="$(mktemp "${DB_CONFIG_DIR}/database.json.XXXXXX")"
OPENBIBLIO_DEPLOY_DB_PASSWORD="$DB_PASSWORD" php -r '
$config = [
    "host" => "localhost",
    "port" => 3306,
    "database" => "openbiblio",
    "username" => "app",
    "password" => getenv("OPENBIBLIO_DEPLOY_DB_PASSWORD"),
];
$json = json_encode($config, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
if (file_put_contents($argv[1], $json . PHP_EOL, LOCK_EX) === false || !chmod($argv[1], 0640)) {
    fwrite(STDERR, "Não foi possível gravar a configuração protegida do banco.\n");
    exit(1);
}
' "$CONFIG_TEMP"
chown root:www-data "$CONFIG_TEMP"
mv -- "$CONFIG_TEMP" "$DB_CONFIG"
CONFIG_TEMP=''
CREATED_DB_CONFIG=1
unset DB_PASSWORD

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

printf 'Executando a suíte antes da publicação...\n'
php "$SOURCE_ROOT/modern/tests/run.php"
apache2ctl -M 2>/dev/null | grep -q 'rewrite_module' || CREATED_REWRITE=1

mkdir -p -- "${WEB_ROOT}/releases"
CREATED_WEB_ROOT=1
chown root:www-data "$WEB_ROOT" "${WEB_ROOT}/releases"
chmod 0750 "$WEB_ROOT" "${WEB_ROOT}/releases"
release_id="$(date -u +%Y%m%dT%H%M%SZ)"
RELEASE_DIR="${WEB_ROOT}/releases/${release_id}"
install -d -o root -g www-data -m 0750 "$RELEASE_DIR"
install -d -o root -g www-data -m 0750 "${RELEASE_DIR}/modern"
rsync -a -- "$SOURCE_ROOT/modern/" "${RELEASE_DIR}/modern/"
rsync -a -- "$SOURCE_ROOT/vendor/" "${RELEASE_DIR}/vendor/"
chown -R root:www-data "$RELEASE_DIR"
find "$RELEASE_DIR" -type d -exec chmod 0750 {} +
find "$RELEASE_DIR" -type f -exec chmod 0640 {} +

cat >"$APACHE_CONF" <<EOF
$CONF_MARKER
Listen 127.0.0.1:${LOCAL_PORT}
<VirtualHost 127.0.0.1:${LOCAL_PORT}>
    ServerName openbiblio.localhost
    DocumentRoot "${WEB_ROOT}/current/modern/public"
    SetEnv OPENBIBLIO_DB_CONFIG "${DB_CONFIG}"
    ErrorLog \${APACHE_LOG_DIR}/openbiblio-local-error.log
    CustomLog \${APACHE_LOG_DIR}/openbiblio-local-access.log combined

    <Directory "${WEB_ROOT}/current/modern/public">
        Options -Indexes +FollowSymLinks
        AllowOverride None
        Require all granted
        DirectoryIndex index.php
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [QSA,L]
    </Directory>
</VirtualHost>
EOF
chmod 0644 "$APACHE_CONF"
CREATED_APACHE_CONF=1
a2enmod rewrite >/dev/null
a2enconf openbiblio-local >/dev/null
CREATED_APACHE_ENABLED=1

if [[ -L "${WEB_ROOT}/current" ]]; then
    HAD_CURRENT=1
    OLD_CURRENT="$(readlink -- "${WEB_ROOT}/current")"
fi
new_link="${WEB_ROOT}/.current-new-$$"
ln -s -- "$RELEASE_DIR" "$new_link"
mv -Tf -- "$new_link" "${WEB_ROOT}/current"
SWITCHED_CURRENT=1

apache2ctl configtest
systemctl reload apache2
curl --fail --silent --show-error --max-time 10 \
    "http://127.0.0.1:${LOCAL_PORT}/health" | grep -Fq '"status":"ok"'
curl --fail --silent --show-error --max-time 10 \
    "http://127.0.0.1:${LOCAL_PORT}/opac?barcode=__deployment_read_only_check__" \
    | grep -Fq 'name="barcode"'

DEPLOY_STARTED=0
printf '\nDeploy local concluído.\n'
printf 'URL: http://127.0.0.1:%s/\n' "$LOCAL_PORT"
printf 'Backup: %s\n' "$backup_path"
printf 'Release: %s\n' "$RELEASE_DIR"
printf 'A base está conectada. Não use dados reais sem validar os fluxos e testar a restauração do backup.\n'
