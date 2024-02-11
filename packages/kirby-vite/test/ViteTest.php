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

it('throws errors for missing JS entries', function () {
  setMode('production');
  $this->vite->js('does-not-exists.js');
})->throws('`does-not-exists.js` is not a manifest entry.');

it('it omits errors for missing JS entries when trying', function () {
  setMode('production');
  $this->vite->js('does-not-exists.js', try: true);
})->throwsNoExceptions();

it('omits CSS imported by JS in development', function () {
  setMode('development');
  // We don't expect any css for the js entry, but the vite client is injected
  // because this is the first css()/js() call.
  expect($this->vite->css('main.js'))->toBe(
    '<script src="http://localhost:5173/@vite/client" type="module"></script>'
  );
  expect($this->vite->css('main.css'))->toBe(
    '<link href="http://localhost:5173/main.css" rel="stylesheet">'
  );
});

it('generates CSS for production', function () {
  setMode('production');

  $result = "<link href=\"/dist/assets/main.1234.css\" rel=\"stylesheet\">";
  expect($this->vite->css('main.css'))->toBe($result);

  // Require the CSS for a JS file (and its imports).
  $result =
    "<link href=\"/dist/assets/main.1234.css\" rel=\"stylesheet\">\n" .
    "<link href=\"/dist/assets/chunk-1234.css\" rel=\"stylesheet\">\n" .
    "<link href=\"/dist/assets/chunk-5678.css\" rel=\"stylesheet\">";
  expect($this->vite->css('main.js'))->toBe($result);

  $result =
    "<link data-test=\"test\" href=\"/dist/assets/main.1234.css\" media=\"print\" rel=\"stylesheet\">";
  $options = ['media' => 'print', 'data-test' => 'test'];
  expect($this->vite->css('main.css', $options))->toBe($result);
});

it('throws errors for missing CSS entries', function () {
  setMode('production');
  $this->vite->css('does-not-exists.css');
})->throws('`does-not-exists.css` is not a manifest entry.');

it('it omits errors for missing CSS entries when trying', function () {
  setMode('production');
  $this->vite->css('does-not-exists.css', try: true);
})->throwsNoExceptions();

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

it('throws errors for missing file', function () {
  setMode('production');
  $this->vite->file('does-not-exist.woff2');
})->throws('`does-not-exist.woff2` is not a manifest entry.');

it('omits errors for missing file when trying', function () {
  setMode('production');
  $this->vite->file('does-not-exist.woff2', try: true);
})->throwsNoExceptions();

it('generates panel assets in development', function () {
  setMode('development');
  expect($this->vite->panelJs())->toBe(['@vite/client']);
  expect($this->vite->panelJs('main.js'))->toBe([
    '@vite/client',
    'http://localhost:5173/main.js',
  ]);
  expect($this->vite->panelCss('main.js'))->toBe(null);
  expect($this->vite->panelCss('main.css'))->toBe([
    'http://localhost:5173/main.css',
  ]);
});

it('generates panel assets in production', function () {
  setMode('production');
  expect($this->vite->panelJs())->toBe(null);
  expect($this->vite->panelJs('main.js'))->toBe(['dist/assets/main.1234.js']);
  expect($this->vite->panelCss('main.js'))->toBe([
    'dist/assets/main.1234.css',
    'dist/assets/chunk-1234.css',
    'dist/assets/chunk-5678.css',
  ]);
  expect($this->vite->panelCss('main.css'))->toBe([
    'dist/assets/main.1234.css',
  ]);
});
