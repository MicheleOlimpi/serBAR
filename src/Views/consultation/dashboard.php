<h4>Consultazione turni</h4>
<div class="mb-3"><a class="btn btn-danger" href="?action=logout">Logout</a></div>
<ul>
<?php foreach($boards as $b): ?>
<li><a href="?action=board_edit&id=<?= $b['id'] ?>">Visualizza tabellone <?= sprintf('%02d/%04d',$b['month'],$b['year']) ?></a></li>
<?php endforeach; ?>
</ul>
