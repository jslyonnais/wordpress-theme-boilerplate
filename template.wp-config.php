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

if( stristr( $_SERVER['SERVER_NAME'], "dev" ) ) { // Dev Environment

    define('DB_NAME'	, "dev");
	define('DB_USER'	, "dev");
	define('DB_PASSWORD', "123");
	define('DB_HOST'	, "0.0.0.0");

	define('WP_SITEURL'	, "http://dev.example.com");
	define('WP_HOME'	, "http://dev.example.com");

} else if( stristr( $_SERVER['SERVER_NAME'], "staging" ) ) { // Staging Environment

    define('DB_NAME'	, "staging");
	define('DB_USER'	, "staging");
	define('DB_PASSWORD', "123");
	define('DB_HOST'	, "0.0.0.0");

	define('WP_SITEURL'	, "http://staging.example.com");
	define('WP_HOME'	, "http://staging.example.com");

} else { // Staging Environment

    define('DB_NAME'	, "prod");
	define('DB_USER'	, "prod");
	define('DB_PASSWORD', "123");
	define('DB_HOST'	, "0.0.0.0");

	define('WP_SITEURL'	, "http://www.example.com");
	define('WP_HOME'	, "http://www.example.com");

}

	define('DB_CHARSET', 'utf8');
	define('DB_COLLATE', '');
    define('WP_AUTO_UPDATE_CORE', false); /* Set true for WP auto-update */

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'a1b2c3d4_';

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
// define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');