<?php 

$author = $this->record->getMainAuthor();
$title = $this->record->getTitle(); 

?> 



<h4>APA (7th ed.) Citation</h4>
<?= $author['last_name']?>, <?= substr($author['first_name'],0,1) ?>. <i><?=$title ?></i>




<h4>Chicago Style (17th ed.) Citation</h4>
<?= $author['last_name']?>, <?= $author['first_name'] ?>. <i><?=$title ?></i>



<h4>MLA (8th ed.) Citation</h4>
<?= $author['last_name']?>, <?= $author['first_name'] ?>. <i><?=$title ?></i>


<h4>Česká literární bibliografie</h4>


<?php
	$a = mb_strtoupper($author['last_name'], "UTF-8") .', '. $author['first_name'] .': ';
	
	$In = $this->record->getMarcLine(773, ['t'], '', '');
	$In .= '. '. $this->record->getMarcLine(773, ['g'], '', '');
		
	$CLB = "$a<i>$title</i>. $In."; 
	?>

<p class="text-left"><?=$CLB ?></p>


<div class="text-muted text-center"><strong><?= $this->transEsc('Note') ?>:</strong> <?= $this->transEsc('These citations may not always be 100% accurate')?></div>

