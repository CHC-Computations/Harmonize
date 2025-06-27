<div class="facets-header">
<span type="button" class="ico-btn" id="slideoutbtn" onclick="facets.SlideOut(); " title="<?= $this->transEsc('Hide facet panel') ?>"><i class="ph-caret-left"></i></span>

<span type="button" class="ico-btn" id="collapse_all_button" onclick="$('.collapse'+'.sidefl').collapse('hide'); " title="<?= $this->transEsc('Collapse all') ?>"><i class="ph ph-caret-double-up"></i></span>
<span type="button" class="ico-btn" id="uncollapse_all_button" onclick="$('.collapse'+'.sidefl').collapse('show'); " title="<?= $this->transEsc('Show all') ?>"><i class="ph ph-caret-double-down"></i></span>

<h4><?= $this->transEsc('Narrow Search') ?></h4>
</div>


 
<?php 

if (!empty($this->buffer->usedFacets)) 
	echo $this->render('wikiResults/facets-active.php', ['activeFacets' => $this->buffer->usedFacets, 'currentCore' => $currentCore ] );
		

foreach ($this->configJson->$currentCore->facets->facetsMenu as $facet) {
	
	$stepSetting = clone $this->configJson->$currentCore->facets->defaults;
	if (!empty($facet->template))
		$stepSetting->template = $facet->template;
	if (!empty($facet->translated))
		$stepSetting->translated = $facet->translated;
	if (!empty($facet->formatter))
		$stepSetting->formatter = $facet->formatter;
	if (!empty($facet->child))
		$stepSetting->child = $facet->child;
				
	switch ($stepSetting->template) {
		case 'box' :
				if (!empty($facets[$facet->solr_index]))
					echo $this->render('wikiResults/facet-box.php', [
							'facet'		 => $facet,
							'facets'	 => $facets[$facet->solr_index],
							'stepSetting' => $stepSetting,
							'currentCore' => $currentCore
							] );
				break;			
		case 'timeGraph' :
				if (!empty($facets[$facet->solr_index]) && is_array($facets[$facet->solr_index]) && (count($facets[$facet->solr_index])>0)) {
					ksort($facets[$facet->solr_index]);
					echo $this->render('wikiResults/facet-years-box.php', [
							'facet' 	=> $facet->solr_index, 
							'facetName' => $facet->name, 
							'facets' => $facets[$facet->solr_index],
							'currFacet' => $facet->solr_index,
							'currentCore' => $currentCore
							] );
					}
				break;	
		}

	}


#echo $this->helper->pre($facets);

?>

	
