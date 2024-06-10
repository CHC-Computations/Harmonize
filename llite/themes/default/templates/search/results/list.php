
<div class="result" id="result_<?=$result->id?>" OnMouseOver="results.FocusOn('result_<?=$result->id?>');" OnMouseOut="results.FocusOff();">
	<?= $this->buffer->resultCheckBox($result) ?>
	<div class='result-media' OnClick="results.preView('<?= $this->transEsc("Loading record") ?>...','<?= $result->id ?>');">
		<?= $this->render('record/cover.php', ['result' => $result]) ?>
	</div>
	<div class="result-body">
		<h4 class="title"><a href="<?= $this->basicUri('results/biblio/record/'.$result->id.'.html') ?>"><?= $this->helper->setLength($title = $record->title ,200) ?></a></h4>
		<div class="result-desc">
			<?php if (!empty($record->persons->mainAuthor)) 
				echo '<b>'.$this->transEsc('by').'</b>: '.$this->render('record/author-link.php', ['author'=>current((array)$record->persons->mainAuthor)]).'<br/>'; 
			?>
			<?php if (!empty($record->corporates->publisher)>0): ?>
				<b><?= $this->transEsc('In')?>:</b> <?= $this->render('record/publisher-link.php', ['publisher'=>current((array)$record->corporates->publisher), 'publicationYear' => current($record->publicationYear) ?? null ]) ?><br/>
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
		<button class="toolbar-btn" OnClick="results.preView('<?= $this->transEsc("Loading record") ?>...','<?= $result->id ?>');"><i class="ph-file-text-bold"></i></button>
	</div>
</div>
