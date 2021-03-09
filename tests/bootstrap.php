<?php
require_once __DIR__ . '/../../../../kirby/bootstrap.php';

new Kirby\Cms\App([
  "roots" => [
    "config" => __DIR__ . '/kirby/config'
  ]
]);
