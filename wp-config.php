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
define( 'DB_NAME', 'bdImobiliaria' );

/** Database username */
define( 'DB_USER', 'erictensol' );

/** Database password */
define( 'DB_PASSWORD', 'er@181620' );

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
define( 'AUTH_KEY',         '&qd)cstCFH#UGg-oc 8$(zI:O]3b:e_2B^(ikY){lc#`G}Vb~uu:xb$_1`-6LEOc' );
define( 'SECURE_AUTH_KEY',  'Nd}cC[;[jcV6fMJV2.|qh>K*5|a,|OZC%qtNn[r@cay3J3if} 53bQ%5GUCPP3S~' );
define( 'LOGGED_IN_KEY',    'B}H DZCsMt}*>P{<S~HsRlZ+Jo(MVbUXzec>6_79A*{cvp$PC+fDFqPNZ9/@cB}W' );
define( 'NONCE_KEY',        'LFnp8!eQV29Kqls vC${>qmrR6crGH&j@Z%C2wPIi*r.nrK+ ={X5CR#N/lPj$^X' );
define( 'AUTH_SALT',        ';<xjdN9g]ngvC5lM:Y<oOl_m8rmqR*MlaI{{W3f*)e_yIKQcyyG;+0E.=`:?(-<7' );
define( 'SECURE_AUTH_SALT', 'NN7zH{Y5l2(S,,ds=C6172%/kX(YPg U|Rli&RRUp9x)[H;Qz;kq7]n)d`MB^1z~' );
define( 'LOGGED_IN_SALT',   'HSB#UsXk5uM8Ky<Bz!R=HKZ6>D^i/eAtz7r(*F/w:NU;sw/!mJM#8&C+Nd1t~(dL' );
define( 'NONCE_SALT',       '>kE$nZ=EUNJqj^4Th/G>ol@s^<JF7|RI,BkHImuPafD)?lv >^{jJZmAH46PZDcc' );

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



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
