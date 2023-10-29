<?php
/**
 * As configurações básicas do WordPress
 *
 * O script de criação wp-config.php usa esse arquivo durante a instalação.
 * Você não precisa usar o site, você pode copiar este arquivo
 * para "wp-config.php" e preencher os valores.
 *
 * Este arquivo contém as seguintes configurações:
 *
 * * Configurações do banco de dados
 * * Chaves secretas
 * * Prefixo do banco de dados
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Configurações do banco de dados - Você pode pegar estas informações com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
define( 'DB_NAME', 'wp_winetech' );

/** Usuário do banco de dados MySQL */
define( 'DB_USER', 'docker' );

/** Senha do banco de dados MySQL */
define( 'DB_PASSWORD', 'aNe7eTkByT' );

/** Nome do host do MySQL */
define( 'DB_HOST', 'database' );

/** Charset do banco de dados a ser usado na criação das tabelas. */
define( 'DB_CHARSET', 'utf8mb4' );

/** O tipo de Collate do banco de dados. Não altere isso se tiver dúvidas. */
define( 'DB_COLLATE', '' );

define('FS_METHOD', 'direct');

/**#@+
 * Chaves únicas de autenticação e salts.
 *
 * Altere cada chave para um frase única!
 * Você pode gerá-las
 * usando o {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org
 * secret-key service}
 * Você pode alterá-las a qualquer momento para invalidar quaisquer
 * cookies existentes. Isto irá forçar todos os
 * usuários a fazerem login novamente.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'mW$?[=W-3qehpS^,{%P3z<T93E}oGzCQf1$)|HP88E.d9;l}SZ?i(MBxK`~O*;0<' );
define( 'SECURE_AUTH_KEY',  'IM2t=R2)hFvrOGhEcb$cxRA@EoZmk[tL8 z5~`@Zk/i5xi>V_fOv__gFK7rU(v9C' );
define( 'LOGGED_IN_KEY',    ':%-X:WY1l-s~vs/B!lN)x+q0kbBGd(6IqOW|gE&N])k&x_3f5P)rD@qv2W8fs0}+' );
define( 'NONCE_KEY',        'u$qZX>pNS -BaT1{0=,wfgbL?6Ar?v{)41mbbtHJ4Q}m/`WCTce+ *o<A5`+I,Z,' );
define( 'AUTH_SALT',        'F])d-;wcZ)}7-wJYefl0Sah2Webvnj%B+VKS]Xtyi6T~K,:bI7%#NM7z9`?9Bo9R' );
define( 'SECURE_AUTH_SALT', '3i& 71|Kz>PsyE:=1-3@E3V /xLGjeX^r%@v$R[L}nivQ`ZqUF}pq}ySKoT*~0Wu' );
define( 'LOGGED_IN_SALT',   ':34!@t]Ya`lv:U)mb(FD:uhT/GQv09RDNcv!P[I {ih4}HpibK-[!iR`/+u~`}-]' );
define( 'NONCE_SALT',       'h&T&=+e wo?CjTRX:_Z`/%Wl.=S/@ :|i;j%Ti`=|F^B}[,$G,j{fF3JK|II%kpc' );

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der
 * um prefixo único para cada um. Somente números, letras e sublinhados!
 */
$table_prefix = 'wp_';

/**
 * Para desenvolvedores: Modo de debug do WordPress.
 *
 * Altere isto para true para ativar a exibição de avisos
 * durante o desenvolvimento. É altamente recomendável que os
 * desenvolvedores de plugins e temas usem o WP_DEBUG
 * em seus ambientes de desenvolvimento.
 *
 * Para informações sobre outras constantes que podem ser utilizadas
 * para depuração, visite o Codex.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', '/var/log/wp-errors.log' );
define( 'WP_DEBUG_DISPLAY', false );

/* Adicione valores personalizados entre esta linha até "Isto é tudo". */



/* Isto é tudo, pode parar de editar! :) */

/** Caminho absoluto para o diretório WordPress. */
// if ( ! defined( 'ABSPATH' ) ) {
// 	define( 'ABSPATH', __DIR__ . '/' );
// }
if ( !defined('ABSPATH') ) {
define('ABSPATH', dirname(__FILE__) . '/');
}

/** Configura as variáveis e arquivos do WordPress. */
require_once ABSPATH . 'wp-settings.php';
