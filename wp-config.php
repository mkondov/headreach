<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'headreach_wp_https');

/** MySQL database username */
define('DB_USER', 'headreach');

/** MySQL database password */
define('DB_PASSWORD', 'NoPasswd@1');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ' t50>M>bq10(YwI}W4T/v8Ted:16uwxVc>P=H-j *O3f68[<)Zq Lg}^p L>X~p?');
define('SECURE_AUTH_KEY',  '3afv=;R99))ZoY-,S9]qQE+OmSw=K[UK.?yTSt)Pw:+UP&,+Sq,Ku![Kkwxkg<E1');
define('LOGGED_IN_KEY',    'IHAURc+S~rs_CFyba[`s!F{TtMg!b$E}lgQaIB*`_0V;FtD7DyJ6_X[ jIz )58Y');
define('NONCE_KEY',        '^ksOOK*a 8+p-b}%2djI.naW)YKcRVF!q-v1iA%HX=]K+8#*eLnJ56)w-*}4&ey6');
define('AUTH_SALT',        '[v@9X9&ZO Y=S&4vyM.1?dBO3=bR4SBWcL}L^}#hb]xz,hqh?vv{VjDD)ze(3+Rj');
define('SECURE_AUTH_SALT', '#/{qVZ459^]TL}0!P/;Rjx)VoRpb?h*i{nWYw5:U:w><V%F1}eLTx/O< eROmn6O');
define('LOGGED_IN_SALT',   '&XZ[$=ae=fvF2)9_d[Mg{:#YcoQxGN3l=F{3kpLlJ#%,:@!MudmnUnR(+Et#h9j{');
define('NONCE_SALT',       'uYL7qO}.<DG#9iDL:Nxk*W{jER%dKsuw1]B/m8M+=HaL`?-_2n4A< >yocwgoN+D');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
