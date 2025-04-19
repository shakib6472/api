<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'api' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '^(HC2WPHs5LAR|5U#jrL>nC`rjj]%**TT$/v1Fd61qhfkwR^Frrqz$GABWV3]%wA' );
define( 'SECURE_AUTH_KEY',  '=4` _%V_i]`);h3wo9[.rw[IMW>MEs{I*6ao.W04fInJO,K(A&L#.c&}GXwut|Z`' );
define( 'LOGGED_IN_KEY',    '?L&5<`1ZSM#&Xtk;ez3D^+rVKqE1x]ldR%;r<z&w)A@oE0H>&l`R#OtPQ=|$4?/3' );
define( 'NONCE_KEY',        '0 _K<|>:~3|,^yGH&Fsn5Y.bz%}:u1(:64i]CBh7~w_[CvC%G8Z@Q~X/Tbl*^.~q' );
define( 'AUTH_SALT',        '-m6:hf~MeG-Bm1BwuO54mf>)&6|<am2%0}4E;nRFuO<U(nubss;Cb_!9&:0{tE=n' );
define( 'SECURE_AUTH_SALT', 'Lo=mTvsNpmIc4n4ViK1AE{7-&B>WA`(RDxZ(Gh5zrXF1`ET`#?s9Z,B^N!~gz`cu' );
define( 'LOGGED_IN_SALT',   '|^P:.:7?eHMUr1MiNP>5,p-UP:A|pd,onXa)Tl?Qd:]9%KSJV-8^lyF8RM*VsB(m' );
define( 'NONCE_SALT',       'H:tjHR;]YdYf`h!XVY8V8l&+dEv$>.!A*fplXYCAZ2N/woM B(DRZVto$tms53fw' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
