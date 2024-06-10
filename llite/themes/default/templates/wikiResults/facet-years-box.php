<?php 

	$graph = $this->helper->drawTimeGraphAjax($facets);
	
	$top = '
		<div class="text-center">
			<div class="form-horizontal facet-year-range">
				<label for="year_str_mvfrom">'.$this->transEsc('from').':</label><input type="number" class="form-control" step="1" name="year_str_mvfrom" id="year_str_mvfrom" OnChange="snapSlider.noUiSlider.set([$(\'#year_str_mvfrom\').val(), $(\'#year_str_mvto\').val()])"></input>
				<label for="year_str_mvto">'.$this->transEsc('to').':</label><input type="number" class="form-control"  step="1" name="year_str_mvto" id="year_str_mvto" OnChange="snapSlider.noUiSlider.set([$(\'#year_str_mvfrom\').val(), $(\'#year_str_mvto\').val()])"></input>
			</div>
		</div>
		';
	
	$OC = "facets.timeFacetLink('change', [ '{$currFacet}', $('#year_str_mvfrom').val(), $('#year_str_mvto').val() ], '{$this->getUserParam('sort')}', '{$this->facetsCode}', 'persons')";
	$bottom = '
		<div id="recalculateLink">
			<div class="text-center" style="padding-bottom:15px; margin-top:-20px; padding-top:-20px;">
				<button type=button class="btn btn-default" OnClick="'.$OC.'">'.$this->transEsc('Use selected range').'</button>
			</div>
		</div>
		';
	
	$sform = $top.$graph.$bottom;
?>
	
<?= 

$this->helper->PanelCollapse(
					uniqid(),
					$this->transEsc($facetName),
					'</div>'.$sform.'<div>'
					);
?>

