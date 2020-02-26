<?php

namespace isaactorresmichel\WPAppConf\Wordpress;

use Symfony\Component\Filesystem\Filesystem;

class ServerPathDefinitions
{

  static $instance;

  protected $wp_application_dir;
  protected $wp_content_dir;

  protected $server_document_root;

  protected $install_dir;


  private function __construct($install_dir)
  {
    $document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $this->server_document_root = rtrim($document_root, '/');

    $this->install_dir = str_replace('\\', '/', $install_dir);
  }

  /**
   * Instancia definiciones de servidor.
   * @return ServerPathDefinitions
   */
  public static function instance($install_dir)
  {
    if (static::$instance) {
      return static::$instance;
    }

    static::$instance = new static($install_dir);

    return static::$instance;
  }

  protected function getSchema()
  {
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'
      ? "https://" : "http://";
  }

  protected function getHostServer()
  {
    return rtrim($_SERVER['SERVER_NAME'], '/');
  }

  protected function getPort()
  {
    return (isset($_SERVER['SERVER_PORT']) and in_array(
      $_SERVER['SERVER_PORT'],
      array(
        80,
        443
      )
    ))
      ? '' : ":{$_SERVER['SERVER_PORT']}";
  }

  /**
   * Return full URI for WordPress install.
   * @return string
   */
  public function getBaseUrl()
  {
    return $this->getSchema() . $this->getHostServer() . $this->getPort() . $this->getInstallationRelativePath();
  }

  protected function getRelativePath($start, $end)
  {
    $fs = new Filesystem();
    $path = $fs->makePathRelative($end, $start);
    $path = rtrim($path, '/');

    return $path != '.' ? "/{$path}" : '';
  }

  protected function getInstallationRelativePath()
  {
    return $this->getRelativePath(
      $this->server_document_root,
      $this->install_dir
    );
  }

  public function getWordpressCodebaseRelativePath()
  {
    return $this->getRelativePath(
      $this->install_dir,
      $this->wp_application_dir
    );
  }

  public function getWordpressContentRelativePath()
  {
    return $this->getRelativePath(
      $this->install_dir,
      $this->wp_content_dir
    );
  }


  /**
   * @param mixed $wp_content_dir
   *
   * @return ServerPathDefinitions
   */
  public function setWpContentDir($wp_content_dir)
  {
    $this->wp_content_dir = $wp_content_dir;

    return $this;
  }

  /**
   * @param mixed $wp_application_dir
   *
   * @return ServerPathDefinitions
   */
  public function setWpApplicationDir($wp_application_dir)
  {
    $this->wp_application_dir = $wp_application_dir;

    return $this;
  }

  /**
   * @return mixed
   */
  public function getWpContentDir()
  {
    return realpath($this->wp_content_dir);
  }

  /**
   * @return mixed
   */
  public function getWpApplicationDir()
  {
    return realpath($this->wp_application_dir);
  }
}
