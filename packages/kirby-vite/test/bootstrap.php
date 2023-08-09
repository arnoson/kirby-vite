<?php
require_once dirname(__DIR__) . '/kirby/bootstrap.php';

new Kirby\Cms\App([
  'roots' => [
    'base' => __DIR__,
    'index' => __DIR__,
    'config' => __DIR__ . '/config',
  ],
]);
