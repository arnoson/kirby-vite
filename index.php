<?php declare(strict_types=1);

require_once __DIR__ . '/lib/Vite.php';

use Arnoson\KirbyVite\Vite;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

function vite(string $path, $options = []) {
  $vite = new Vite();

  // Client is special because it doesn't have a file extension we can use.
  if ($path === '@client') {
    return js($vite->client(), ['type' => 'module']);
  }

  static $placeholders = [
    '@entry' => 'index.js',
    '@style' => 'style.css'
  ];
  $path = $vite->asset($placeholders[$path] ?? $path);
  $extension = F::extension($path);
  
  if (Str::contains($extension, 'js')) {
    $options = array_merge(['type' => 'module'], $options);
    $result = js($path, $options);
  } else if (Str::contains($extension, 'css')) {
    // Vite will inject styles automatically in development mode.
    $result = $vite->isDev() ? '' : css($path, $options);
  } else {
    if (option('debug')) {
      throw new Exception('File type not recognized.');
    }
    return false;
  }

  return $result;
}