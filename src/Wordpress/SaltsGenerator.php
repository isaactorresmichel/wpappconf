<?php

namespace isaactorresmichel\WPAppConf\Wordpress;

/**
 * Wordpress salt generator.
 * 
 * @see https://api.wordpress.org/secret-key/1.1/salt/
 */
class SaltsGenerator
{
  /**
   * Salts file template.
   */
  private const TEMPLATE = "<?php\n%salts";

  /**
   * Get salts data from WP site API.
   */
  public static function getFromAPI()
  {
    $response = file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/');

    if (!$response) {
      throw new \Exception("Couldn't generate salts keys from API.");
    }

    return $response;
  }

  /**
   * Builds a salts file.
   */
  public static function buildFile($path, $replace = false) {
    $salts = static::getFromAPI();

    if ($replace && file_exists($path)) {
      unlink($path);
    }

    $content = strtr(static::TEMPLATE, ['%salts' => $salts]);

    return file_put_contents($path, $content);
  }
}
