<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');

$this->addJS('$("#facetsBox").css("opacity","1"); ');

$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this)); 
$this->addClass('buffer', 	new buffer()); 


$facets = $this->getConfig('facets');



$content = '';

foreach ($facets['facetList'] as $currFacet=>$v) {
	$facetName = $facets['facetList'][$currFacet];
	if ( !in_array($currFacet, $facets['cascade']))
	if ( array_key_exists($currFacet, $facets['facetDate']) ) {
			############################################################################################
			##					YEARS panel
			############################################################################################
			$query = [];
			# $query[] = $this->solr->lookFor($lookfor = $this->getParam('GET', 'lookfor'), $type = $this->getParam('GET', 'type') );		
			/*
			if (!empty($_SESSION['advSearch']['json']))
				$query[] = [ 
					'field' => 'q',
					'value' => $this->solr->advandedSearch($_SESSION['advSearch']['json'])
					];
			*/
			$results = $this->solr->getCleanedYears('biblio', [$currFacet], $query);
			$sform = $this->render('search/advanced/facet-years-box.php', [
						'facet' => $k, 
						'facetName' => $facets['facetList'][$currFacet], 
						'facets' => $results[$currFacet],
						'currFacet' => $currFacet,
						] );
			$content.= $this->helper->PanelCollapse(
					uniqid(),
					$this->transEsc($facetName),
					$sform,
					'',
					false
					); 
			
			} else {
			############################################################################################
			##					"normal" facets
			############################################################################################
				
			$cascadeRes = [];
			#$currFacet = $k;
			$RF = "advancedSearch.fSearch('".$currFacet."');";
			$sform = '
					<form class="form-horizontal">
					  <div class="form-group has-feedback">
						<div class="col-sm-6">
						  <input type="text" class="form-control" id="ajaxSearchInput_'.$currFacet.'" name="search" placeholder="'.$this->transEsc('Search') .'" onkeyup="'.$RF.'">
						  <span class="glyphicon glyphicon-search form-control-feedback"></span>
						</div>
						<div class="col-sm-6">
						  '.$this->transEsc('Sort').':
						  <label><input type=radio name="facetsort'.$currFacet.'" id="facetsort_c" value="count" checked OnChange="'.$RF.'"> '. $this->transEsc('Result count') .'</label>
						  <label><input type=radio name="facetsort'.$currFacet.'" id="facetsort_i" value="index" OnChange="'.$RF.'"> '. $this->transEsc('Alphabetical') .'</label>
						  
						</div>
					  </div>

					</form> 

					<div id="ajaxSearchBox_'.$currFacet.'">
						<div class="loader"></div>
					</div>
					
					';			
			$this->addJS("advancedSearch.fSearch('".$currFacet."');");			
			$content.= $this->helper->PanelCollapse(
					uniqid(),
					$this->transEsc($facetName),
					$sform,
					'',
					false
					); 
			
			}
			
	}

/*
$results_number = number_format($this->solr->totalResults(),0,'','.'); 	
echo $results_number;
$this->addJS("$('#results_number').html('$results_number');");
*/

echo $this->helper->panelCollapse(
					'limitTo',
					'<b>'.$this->transEsc('Limit to').'</b>',
					$content
					);

# echo "GET<pre>".print_r($this->GET,1).'</pre>';
# echo "params<pre>".print_r($this->routeParam,1).'</pre>';
# echo "results<pre>".print_r($results,1).'</pre>';
# echo "facets.ini<pre>".print_r($facets,1).'</pre>';

?>