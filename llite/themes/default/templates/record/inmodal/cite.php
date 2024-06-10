<?php 

$author = current((array)$this->record->get('persons', 'mainAuthor'));
$elbRecord = json_decode($rec->relations);
$title = $elbRecord->title; 

$publicationYear = current((array)$this->record->get('publicationYear'));

?> 



<h4>APA (7th ed.) Citation</h4>
<?= $this->record->getNamePart('last name', $author->name)?>, <?= substr($this->record->getNamePart('first name', $author->name),0,1) ?>. 
<?php if (!empty($publicationYear)) echo '('.$publicationYear.').'?> <i><?=$title ?></i>
<hr>



<h4>Chicago Style (17th ed.) Citation</h4>
<?= $author->name ?>. <i><?=$title ?></i><?php if (!empty($publicationYear)) echo ', '.$publicationYear.'.'?>
<hr>


<h4>MLA (8th ed.) Citation</h4>
<?= $author->name ?>. <i><?=$title ?></i><?php if (!empty($publicationYear)) echo ', '.$publicationYear.'.'?>
<hr>


<h4>Česká literární bibliografie</h4>


<?php
	$a = mb_strtoupper($this->record->getNamePart('last name', $author->name), "UTF-8") .', '. $this->record->getNamePart('first name', $author->name) .': ';
	
	$magazine = current((array)$this->record->get('magazines', 'sourceMagazine'));
	$In = '';
	if (!empty($magazine->title)) $In = $magazine->title;
	if (!empty($magazine->relatedPart)) $In .= '. '. $magazine->relatedPart;
	if (!empty($In)) $In .='.';
		
	$CLB = "$a<i>$title</i>. $In"; 
	if (!empty($publicationYear)) $CLB .=', '.$publicationYear.'.'
	?>

<p class="text-left"><?=$CLB ?></p>


<div class="text-muted text-center"><strong><?= $this->transEsc('Note') ?>:</strong> <?= $this->transEsc('These citations may not always be 100% accurate')?></div>

