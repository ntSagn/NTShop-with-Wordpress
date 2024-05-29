<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', "wordpress" );

/** Database username */
define( 'DB_USER', "root" );

/** Database password */
define( 'DB_PASSWORD', "11111111" );

/** Database hostname */
define( 'DB_HOST', "localhost" );

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
define( 'AUTH_KEY',         ';8RbtqK#A,6:;&eGqE_EC~2/cq$)YhxeFlBq) `jHdyGt<,c)MEicS)^@<3W%}i^' );
define( 'SECURE_AUTH_KEY',  'n^]BFT*YVg_0_)XQp:gV(u/k:rsTt7eKPYfY4ZA WWY&[=Hzbb.2EW%aH2B}^|nk' );
define( 'LOGGED_IN_KEY',    '_(q5CvPR9gWz2vW .47%BB(;)CN`w8+vo 6k>zViWPgIu@IH<y5}E#UqVns-#qag' );
define( 'NONCE_KEY',        '%:{R6[q,bn9@9i8GOHC0B,Up/.?/s5Y})B88F$~z5z*[yQyyJD2VekT{@W$=[iM!' );
define( 'AUTH_SALT',        'fL{ClE V:d~ >qh!eWgCxAMxhPV3G@Y(brt$J3xE{}&GVGM4s]=ZY`5WCQ=_b/XC' );
define( 'SECURE_AUTH_SALT', 'f{]oGZ~&ao4IRGQq0lLthD{v@2+x-%D#lC:[Ly7,<?21?$:czhCIupt`1^jK|xlF' );
define( 'LOGGED_IN_SALT',   '|0]-sl:>>tFk634X)lMX,Rjy>G!IXh7^sBl!M;}A`&tE9xVG8G|<KPdfP_qu,L1[' );
define( 'NONCE_SALT',       ';(XFPyc~4V7%;(e)Zol+^cC$]]X21q)WXX gx/w%C&:&bOSZ*?^GZ*F/^<XGDbKJ' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

define( 'WP_CACHE', true );


define( 'FS_METHOD', 'direct' );
/**
 * The WP_SITEURL and WP_HOME options are configured to access from any hostname or IP address.
 * If you want to access only from an specific domain, you can modify them. For example:
 *  define('WP_HOME','http://example.com');
 *  define('WP_SITEURL','http://example.com');
 *
 */
if ( defined( 'WP_CLI' ) ) {
	$_SERVER['HTTP_HOST'] = '127.0.0.1';
}

define( 'WP_HOME', 'http://localhost/wordpress/backup' );
define( 'WP_SITEURL', 'http://localhost/wordpress/backup' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname(__FILE__) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

/**
 * Disable pingback.ping xmlrpc method to prevent WordPress from participating in DDoS attacks
 * More info at: https://docs.bitnami.com/general/apps/wordpress/troubleshooting/xmlrpc-and-pingback/
 */
if ( !defined( 'WP_CLI' ) ) {
	// remove x-pingback HTTP header
	add_filter("wp_headers", function($headers) {
		unset($headers["X-Pingback"]);
		return $headers;
	});
	// disable pingbacks
	add_filter( "xmlrpc_methods", function( $methods ) {
		unset( $methods["pingback.ping"] );
		return $methods;
	});
}
