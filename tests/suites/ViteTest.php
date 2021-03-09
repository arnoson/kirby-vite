<?php declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use arnoson\KirbyVite\Vite;

final class ViteTest extends TestCase {
  public function testClient () {
    // prod
    $vite = new Vite();
    $this->assertEquals(null, $vite->client());

    // dev
    $vite = new Vite(true);
    $this->assertEquals(
      '<script src="http://localhost:3000/@vite/client" type="module"></script>',
      $vite->client()
    );   
  }

  /**
   * @dataProvider provideJsData
   */  
  public function testJs($dev, $args, $result) {
    $vite = new Vite($dev);
    $this->assertEquals($result, $vite->js(...$args));
  }

  /**
   * @dataProvider provideCssData
   */  
  public function testCss($dev, $args, $result) {
    $vite = new Vite($dev);
    $this->assertEquals($result, $vite->css(...$args));    
  }

  public function provideJsData() {
    $distDir = '/site/plugins/kirby-vite/tests/dist';
    $devServer = 'http://localhost:3000';

    return [
      'production' => [
        false,
        [],
        "<script src=\"$distDir/assets/main.1234.js\" type=\"module\"></script>"
      ],
      'production, options' => [
        false,
        [null, ['async' => true]],
        "<script async src=\"$distDir/assets/main.1234.js\" type=\"module\"></script>"
      ],
      'production, entry' => [
        false,
        ['nested/index.js'],
        "<script src=\"$distDir/assets/nested.1234.js\" type=\"module\"></script>"
      ],
      'production, entry, options' => [
        false,
        ['nested/index.js', ['async' => true]],
        "<script async src=\"$distDir/assets/nested.1234.js\" type=\"module\"></script>"
      ],         
      'development' => [
        true,
        [],
        "<script src=\"$devServer/index.js\" type=\"module\"></script>"
      ]
    ];
  }

  public function provideCssData() {
    $distDir = '/site/plugins/kirby-vite/tests/dist';

    return [
      'production' => [
        false,
        [],
        "<link href=\"$distDir/assets/main.1234.css\" rel=\"stylesheet\">"
      ],
      'production, options' => [
        false,
        [null, ['media' => 'print']],
        "<link href=\"$distDir/assets/main.1234.css\" media=\"print\" rel=\"stylesheet\">"
      ],
      'production, entry' => [
        false,
        ['nested/index.js'],
        "<link href=\"$distDir/assets/nested.1234.css\" rel=\"stylesheet\">"
      ],
      'production, entry, options' => [
        false,
        ['nested/index.js', ['media' => 'print']],
        "<link href=\"$distDir/assets/nested.1234.css\" media=\"print\" rel=\"stylesheet\">"
      ],         
      'development' => [true, [], null]
    ];
  }
}