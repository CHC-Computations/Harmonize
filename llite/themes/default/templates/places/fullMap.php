<div style="position:relative;">
	<div class="fullMap">
		<?= $this->maps->drawWorldMap() ?>
	</div>
	<div id="ajaxBox" class="mapPopup">
		<div id="mapPopupSummary" class="mapPopupSummary"></div>
		<div id="mapPopupCurrentView" class="mapPopupCurrentView">
			<small><?=$this->transEsc("loading data")?> ... </small>
			<?=$this->helper->loader2("loading data")?>
		</div>
		<div id="mapPopupCurrentPlace" class="mapPopupCurrentPlace"></div>
		<div id="mapLastAction" class="text-center" style="display:none;">
			
			<hr><small>The part below will disappear when I`m done with it</small><br/>
			N: <input id="mapBoundN"><br/>
			S: <input id="mapBoundS"><br/>
			E: <input id="mapBoundE"><br/>
			W: <input id="mapBoundW"><br/>
			ZS: <input id="mapStartZoom"><br/>
			Z: <input id="mapZoom"><br/>
			<button class="btn btn-success" type="button" OnClick="results.maps.moved('<?=$this->facetsCode?>');"><i class="ph-bold ph-map-pin"></i> Reload</button>
		</div>
	</div>
</div>