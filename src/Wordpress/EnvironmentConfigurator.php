<?php

namespace isaactorresmichel\WPAppConf\Wordpress;

use Dotenv\Dotenv;

/**
 * Environment configurator.
 */
class EnvironmentConfigurator
{
  /**
   * Required environment variables.
   */
  const REQUIRED_ENV = [
    'DB_NAME',
    'DB_USER',
    'DB_PASSWORD',
    'DB_CHARSET',
    'DB_COLLATE',
    'DB_PREFIX',
    'WP_ENV',
    'ROOT_DIR',
    'PUBLIC_DIR'
  ];

  /**
   * Initialize environment configuration.
   * 
   * @param string $configpath
   * @param bool $dotenvpath
   */
  public static function init($configpath, $dotenvpath = null)
  {
    static::loadEnvironmentVariables($dotenvpath);
    static::setWPConstants();
    static::setFilesystemConstants();
    static::setDBConstants();
    static::loadSalts($configpath);
    static::setCacheConstants();
  }

  /**
   * Load ENV data
   * 
   * @param string $dotenvpath 
   */
  protected static function loadEnvironmentVariables($dotenvpath)
  {
    /**
     * Expose global env() function from oscarotero/env
     */
    \Env::init();

    if ($dotenvpath) {
      /**
       * Use Dotenv to set required environment variables and load 
       * .env file in root
       */
      $dotenv = Dotenv::create($dotenvpath);

      $dotenv->required(static::REQUIRED_ENV);

      return;
    }

    foreach (static::REQUIRED_ENV as $vname) {
      if (env($vname) == null) {
        throw new \Exception("Error when seaching for ${$vname} variable.");
      }
    }
  }

  /**
   * Returns a boolean indicating if the local environment is DEV.
   */
  protected static function isDev()
  {
    return env('WP_ENV') === 'development'
      || php_sapi_name() == "cli"
      || $_SERVER['REMOTE_ADDR'] == '127.0.0.1'
      || $_SERVER['REMOTE_ADDR'] == "::1";
  }

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
  protected static function setWPConstants()
  {
    // ini_set('display_errors', 0);
    // ini_set('error_reporting', 'E_ALL');

    define('WP_DEBUG', false);

    /**
     * WP_DEBUG_DISPLAY is another companion to WP_DEBUG that controls whether
     * debug messages are shown inside the HTML of pages or not. The default is
     * ‘true’ which shows errors and warnings as they are generated. 
     * 
     * Setting this to false will hide all errors. 
     * 
     * This should be used in conjunction with WP_DEBUG_LOG so that errors 
     * can be reviewed later.
     * 
     * @see https://wordpress.org/support/article/debugging-in-wordpress/#wp_debug_display
     */
    define('WP_DEBUG_DISPLAY', false);

    // define('ADMIN_COOKIE_PATH', '/');
    // define('COOKIE_DOMAIN', '');
    // define('COOKIEPATH', '');
    // define('SITECOOKIEPATH', '');

    define('WP_DEFAULT_THEME', 'twentyseventeen');

    if (static::isDev()) {
      define('WP_ENV', 'development');
      define('WP_CACHE', false);

      // Define this site as a dev site, then disable jetpack.
      define('JETPACK_DEV_DEBUG', true);

      /**
       * The SAVEQUERIES definition saves the database queries to an array 
       * and that array can be displayed to help analyze those queries. 
       * The constant defined as true causes each query to be saved, 
       * how long that query took to execute, and what function called it.
       * 
       * The array is stored in the global $wpdb->queries. 
       * 
       * NOTE: This will have a performance impact on your site, so make
       * sure to turn this off when you aren’t debugging.
       * 
       * @see https://wordpress.org/support/article/debugging-in-wordpress/#savequeries
       */
      // define('SAVEQUERIES', true);

      if (function_exists('xdebug_disable')) {
        \xdebug_disable();
      }

      /**
       * SCRIPT_DEBUG is a related constant that will force WordPress to use 
       * the “dev” versions of core CSS and JavaScript files rather than the 
       * minified versions that are normally loaded. This is useful when you 
       * are testing modifications to any built-in .js or .css files. 
       * 
       * Default is false.
       * 
       * @see https://wordpress.org/support/article/debugging-in-wordpress/#script_debug
       */
      define('SCRIPT_DEBUG', true);
    } else {
      define('WP_ENV', 'production');

      /**
       * Disable all file modifications including updates and update
       * notifications.
       */
      define('DISALLOW_FILE_MODS', true);
      define('DISALLOW_FILE_EDIT', true);
    }

    define('AUTOMATIC_UPDATER_DISABLED', true);
    define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?: false);
    define('FS_METHOD', 'direct');

