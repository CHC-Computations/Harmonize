<?php 
	if (!empty($result->solrRecord->picture))
		$photoBox = '<div class="box-Image"><div class="box-img-exists" style="background-image: url(\''.current($result->solrRecord->picture).'\');"></div></div>';
		else 
		$photoBox = '
			<div class="box-Image empty">
				<img src="'. $this->HOST .'themes/default/images/no_photo.svg" alt="no cover found" class="img img-responsive no-photo">
			</div>';
?>

<div class="box-Body">
	<?= $photoBox ?>
	<div class="box-head">
		<h4>
		  <a href="<?= $this->buildUri('wiki/record/'.$result->solrRecord->wikiq); ?>" title="<?= $this->transEsc('card of')?>...">
			<?= $result->getStr('labels') ?> 
		  </a>
		</h4>
	</div>
	<div class="box-Desc">
		<p><?= $this->helper->setLength($result->getStr('descriptions'),155) ?></p>
	</div>
	<div class="box-linkPanel"><?= $this->render('wikiResults/resultBoxes/linkPanel.php', ['AP' => $result->linkPanel()] ) ?></div>
</div>
