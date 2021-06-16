<?php declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use arnoson\KirbyVite\Vite;

function setMode($mode) {
  if ($mode === 'development') {
    $file = fopen(dirname(__DIR__, 2) . '/src/.lock', 'w');
    fclose($file);    
  } else if ($mode === 'production') {
    @unlink(dirname(__DIR__, 2) . '/src/.lock');
  }
}

final class ViteTest extends TestCase {
  public function testClient() {
    setMode('production');
    $vite = new Vite();
    $this->assertEquals(null, $vite->client());

    setMode('development');
    $vite = new Vite();
    $this->assertEquals(
      '<script src="http://localhost:3000/@vite/client" type="module"></script>',
      $vite->client()
    );
  }

  /**
   * @dataProvider provideJsData
   */
  public function testJs($dev, $args, $result) {
    setMode($dev ? 'development' : 'production');
    $vite = new Vite();
    $this->assertEquals($result, $vite->js(...$args));
  }

  /**
   * @dataProvider provideCssData
   */
  public function testCss($dev, $args, $result) {
    setMode($dev ? 'development' : 'production');
    $vite = new Vite();
    $this->assertEquals($result, $vite->css(...$args));
  }

  public function provideJsData() {
    $outDir = option('arnoson.kirby-vite.outDir');
    $devServer = 'http://localhost:3000';

    return [
      'production' => [
        false,
        [],
        "<script src=\"/$outDir/assets/main.1234.js\" type=\"module\"></script>"
      ],
      'production, options' => [
        false,
        [null, ['async' => true]],
        "<script async src=\"/$outDir/assets/main.1234.js\" type=\"module\"></script>"
      ],
      'production, entry' => [
        false,
        ['nested/index.js'],
        "<script src=\"/$outDir/assets/nested.1234.js\" type=\"module\"></script>"
      ],
      'production, entry, options' => [
        false,
        ['nested/index.js', ['async' => true]],
        "<script async src=\"/$outDir/assets/nested.1234.js\" type=\"module\"></script>"
      ],
      'development' => [
        true,
        [],
        "<script src=\"$devServer/index.js\" type=\"module\"></script>"
      ]
    ];
  }

  public function provideCssData() {
    $outDir = option('arnoson.kirby-vite.outDir');

    return [
      'production' => [
        false,
        [],
        "<link href=\"/$outDir/assets/main.1234.css\" rel=\"stylesheet\">"
      ],
      'production, options' => [
        false,
        [null, ['media' => 'print']],
        "<link href=\"/$outDir/assets/main.1234.css\" media=\"print\" rel=\"stylesheet\">"
      ],
      'production, entry' => [
        false,
        ['nested/index.js'],
        "<link href=\"/$outDir/assets/nested.1234.css\" rel=\"stylesheet\">"
      ],
      'production, entry, options' => [
        false,
        ['nested/index.js', ['media' => 'print']],
        "<link href=\"/$outDir/assets/nested.1234.css\" media=\"print\" rel=\"stylesheet\">"
      ],
      'development' => [true, [], null]
    ];
  }
}