    /**
     * Custom Settings
     */

    define('WP_MEMORY_LIMIT', '128M');
    define('WP_MAX_MEMORY_LIMIT', '256M');
  }

  protected static function setCacheConstants()
  {
    if (!defined('WP_CACHE')) {
      // WP-Cache
      define('WP_CACHE', true); //Added by WP-Cache Manager
      define('WPCACHEHOME', WP_CONTENT_DIR . '/plugins/wp-super-cache/'); //Added by WP-Cache Manager
    }

    // Autoptimize
    if (!defined('AUTOPTIMIZE_WP_SITE_URL')) {
      define('AUTOPTIMIZE_WP_SITE_URL', WP_HOME);
    }
  }

  protected static function setFilesystemConstants()
  {
    $definitions = ServerPathDefinitions::instance(env('PUBLIC_DIR'))
      ->setWpContentDir(env('PUBLIC_DIR') . "/content")
      ->setWpApplicationDir(env('PUBLIC_DIR') . "/app");

    define('WP_HOME', $definitions->getBaseUrl());
    define('WP_SITEURL', "{$definitions->getBaseUrl()}{$definitions->getWordpressCodebaseRelativePath()}");
    define('WP_CONTENT_DIR', $definitions->getWpContentDir());
    define('WP_CONTENT_URL', "{$definitions->getBaseUrl()}{$definitions->getWordpressContentRelativePath()}");

    /** Absolute path to the WordPress directory. */
    if (!defined('ABSPATH')) {
      define('ABSPATH', env('PUBLIC_DIR') . '/app/');
    }

    /** Real absolute path to the site directory. */
    if (!defined('REAL_ABSPATH')) {
      define('REAL_ABSPATH', env('ROOT_DIR') . DIRECTORY_SEPARATOR);
    }
  }

  protected static function setDBConstants()
  {
    global $table_prefix;

    // ** MySQL settings - You can get this info from your web host ** //
    /** The name of the database for WordPress */
    define('DB_NAME', env('DB_NAME'));

    /** MySQL database username */
    define('DB_USER', env('DB_USER'));

    /** MySQL database password */
    define('DB_PASSWORD', env('DB_PASSWORD'));

    /** MySQL hostname */
    define('DB_HOST', env('DB_HOST'));

    /** Database Charset to use in creating database tables. */
    define('DB_CHARSET', env('DB_CHARSET'));

    /** The Database Collate type. Don't change this if in doubt. */
    define('DB_COLLATE', env('DB_COLLATE'));

    /**
     * WordPress Database Table prefix.
     *
     * You can have multiple installations in one database if you give each
     * a unique prefix. Only numbers, letters, and underscores please!
     */
    $table_prefix = env('DB_PREFIX') ?: 'wp_';
  }

  /**
   * Load/Generate salts file.
   * 
   * @param string $configpath 
   */
  protected function loadSalts($configpath, $replace = true)
  {
    /**#@+
     * Authentication Unique Keys and Salts.
     *
     * Change these to different unique phrases!
     * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
     * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
     *
     * @since 2.6.0
     */

    $salts_file = $configpath . '/wp-salts.php';

    if (!file_exists($salts_file)) {
      SaltsGenerator::buildFile($salts_file, $replace);
    }

    include_once($salts_file);
  }
}
