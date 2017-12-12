<?php
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
define('DB_NAME', 'gotravels.net');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'Rikaki1492');

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
define('AUTH_KEY',         '8h<yCZ-N^^lFh152/~QLINHg,pFdp/##hXU@1wsd+F84gH{|`498(^{5)]]L&+<!');
define('SECURE_AUTH_KEY',  '*|:F.J|93~`$S51+H:4%9gMjdyyczuehgu/@.?x1d1NTR!9o_F3`!y+V?)t& s2(');
define('LOGGED_IN_KEY',    'bRyR,YMk+;XT7g(iBM5`d>t(]OLua%S#VMo$#B`N&zW*3mT<hmyGD.b(v)4YZj@A');
define('NONCE_KEY',        '>`#u{F=5oDII*Pp;sfva5KWP;Q5{7xUZRJV0s$e$t6K,_u}h1rf[)ZEL^Gd;ks=k');
define('AUTH_SALT',        '$%D|)}?kGvkJ:.6<7RRU-t*tn5pN>dr81_ YS.R 5`Hv8rs7yw+;^>kQz#sIN)80');
define('SECURE_AUTH_SALT', 'L+?Oq0!Vjxbk9E(o*e$x$/p<!GJEarmVt9+dM1d?v YD2Tu^=?4`vDN<}}^W$=j,');
define('LOGGED_IN_SALT',   '2P4)]J{zp3F6W>?BzUcdkt}s~UC~lBz~ml#doLL`7[`[A:Kg7C<2d`2IgkF)54$7');
define('NONCE_SALT',       'BB4$)^tH4>ilN#!{AG<%[&Z%7$^<W(vMF;NdR_*M]JPtV|h:D,.UT|7fLa(RK3!&');

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
