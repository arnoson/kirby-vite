<?php

return [
  'debug' => true,
  'ready' => fn() => [
    'panel' => [
      'css' => vite()->panelCss('panel.js'),
      'js' => vite()->panelJs('panel.js'),
    ],
  ],
];
