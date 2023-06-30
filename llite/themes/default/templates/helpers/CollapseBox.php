<?php 
	if (empty($idDescBox))
		$idDescBox = uniqid(); 
	if (empty($minSize))
		$minSize = 134; 
?>

<div class="collapseBox" id="<?= $idDescBox ?>">
	<input type="hidden" id="<?= $idDescBox ?>_maxSize">
	<input type="hidden" id="<?= $idDescBox ?>_minSize" value="<?= $minSize ?>">
	<div class="collapseBox-body">
		<?= $desc ?>
	</div>
	<div class="collapseBox-bottom text-right">
		<button class="toolbar-btn show-btn" OnClick="colbox.Show('<?= $idDescBox ?>')"><i class="ph-caret-down-bold"></i> <?=$this->transEsc('More')?> ...</button>
		<button class="toolbar-btn hide-btn" OnClick="colbox.Hide('<?= $idDescBox ?>')"><i class="ph-caret-up-bold"></i> <?=$this->transEsc('Less')?> ...</button>
	</div>
</div>
<?php $this->addJS("colbox.Check('$idDescBox')"); ?>