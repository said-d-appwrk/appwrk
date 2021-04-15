<?php
 // Added by WP Rocket
/** Enable W3 Total Cache */
define('WP_AUTO_UPDATE_CORE', 'minor');// This setting is required to make sure that WordPress updates can be properly managed in WordPress Toolkit. Remove this line if this WordPress website is not managed by WordPress Toolkit anymore.
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
define('DB_NAME', 'appwrk');
/** MySQL database username */
define('DB_USER', 'appwrkadmin');
/** MySQL database password */
define('DB_PASSWORD', 'i50@j0Nn');
/** MySQL hostname */
define('DB_HOST', 'localhost:3306');
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
define('AUTH_KEY',         '9vWowy*87ByZ-TvZ|``jQ3;C4@Y1$Sk-<a@6g<IREA4W8USpJ3|b^B*d@<5Fv;+B');
define('SECURE_AUTH_KEY',  ' pS9 f[uC_g.0F`8W;Bvl+nlV6)(aNEGyH^)Mc~70o$6PIiHY-yzRd}h/6~ Xn16');
define('LOGGED_IN_KEY',    '^6xPS2D*miE4zyni>lpxyk3>C#,utKq-=[nF4-hGkq}XQcV+bO)n4]u3S9HPkN<q');
define('NONCE_KEY',        '%Td]}+/m>Iutpfa $O6boWF|*2;t.l~H~`,u~p0fg)^ eOe@2Gn=n!EM9t.Y&{@K');
define('AUTH_SALT',        '+6-pW_mw- AJ_Mo&21 Ct%=MhyBb,yT yla~n[;C I&{.,!<:>hYbPMQ3ay;uFql');
define('SECURE_AUTH_SALT', 'wQ8?Dlhk8+U$_sT+lszGlDs7dS>&Gye~z=3G:&+pPp;sCagd6E<X_mcTPrl]%84l');
define('LOGGED_IN_SALT',   'i{AF88%V)l bwJGKbF>Bt2iA[2gN-Zua7N$kjE[|e=)4W>eix-o{~m;cqU%TJ!so');
define('NONCE_SALT',       '}};Ng@.-P]k:?]y33/b=9P_>>QoQqs,yFGJei1BVu;yuSqwlA$qTn!dNT*8C6+/=');
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






