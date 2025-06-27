<?php 
if (!stristr($rec['id'], $recPrefix.'.')) {
	$newRecId = $recPrefix.'.'.$rec['id'];
	} else 
	$newRecId = $rec['id'];

$recIdField = str_replace('.','_', $newRecId);

$this->addJS("results.miniPreView('$newRecId', '$lp');");

?>

<div class="record-list-item" id="extra_rec_<?= $recIdField ?>">
	<h4><a href="<?= $this->basicUri('results/biblio/record') ?>/<?= $newRecId ?>.html"><?= $rec['title']?></a></h4>
		<?php if (!empty($rec['publisher'])) echo $rec['publisher'].', ';?>
		<?php if (!empty($rec['place'])) echo $rec['place'].', ';?> 
		<?php if(!empty($rec['nr'])) $rec['nr'].', '; ?>
		<?php if(!empty($rec['pages'])) $rec['pages'].', '; ?>
		<br/>
	<?php if (!empty($rec['author'])) echo current((array)$rec['author']) ?><br/>
</div>

