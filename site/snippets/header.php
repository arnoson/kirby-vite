<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kirby Vite Basic</title>
  <?= vite()->js(null, ['defer' => true]) ?>
  <?= vite()->css() ?>
</head>
<body>