<?php 
	if (!empty($photo))
		$photoBox = '<div class="pi-Image"><div class="img-circle" style="background-image: url(\''.$photo.'\');"></div></div>';
		else 
		$photoBox = '
			<div class="pi-Image empty">
				<img src="'. $this->HOST .'themes/default/images/no_photo.svg" alt="no cover found" class="img img-responsive no-photo">
			</div>';
			
	$activePerson = $activeCorpo->getActivePersonValues();		
?>

<div class="pi-Body">
	<?= $photoBox ?>
	<div class="pi-Desc">
		<div class="pi-linkPanel"><?= $this->render('persons/linkPanel.php', ['AP' => $activePerson] ) ?></div>
		<div class="pi-head">
			<h4>
			  <a href="<?= $this->buildUri('wiki/record/Q'.$activePerson->wikiq); ?>" title="<?= $this->transEsc('card of')?>...">
				<?= $activeCorpo->get('labels') ?> 
			  </a>
			</h4>
		</div>
		<p><?= $this->helper->setLength($activeCorpo->get('descriptions'),155) ?></p>
		<?php if (empty($activeCorpo->bottomLink)): ?>
			<a class="pi-bottom-link" href="<?= $this->buildUri('wiki/record/Q'.$activePerson->wikiq); ?>" title="<?= $this->transEsc('card of')?>..."><?= $this->transEsc('More about') ?>...</a>
		<?php else: ?>	
			<a class="pi-bottom-link" href="<?= $activeCorpo->bottomLink ?>" title="<?= $activeCorpo->bottomStr ?>"><?= $activeCorpo->bottomStr ?> <span class="badge"><?= $activeCorpo->bottomCount ?></span></a>
		<?php endif; ?>	
	</div>
</div>
