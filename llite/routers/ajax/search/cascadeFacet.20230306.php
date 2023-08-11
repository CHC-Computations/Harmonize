<?php
if (empty($this)) die;
$this->facetsCode = $facetCode = $this->routeParam[0];
$facetName = $this->routeParam[1];
$parentBox = $this->routeParam[2];
$cascadeName = $this->routeParam[3];


require_once('functions/klasa.helper.php');

$formattedFacets = $this->getConfigParam('facets', 'formattedFacets');
$transletedFacets = $this->getConfigParam('facets', 'facetOptions', 'transletedFacets');


$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this->config)); 
$this->addClass('buffer', 	new marcBuffer()); 

$facets = $this->getConfig('facets');

$query[] = $this->buffer->getFacets($this->sql, $this->facetsCode);	

if (!empty($this->GET['sj'])) {
	$query[] = [ 
			'field' => 'q',
			'value' => $this->solr->advandedSearch($this->GET['sj'])
			];
	}
$query['q'] = $this->solr->lookFor($lookfor = $this->getParam('GET', 'lookfor'), $type = $this->getParam('GET', 'type') );			

$results = $this->solr->getFacets('biblio', [$cascadeName], $query);

if (is_Array($results)) {
	$lines = [];
	if (!empty($results[$cascadeName]))
		foreach ($results[$cascadeName] as $name=>$count) {
				if ($count>0) {
					$tname = $name;
					if (array_key_exists($cascadeName, $formattedFacets)) {
						$formatter = $formattedFacets[$cascadeName];
						$tname = $this->helper->$formatter($name);
						}
					if (in_array($cascadeName, $transletedFacets))
						$tname = $this->transEsc($tname);
								
					
					$input_values = $this->buffer->addFacet($cascadeName, $name);
					$key = $this->buffer->createFacetsCode($this->sql, $input_values);
					$lines[] = '<a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" class="facet js-facet-item" >
										<span class="text">'.$this->transEsc($tname).'</span>
										<span class="badge">'.$this->helper->numberFormat($count).'</span>
									</a>';
								
					}
					
			}
	if (count($lines)>1) {
		echo '<div class="subfacetCascade">'.implode('', $lines).'</div>';
		echo "
			<script>
				var pos = $('#{$parentBox}_panel').position();
				var pos2 = $('#facetBase{$facetCode}').position();
				var wid = $('#{$parentBox}_panel').width();
				left = pos.left+wid;
				top2 = pos2.top-5;
				
				$('#caret_{$facetCode}').css('color','#888');
				$('#facetLink{$facetCode}').css('top', pos2.top+'px');
				$('#facetLink{$facetCode}').css('left', left+'px');
				$('#facetLink{$facetCode}').width(wid);
				$('#facetLink{$facetCode}').css('opacity', 1);
				
				
			</script>
			";
		 
		}
	}



# echo "TEST: ";
#print_r($this->routeParam);
#print_r($this->GET);



?>