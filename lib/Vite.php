<?php declare(strict_types=1);

namespace arnoson\KirbyVite;

use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use \Exception;

class Vite {
  protected static $instance = null;

  protected array $manifest;
  protected string $outDir;
  protected string $rootDir;
  protected string $devServer;

  public static function getInstance() {
    return self::$instance ??= new self();
  }

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
    return $this->outDir ??= $this->sanitizeDir(
      option('arnoson.kirby-vite.outDir', '/dist')
    );
  }

  /**
   * Get vite's root directory. So if vite serves our asset under
   * `localhost:3000/src/index.js`, `src` would be the root directory.
   */
  protected function rootDir(): string {
    return $this->rootDir ??= $this->sanitizeDir(
      option('arnoson.kirby-vite.rootDir', '/src')
    );
  }

  /**
   * Get vite's dev server url.
   */
  protected function devServer(): string {
    return $this->devServer ??= option(
      'arnoson.kirby-vite.devServer',
      'http://localhost:3000'
    );
  }

  /**
   * Check for `.lock` file in vite's root dir as indicator for development
   * mode.
   */
  protected function hasLockFile(): bool {
    $lockFile = kirby()->root('base') . $this->rootDir() . '/.lock';
    return F::exists($lockFile);
  }

  /**
   * Check if we're in development mode.
   */
  protected function isDev(): bool {
    return option('arnoson.kirby-vite.dev') ?? $this->hasLockFile();
  }

  /**
   * Read and parse the manifest file.
   *
   * @throws Exception
   */
  protected function manifest(): array {
    if (isset($this->manifest)) {
      return $this->manifest;
    }

    $manifestPath = kirby()->root() . $this->outDir() . '/manifest.json';

    if (!F::exists($manifestPath)) {
      if (option('debug')) {
        throw new Exception('The `manifest.json` does not exist.');
      }
      return [];
    }

    return $this->manifest = json_decode(F::read($manifestPath), true);
  }

  /**
   * Get the value of a manifest property for a specific entry.
   *
   * @throws Exception
   */
  protected function getManifestProperty(string $entry = null, $key = 'file') {
    $entry ??= option('arnoson.kirby-vite.entry');
    $manifestEntry = $this->manifest()[$entry] ?? null;
    if (!$manifestEntry) {
      if (option('debug')) {
        throw new Exception("`$entry` is not a manifest entry.");
      }
      return;
    }

    $value = $manifestEntry[$key] ?? null;
    if (!$value) {
      if (option('debug')) {
        throw new Exception(
          "Manifest entry `$entry` doesn't have property `$key`."
        );
      }
      return;
    }

    return $value;
  }

  /**
   * Get the url for the specified file for development mode.
   */
  protected function assetDev(string $file) {
    return $this->devServer() . "/$file";
  }

  /**
   * Get the URL for the specified file for production mode.
   */
  protected function assetProd(string $file) {
    $root = kirby()->url('index');
    return ($root === '/' ? '' : $root) . $this->outDir() . "/$file";
  }

  /**
   * Include vite's client in development mode.
   */
  public function client(): ?string {
    return $this->isDev()
      ? js($this->assetDev('@vite/client'), ['type' => 'module'])
      : null;
  }

  /**
   * Include the css file for the specified entry in production mode.
   */
  public function css($entry = null, array $options = null): ?string {
    return !$this->isDev()
      ? css(
        $this->assetProd($this->getManifestProperty($entry, 'css')[0]),
        $options
      )
      : null;
  }

  /**
   * Include the js file for the specified entry.
   */
  public function js($entry = null, $options = []): ?string {
    $file = $this->isDev()
      ? $this->assetDev($entry ?? option('arnoson.kirby-vite.entry'))
      : $this->assetProd($this->getManifestProperty($entry, 'file'));

    if ($this->isDev() || option('arnoson.kirby-vite.module')) {
      $options = array_merge(['type' => 'module'], $options);
    }

    return js($file, $options);
  }
}
