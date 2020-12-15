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

  protected function sanitizePath(string $dir): string {
    if (!Str::startsWith($dir, '/')) {
      $dir = "/{$dir}";
    }

    if (Str::endsWith($dir, '/')) {
      $dir = substr($dir, 0, -1);
    }

    return $dir;
  }

  protected function outDir(): string {
    return $this->outDir ??
      ($this->outDir = $this->sanitizePath(
        option('arnoson.kirby-vite.outDir', '/assets')
      ));
  }

  protected function assetsDir():string {
    return $this->assetsDir ??
      ($this->assetsDir = $this->sanitizePath(
        option('arnoson.kirby-vite.assetsDir', '/')
      ));
  }

  protected function rootDir(): string {
    return $this->rootDir ??
      ($this->rootDir = $this->sanitizePath(
        option('arnoson.kirby-vite.rootDir', '/')
      ));
  }

  protected function devServer(): string {
    return $this->devServer ??
      ($this->devServer = option(
        'arnoson.kirby-vite.devServer',
        'http://localhost:3000'
      ));
  }

  public function isDev(): bool {
    return option('arnoson.kirby-vite.dev', false);
  }

  public function manifest(string $fileName = null) {
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

  protected function assetDev(string $fileName) {
    return $this->devServer() . $this->rootDir() . "/$fileName";
  }

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

  public function asset(string $fileName): string {
    return $this->isDev()
      ? $this->assetDev($fileName)
      : $this->assetProd($fileName);
  }

  public function client(): ?string {
    return $this->isDev()
      ? $this->devServer() . '/vite/client'
      : null;
  }
}