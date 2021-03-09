<nav>
  <ul>
    <?php foreach($site->children() as $child): ?>
    <li>
      <a href="<?= $child->url() ?>"><?= $child->title() ?></a>
    </li>
    <?php endforeach ?>
  </ul>
</nav>