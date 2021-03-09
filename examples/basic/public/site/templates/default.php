<html>
  <head>
    <?= vite()->client() ?>
    <?= vite()->css() ?>
  </head>
  <body>
    <h1><?= $page->title() ?></h1>
    <?= vite()->js() ?>
  </body>
</html>