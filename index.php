<?php declare(strict_types=1);

use arnoson\KirbyVite\Vite;

require_once __DIR__ . '/packages/kirby-vite/Vite.php';

Kirby\Cms\App::plugin('arnoson/kirby-vite', []);

function vite() {
  return Vite::getInstance();
}
