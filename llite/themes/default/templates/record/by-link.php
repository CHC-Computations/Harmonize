

<div class="record-list-item" id="extra_rec_<?=$rec['id']?>">
	<h4><a href="<?= $this->basicUri('record') ?>/<?= $this->urlName($rec['id']) ?>.html"><?= $rec['title']?></a></h4>
		<?php if (!empty($rec['publisher'])) echo $rec['publisher'].', ';?>
		<?php if (!empty($rec['place'])) echo $rec['place'].', ';?> 
		<?php if(!empty($rec['nr'])) $rec['nr'].', '; ?>
		<?php if(!empty($rec['pages'])) $rec['pages'].', '; ?>
		<br/>
	<?php if (!empty($rec['author'])) echo current($rec['author']) ?><br/>
</div>
<pre><?=print_r($rec,1)?></pre>

<?php $this->JS[] = "results.miniPreView('$rec[id]', '$lp');";