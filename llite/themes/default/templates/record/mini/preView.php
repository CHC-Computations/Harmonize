<?php 

$this->addClass('marc', new marc21($marcJson)); 
$this->marc->setBasicUri($this->basicUri());
$this->marc->getCoreFields();
?>

<div class="result" >
	<div class="result-number">
		<?= $this->GET['lp'] ?>.
	</div>
	<div class='result-media' OnClick="results.preView('<?= $this->transEsc("Loading record") ?>...','<?= $result->id ?>');">
		<?= $this->render('record/cover.php', ['rec' => $this->marc]) ?>
	</div>
	<div class="result-body">
		<h4 class="title"><a href="<?= $this->basicUri('search/record/'.$result->id.'.html') ?>"><?= $title=$this->marc->getTitle() ?></a></h4>
		<div class="result-desc">
			<?php if (!empty($auth = $this->marc->getMainAuthorLink())) echo '<b>'.$this->transEsc('by').'</b>: '.$auth.'<br/>'; ?>
			<?php if (!empty($in = $this->marc->getIn())>0): ?>
				<b><?= $this->transEsc('In')?>:</b> <?= $in?><br/>
			<?php endif; ?>	
			<?php 
				$in = $this->marc->getPublished();
				if (count($in)>0): ?>
					<b><?= $this->transEsc('Published')?>:</b> <?= implode('<br/>', $in)?><br/>
			<?php endif; ?>	
			<?= $this->marc->getFormat() ?><br/>
			
		</div>
	</div>
</div>