<?php

 
$key = $activePerson['hash'];

$a = [];
if (!empty($activePerson['name'])) $a['name'] = $activePerson['name'];
if (!empty($activePerson['date'])) $a['date'] = $activePerson['date'];
if (!empty($activePerson['id_type'])) {
	$id_link = '/' .$activePerson['id_type']. '_id' .$activePerson['id']. '/?'.http_build_query($a);
	} elseif (!empty($activePerson['id']))
	$id_link = '/' .$activePerson['id']. '/?'.http_build_query($a);
	else {
	$id_link = '?'.http_build_query($a);
	}

if (!empty($activePerson['viaflink'])) 
	$this->addJS("
		page.ajax('box_".$key."', '/results/person/{$this->urlName($activePerson['name'])}{$id_link}')
		");

?>

<div id="box_<?=$key?>">
	<i class="glyphicon glyphicon-info-sign" ></i>
	<div class="cloud-info"> 
		<h4><?= $activePerson['name'] ?></h4>
		:-) ??	
		<div class="bulkActionButtons">
		  <ul class="action-toolbar">
			<li><a href="<?= $this->basicUri('persons/record/') ?><?= $this->urlName($activePerson['name']) ?><?= $id_link ?>" title="<?=$this->transEsc('more with LiBRI')?>"><i class="ph-user-focus-bold"></i></a></li>
			<?php if (!empty($activePerson['viaflink'])): ?>
				<li><a href="<?=$activePerson['viaflink']?>" title="<?= $this->transEsc('See more on VIAF') ?>"><i class="ph-identification-card-bold"></i></a></li>
			<?php endif; ?>
			<?php if (!empty($activePerson['googlelink'])): ?>
				<a href="<?=$activePerson['googlelink']?>" title="<?= $this->transEsc('Search with Google')?>"><i class="ph-google-logo-bold"></i></a>
			<?php endif; ?>
		  </ul>	
		</div> 
	</div>
</div>
