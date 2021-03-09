<?php
/** @var Kirby\Cms\Page $page */
$template = $page->template();
$entry = "templates/$template/index.js";
?>

<?= vite()->js($entry) ?>
</body>
</html>