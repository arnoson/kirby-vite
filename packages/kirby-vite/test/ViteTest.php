<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/Vite.php';
use arnoson\KirbyVite\Vite;

function setMode($mode) {
  if ($mode === 'development') {
    file_put_contents(__DIR__ . '/.dev', 'VITE_SERVER=http://localhost:5173');
  } elseif ($mode === 'production') {
    @unlink(__DIR__ . '/.dev');
  }
}

beforeEach(function () {
  $this->vite = new Vite();
});

it('generates JS for development', function () {
  setMode('development');

  $result =
    "<script src=\"http://localhost:5173/@vite/client\" type=\"module\"></script>\n<script src=\"http://localhost:5173/main.js\" type=\"module\"></script>";

  expect($this->vite->js('main.js'))->toBe($result);
});

it('generates JS for production', function () {
  setMode('production');

  $result =
    "<script src=\"/dist/assets/main.1234.js\" type=\"module\"></script>";
  expect($this->vite->js('main.js'))->toBe($result);

  $result =
    "<script data-test=\"test\" defer src=\"/dist/assets/main.1234.js\" type=\"module\"></script>";
  $options = ['defer' => true, 'data-test' => 'test'];
  expect($this->vite->js('main.js', $options))->toBe($result);
});

it('omits CSS for development', function () {
  setMode('development');
  expect($this->vite->css('main.css'))->toBe(null);
});

it('generates CSS for production', function () {
  setMode('production');

  $result = "<link href=\"/dist/assets/main.1234.css\" rel=\"stylesheet\">";
  expect($this->vite->css('main.css'))->toBe($result);

  // Require the CSS for a JS file.
  $result = "<link href=\"/dist/assets/main.1234.css\" rel=\"stylesheet\">";
  expect($this->vite->css('main.js'))->toBe($result);

  $result =
    "<link data-test=\"test\" href=\"/dist/assets/main.1234.css\" media=\"print\" rel=\"stylesheet\">";
  $options = ['media' => 'print', 'data-test' => 'test'];
  expect($this->vite->css('main.css', $options))->toBe($result);
});

it('provides a file path for development', function () {
  setMode('development');
  expect($this->vite->file('my-font.woff2'))->toBe(
    'http://localhost:5173/my-font.woff2'
  );
});

it('provides a file path for production', function () {
  setMode('production');
  expect($this->vite->file('my-font.woff2'))->toBe(
    '/dist/assets/my-font.1234.woff2'
  );
});
