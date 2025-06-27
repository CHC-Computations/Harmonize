<?php $box_id = str_replace('.','_', $result->id); ?>

<div class="result" id="result_<?= $box_id ?>" OnMouseOver="results.FocusOn('result_<?=$box_id?>');" OnMouseOut="results.FocusOff();">
	<div class="result-number" id="check_<?=$box_id?>"><?= $this->bookcart->resultCheckBox($result) ?></div>
	<div id="stickyArea<?=$box_id?>"><?= $this->bookcart->resultStickyNote($result->id) ?></div>
	<div class='result-media' OnClick="results.preViewCopy('<?=  $record->title ?>', '<?= $box_id ?>');">
		<?= $this->render('record/cover.php', ['result' => $result]) ?>
	</div>
	<div class="result-body">
		<h4 class="title"><a href="<?= $this->basicUri('results/biblio/record/'.$result->id.'.html') ?>"><?= $this->helper->setLength($title = $record->title ,200) ?></a></h4>
		<div class="result-desc">
			<?php if (!empty($record->persons->mainAuthor)) 
				echo '<b>'.$this->transEsc('Main Author').'</b>: '.$this->render('record/author-link.php', ['author'=>current((array)$record->persons->mainAuthor), 'facetField' => 'author_facet']).'<br/>'; 
			?>
			<?php if (!empty($record->corporates->publisher) && ($record->majorFormat!=='Book') && empty($record->publishedIn)): ?>
				<b><?= $this->transEsc('In')?>:</b> <?= $this->render('record/publisher-link.php', ['publisher'=>current((array)$record->corporates->publisher), 'publicationYear' => current($record->publicationYear) ?? null ]) ?><br/>
			<?php endif; ?>	
			<?php if (!empty($record->sourceDocument)): ?>
				<b><?= $this->transEsc('In')?>:</b> <?= $this->render('record/source-link.php', ['source'=>current((array)$record->sourceDocument), 'facetField' => 'magazines_str_mv' ]) ?><br/>
			<?php endif; ?>	
			<?php if (!empty($record->magazines->sourceMagazine)): ?>
				<b><?= $this->transEsc('In')?>:</b> <?= $this->render('record/source-link.php', ['source'=>current((array)$record->magazines->sourceMagazine), 'facetField' => 'magazines_str_mv' ]) ?><br/>
			<?php endif; ?>	
			<?php 
				if (!empty($record->publishedIn)) {
					echo '<b>'.$this->transEsc('Published').'</b>: ';
					foreach ($record->publishedIn as $in)
						echo $in.'<br/>';
					}
			?>	
			<span class="label label-primary"><?= $this->transEsc($record->majorFormat) ?></span><br/>
			
		</div>
	</div>
	<div class="result-actions">
		<?php if (!empty($this->user->LoggedIn)): ?>
		<button class="toolbar-btn" OnClick="results.InModal('<?= $result->id ?>', '<?= base64_encode('<pre>'.print_r($result,1).'</pre>') ?>');"><i class="ph-wrench-bold"></i></button>
		<?php endif; ?>
	</div>
</div>

<div class="hidden" id="previewbox_<?= $box_id ?>">
	<?= $this->render('record/inmodal/core.php', ['record' => $this->record ]) ?>
</div>