

<div class="result" id="result_<?=$result->id?>" >
	<?= $this->buffer->resultCheckBox($result) ?>
	<div class='result-media'>
		<?= $this->render('record/cover.php', ['rec' => $this->marc]) ?>
	</div>
	<div class="result-body">
		<h4 class="title"><a href="<?= $this->basicUri('search/record/'.$result->id.'.html') ?>"><?= $this->helper->setLength($result->title,60) ?></a></h4>
		<div class="result-desc">
			<?php if (!empty($auth)) echo '<b>'.$this->transEsc('by').'</b>: '.$this->render('record/author-link-simple.php', ['author'=>$auth]).'<br/>'; ?>
			<?php if (!empty($in = $this->marc->getIn())>0): ?>
				<b><?= $this->transEsc('In')?>:</b> <?= $in?><br/>
			<?php endif; ?>	
			<?php 
				$in = $this->marc->getPublished();
				if (count($in)>0): ?>
					<b><?= $this->transEsc('Published')?>:</b> <?= implode('<br/>', $in)?><br/>
			<?php endif; ?>	
			<span class="label label-primary"><?= $this->transEsc($this->marc->getFormat()) ?></span><br/>
			
		</div>
	</div>
	<div class="result-actions">
		<button OnClick="results.InModal('<?= $result->id ?>', '<?= base64_encode('<pre>'.print_r($result,1).'</pre>') ?>');">full</button>
	</div>
</div>
