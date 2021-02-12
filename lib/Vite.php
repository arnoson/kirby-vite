<?php declare(strict_types=1);

namespace Arnoson\KirbyVite;

use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use \Exception;

class Vite {
  protected array $manifest;
  protected string $outDir;
  protected string $assetsDir;
  protected string $rootDir;
  protected string $devServer;

  /** 
   * Make sure, a directory starts with a slash and doesn't end with a slash.
   * 
   * @param $dir The directory.
   * @example
   * sanitizeDir('test') // => '/test'
   */
  protected function sanitizeDir(string $dir): string {
    if (!Str::startsWith($dir, '/')) {
      $dir = "/{$dir}";
    }

    if (Str::endsWith($dir, '/')) {
      $dir = substr($dir, 0, -1);
    }

    return $dir;
  }

  /**
   * Get the output directory.
   */
  protected function outDir(): string {
    return $this->outDir ??
      ($this->outDir = $this->sanitizeDir(
        option('arnoson.kirby-vite.outDir', '/assets/dist')
      ));
  }

  /**
   * Get the assets directory (relative to the output directory).
   */
  protected function assetsDir():string {
    return $this->assetsDir ??
      ($this->assetsDir = $this->sanitizeDir(
        option('arnoson.kirby-vite.assetsDir', '/')
      ));
  }

  /**
   * Get vite's root directory. So if vite serves our asset under
   * `localhost:3000/src/index.js`, `src` would be the root directory.
   */
  protected function rootDir(): string {
    return $this->rootDir ??
      ($this->rootDir = $this->sanitizeDir(
        option('arnoson.kirby-vite.rootDir', '/')
      ));
  }

  /**
   * Get vite's dev server url.
   */
  protected function devServer(): string {
    return $this->devServer ??
      ($this->devServer = option(
        'arnoson.kirby-vite.devServer',
        'http://localhost:3000'
      ));
  }

  /**
   * Check if we're in development mode.
   */
  public function isDev(): bool {
    return option('arnoson.kirby-vite.dev', false);
  }

  /**
   * Read and parse the manifest file.
   * 
   * @return array An associative array with the unhashed file name as key and
   * the hashed file name as value.
   */
  public function manifest(): array {
    if (isset($this->manifest)) {
      return $this->manifest;
    }

    $manifestPath =
      kirby()->root() . $this->outDir() . $this->assetsDir() . '/manifest.json';
    
    if (!F::exists($manifestPath)) {
      if (option('debug')) {
        throw new Exception('The `manifest.json` does not exist.');
      }
      return [];
    }

    return $this->manifest = json_decode(F::read($manifestPath), true);
  }

  /**
   * Get the url for the specified file for development mode.
   */
  protected function assetDev(string $fileName) {
    return $this->devServer() . $this->rootDir() . "/$fileName";
  }

  /**
   * Get the url for the specified file for production mode.
   */
  protected function assetProd(string $fileName) {
    $hashedFileName = $this->manifest()[$fileName] ?? null;

    if (!$hashedFileName) {
      if (option('debug')) {
        throw new Exception("No manifest entry exists for file `$fileName`");
      }
      return;
    }

    return kirby()->url('index') .
      $this->outDir() .
      $this->assetsDir() .
      "/$hashedFileName";
  }

  /**
   * Get the url for the specified file, depending on whether we are in
   * development or production mode.
   */
  public function asset(string $fileName): string {
    return $this->isDev()
      ? $this->assetDev($fileName)
      : $this->assetProd($fileName);
  }

  /**
   * Get vite's client url if we are in development mode.
   */
  public function client(): ?string {
    return $this->isDev()
      ? $this->devServer() . '/@vite/client'
      : null;
  }
}