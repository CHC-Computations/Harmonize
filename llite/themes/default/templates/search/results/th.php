

<div class="result" id="result_<?=$result->id?>" >
	<?= $this->buffer->resultCheckBox($result) ?>
	<div class='result-media'>
		<?= $this->render('record/cover.php', ['result' => $result]) ?>
	</div>
	<div class="result-body">
		<h4 class="title"><a href="<?= $this->basicUri('results/biblio/record/'.$result->id.'.html') ?>"><?= $this->helper->setLength($result->title,60) ?></a></h4>
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
		<button OnClick="results.InModal('<?= $result->id ?>', '<?= base64_encode('<pre>'.print_r($result,1).'</pre>') ?>');">full</button>
	</div>
</div>
