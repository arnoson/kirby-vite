<?php declare(strict_types=1);

namespace arnoson\KirbyVite;
use Kirby\Filesystem\F;
use \Exception;

class Vite {
  protected static Vite $instance;
  protected $isFirstScript = true;

  public static function getInstance() {
    return self::$instance ??= new self();
  }

  /**
   * Check if we're in development mode.
   */
  protected function isDev(): bool {
    return F::exists(kirby()->root('base') . '/.dev');
  }

  /**
   * Read vite's dev server from the `.dev` file.
   *
   * @throws Exception
   */  
  protected function server() {
    $dev = F::read(kirby()->root('base') . '/.dev');

    [$key, $value] = explode('=', trim($dev), 2);
    if ($key !== 'VITE_SERVER' && option('debug')) {
      throw new Exception('VITE_SERVER not found in `.dev` file.');
    }

    return $value;
  }

  /**
   * Read and parse the manifest file.
   *
   * @throws Exception
   */
  public function manifest(): array {
    if (isset($this->manifest)) {
      return $this->manifest;
    }

    $manifestPath = kirby()->root('index') . '/' . option('arnoson.kirby-vite.outDir', 'dist') . '/manifest.json';

    if (!F::exists($manifestPath)) {
      if (option('debug')) {
        throw new Exception('`manifest.json` not found.');
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
  protected function manifestProperty(string $entry = null, $key = 'file') {
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
        throw new Exception("{$key} not found in manifest entry {$entry}");
      }
      return;
    }

    return $value;
  }

  /**
   * Get the url for the specified file for development mode.
   */
  protected function assetDev(string $file) {
    return $this->server() . "/$file";
  }

  /**
   * Get the URL for the specified file for production mode.
   */
  protected function assetProd(string $file) {
    return '/' . option('arnoson.kirby-vite.outDir', 'dist') . "/$file";
  }

  /**
   * Include vite's client in development mode.
   */
  protected function client(): ?string {
    return $this->isDev()
      ? js($this->assetDev('@vite/client'), ['type' => 'module'])
      : null;
  }

  /**
   * Include the js file for the specified entry.
   */
  public function js($entry = null, $options = []): ?string {
    $file = $this->isDev()
      ? $this->assetDev($entry ?? option('arnoson.kirby-vite.entry'))
      : $this->assetProd($this->manifestProperty($entry, 'file'));

    if ($this->isDev() || option('arnoson.kirby-vite.module')) {
      $options = array_merge(['type' => 'module'], $options);
    }

    $legacy = option('arnoson.kirby-vite.legacy'); 
    // There might be multiple `vite()->js()` calls but some scripts
    // (vite client, legacy polyfills) should be only included once per page.
    $scripts = [
      $this->isFirstScript ? $this->client() : null,
      ($this->isFirstScript && $legacy) ? $this->legacyPolyfills() : null,
      $legacy ? $this->legacyJs($entry) : null,
      js($file, $options)
    ];

    $this->isFirstScript = false;
    return implode("\n", array_filter($scripts));

  }  

  /**
   * Include the css file for the specified entry in production mode.
   */
  public function css($entry = null, array $options = null): ?string {
    return !$this->isDev()
      ? css(
        $this->assetProd($this->manifestProperty($entry, 'css')[0]),
        $options
      )
      : null;
  }

  public function legacyPolyfills($options = []): ?string {
    if ($this->isDev()) return null;
    
    $entry = null;
    foreach (array_keys($this->manifest()) as $key) {
      // The legacy entry is relative from vite's root folder (e.g.:
      // `../vite/legacy-polyfills-legacy`). To handle all cases we just check
      // for the ending.
      if (str_ends_with($key, "vite/legacy-polyfills-legacy")) {
        $entry = $key;
        break;
      }  
    }
    $file = $this->assetProd($this->manifestProperty($entry, 'file'));

    return js($file, array_merge(['nomodule' => true], $options));
  }

  public function legacyJs($entry = null, $options = []): ?string {
    if ($this->isDev()) return null;

    $entry ??= option('arnoson.kirby-vite.entry');
    $parts = explode('.', $entry);
    $parts[count($parts) - 2] .= '-legacy';
    $legacyEntry = join('.', $parts);

    $file = $this->assetProd($this->manifestProperty($legacyEntry, 'file'));
    return js($file, array_merge(['nomodule' => true], $options));
  }  
}