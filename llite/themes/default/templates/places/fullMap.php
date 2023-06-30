<div style="position:relative;">
	<div class="fullMap">
		<?= $this->maps->drawWorldMap() ?>
	</div>
	<div id="ajaxBox" class="mapPopup">
		<small><?=$this->transEsc("loading data")?> ... </small>
		<?=$this->helper->loader2("loading data")?>
	</div>
</div>