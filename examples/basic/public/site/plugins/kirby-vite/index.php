<?php declare(strict_types=1);

use arnoson\KirbyVite\Vite;

require_once __DIR__ . '/lib/Vite.php';

Kirby\Cms\App::plugin('arnoson/kirby-vite', [
  'options' => [
    'entry' => 'index.js',
    'outDir' => 'dist',
    'module' => true,
    'assetsDir' => 'assets'
  ]
]);

function vite() {
  return Vite::getInstance();
}