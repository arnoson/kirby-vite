<?php declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use arnoson\KirbyVite\Vite;

function setMode($mode) {
  if ($mode === 'development') {
    file_put_contents(dirname(__DIR__, 2) . '/.dev', "VITE_SERVER=http://localhost:5173");  
  } else if ($mode === 'production') {
    @unlink(dirname(__DIR__, 2) . '/.dev');
  }
}

final class ViteTest extends TestCase {
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

  public function testFile() {
    $outDir = option('arnoson.kirby-vite.outDir');
    $devServer = 'http://localhost:5173';
    $vite = new Vite();

    setMode('development');
    $this->assertEquals(
      "$devServer/my-font.woff2",
      $vite->file('my-font.woff2')
    );

    setMode('production');
    $this->assertEquals(
      "/$outDir/assets/my-font.1234.woff2",
      $vite->file('my-font.woff2')
    );
  }

  static public function provideJsData() {
    $outDir = option('arnoson.kirby-vite.outDir');
    $devServer = 'http://localhost:5173';

    return [
      'production' => [
        false,
        ['main.js'],
        "<script src=\"/$outDir/assets/main.1234.js\" type=\"module\"></script>"
      ],
      'production, options' => [
        false,
        ['main.js', ['async' => true]],
        "<script async src=\"/$outDir/assets/main.1234.js\" type=\"module\"></script>"
      ],
      'development' => [
        true,
        ['main.js'],
        "<script src=\"http://localhost:5173/@vite/client\" type=\"module\"></script>\n<script src=\"$devServer/main.js\" type=\"module\"></script>"
      ]
    ];
  }

  static public function provideCssData() {
    $outDir = option('arnoson.kirby-vite.outDir');

    return [
      'production' => [
        false,
        ['main.css'],
        "<link href=\"/$outDir/assets/main.1234.css\" rel=\"stylesheet\">"
      ],
      'production, options' => [
        false,
        ['main.css', ['media' => 'print']],
        "<link href=\"/$outDir/assets/main.1234.css\" media=\"print\" rel=\"stylesheet\">"
      ],
      'development' => [
        true,
        ['main.css'],
        null
      ]
    ];
  }
}
