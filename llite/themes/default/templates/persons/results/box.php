<?php 


?>

<?php if (!empty($person->id)): ?>

	<div class="result">
		
		<div class="result-media">
			<?= $this->person->getImageBG() ?>

		</div>
			
		<div class="result-body">
			<h4><a href="<?= $this->person->createLibriLink() ?>"><?= $this->person->getName() ?><br/><small><?= $this->person->getDateRange() ?></small></a></h4>
			<p><?= $this->person->getDescription() ?></p>
			<a class="pi-bottom-link" href="<?= $this->person->createLibriLink() ?>" title="<?= $this->transEsc('card of')?>..."><?= $this->transEsc('More about') ?>...</a>
		
		</div>
		<div class="result-footer"><?= $this->person->getLinkPanel() ?></div>
	</div>
<?php endif; ?>