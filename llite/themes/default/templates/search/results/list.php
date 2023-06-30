
<div class="result" id="result_<?=$result->id?>" OnMouseOver="results.FocusOn('result_<?=$result->id?>');" OnMouseOut="results.FocusOff();">
	<?= $this->buffer->resultCheckBox($result) ?>
	<div class='result-media' OnClick="results.preView('<?= $this->transEsc("Loading record") ?>...','<?= $result->id ?>');">
		<?= $this->render('record/cover.php', ['rec' => $this->marc]) ?>
	</div>
	<div class="result-body">
		<h4 class="title"><a href="<?= $this->basicUri('search/record/'.$result->id.'.html') ?>"><?= $this->helper->setLength($title=$this->marc->getTitle(),200) ?></a></h4>
		<div class="result-desc">
			<?php if (!empty($auth['name'])) echo '<b>'.$this->transEsc('by').'</b>: '.$this->render('record/author-link.php', ['author'=>$auth]).'<br/>'; ?>
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
		<?php if (!empty($this->user->LoggedIn)): ?>
		<button class="toolbar-btn" OnClick="results.InModal('<?= $result->id ?>', '<?= base64_encode('<pre>'.print_r($result,1).'</pre>') ?>');"><i class="ph-wrench-bold"></i></button>
		<?php if (!empty($marcJson->errors))
				echo '<button class="toolbar-btn" title="'.$marcJson->errors.'" type=button><i class="ph-file-x-bold"></i></button>';
		?>
		<?php endif; ?>
		<button class="toolbar-btn" OnClick="results.preView('<?= $this->transEsc("Loading record") ?>...','<?= $result->id ?>');"><i class="ph-file-text-bold"></i></button>
	</div>
</div>
