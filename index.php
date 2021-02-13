<?php declare(strict_types=1);

require_once __DIR__ . '/lib/Vite.php';

use Arnoson\KirbyVite\Vite;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

/**
 * Get the appropriate HTML tag with the right path for the (versioned) asset
 * file.
 * 
 * @param string|array $path Path as it appears in the manifest.json
 * @param string|bool|array $options Pass an array of attributes for the tag 
 * or a string/bool. A string/bool behaves in the same way as in Kirby's `css()` 
 * and `js()` helper functions: for css files it will be used as the value 
 * of the media attribute, for js files it will determine wether or not the 
 * script is async. By default the `type` attribute for a script tag will be
 * `module` but you can override this behaviour.
 */
function vite($path, $options = []) {
  $vite = new Vite();

  // Client is special because it doesn't have a file extension we can use.
  if ($path === '@client') {
    $client = $vite->client();
    return $client ? js($client, ['type' => 'module']) : '';
  }

  static $placeholders = [
    '@entry' => 'index.js',
    '@style' => 'index.css'
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