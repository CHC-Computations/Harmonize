<?php if (!empty($author['name'])): ?>
	<a href="<?= $this->basicUri('event') ?>/<?= $this->urlName($author['name']) ?>/?<?= http_build_query($author)?>" title="<?= $this->transEsc('cart')?>: <?= $author['name'] ?>"><?= $author['name'] ?></a> 
	<?php if (!empty($author['date'])): ?>
		<span class="date"><?= $author['date'] ?></span>
	<?php endif; ?>
<?php endif; ?>