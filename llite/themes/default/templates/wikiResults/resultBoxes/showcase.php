<?php 
	if (!empty($result->solrRecord->picture))
		$photoBox = '<div class="box-Image"><div class="box-img-exists" style="background-image: url(\''.current($result->solrRecord->picture).'\');"></div></div>';
		else 
		$photoBox = '
			<div class="box-Image empty">
				<img src="'. $this->HOST .'themes/default/images/no_photo.svg" alt="no cover found" class="img img-responsive no-photo">
			</div>';
			
	$matchLevelStr = '';		
	if (!empty($matchLevel)) {
		$result->solrRecord->bottomLink = $this->buildUrl('home/about/methods/wikiDataMatching');
		$result->solrRecord->bottomStr = $this->render('helpers/matchLevel.php', ['matchLevel'=>$matchLevel]);
		$result->solrRecord->bottomTitle = '';
		}
		
		
	$desc = $result->getStr('descriptions');
	if (!empty($result->getDateRange()) & !empty($desc)) {
		$dates = '('.$result->getDateRange().')';
		$desc = str_replace($dates, '', $desc);
		}
	
?>

<div class="box-Body">
	<?= $photoBox ?>
	<div class="box-Desc">
		<div class="box-linkPanel"><?= $this->render('wikiResults/resultBoxes/linkPanel.php', ['AP' => $result->linkPanel()] ) ?></div>
		<div class="box-head">
			<h4>
			  <a href="<?= $this->buildUri('wiki/record/'.$result->solrRecord->wikiq); ?>" title="<?= $this->transEsc('card of')?>...">
				<?= $result->getStr('labels') ?> 
				
			  </a>
			  <?php if (!empty($result->getDateRange())) echo '<small class="box-date">'.$result->getDateRange().'</small>'; ?>
			</h4>
			
		</div>
		<p><?= $this->helper->setLength($desc,155) ?></p>
		
	</div>
	<?php if (empty($result->solrRecord->bottomLink)): ?>
			<a class="box-bottom-link" href="<?= $this->buildUri('wiki/record/'.$result->solrRecord->wikiq); ?>" title="<?= $this->transEsc('card of')?>..."><?= $this->transEsc('More about') ?>...</a>
		<?php else: ?>	
			<a class="box-bottom-link-left" href="<?= $result->solrRecord->bottomLink ?>" title="<?= $result->solrRecord->bottomTitle ?>"><?= $result->solrRecord->bottomStr ?></a>
		<?php endif; ?>	
		
</div>
