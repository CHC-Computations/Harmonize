
	
<?= $this->helper->drawTimeGraphAjax($facets); ?>

<div class="text-center">

	<div class="form-horizontal">
		<label><?=$this->transEsc('from')?>:<input type="number" class="form-control" step="1" name="year_str_mvfrom" id="year_str_mvfrom" OnChange="snapSlider.noUiSlider.set([$('#year_str_mvfrom').val(), $('#year_str_mvto').val()])"></input></label>
		<label><?=$this->transEsc('to')?>:<input type="number" class="form-control"  step="1" name="year_str_mvto" id="year_str_mvto" OnChange="snapSlider.noUiSlider.set([$('#year_str_mvfrom').val(), $('#year_str_mvto').val()])"></input></label>
		<button type=button class="btn btn-default" OnClick="advancedSearch.AddRemove('change', [ $('#year_str_mvfrom').val(), $('#year_str_mvto').val() ], '<?= $currFacet?>', 'range')"><?=$this->transEsc('Use selected range')?></button>
	</div>
</div>
	 