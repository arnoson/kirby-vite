<?php

use Kirby\Cms\App;

require 'kirby/bootstrap.php';

echo (new App([
  'roots' => ['base' => __DIR__],
]))->render();
