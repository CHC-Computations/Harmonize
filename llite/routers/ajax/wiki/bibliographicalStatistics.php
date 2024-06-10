<?php
require_once('functions/class.wikidata.php');
$this->addClass('solr', 	new solr($this));  
$this->addClass('buffer', 	new buffer()); 
$this->addClass('wiki', 	new wikidata($this)); 


$wikiq = $this->routeParam[0];
$recType = $this->routeParam[1];
$recCore = $recType.'s';

$graphDefaultRange = 6;

$statBoxes = $this->configJson->$recCore->statBoxes ?? new stdClass;

$query['q'] = [
		'field' => 'q',
		'value' => '*:*'
		];
$query['fq'] = [
		'field' => 'fq',
		'value' => 'all_wiki:"'.$wikiq.'"'
		];
		
$query['facet']=[ 
		'field' => 'facet',
		'value' => 'true'
		];
$query['facet.limit']=[ 
		'field' => 'facet.limit',
		'value' => 9999 // $statBoxes->maxResultsOnGraphs
		];
$query['facet.mincount ']=[ 
		'field' => 'facet.mincount',
		'value' => 1
		];
if (!empty($statBoxes->graphs))
	foreach ($statBoxes->graphs as $statField) {
		if (!empty($statField->graphMode) && ($statField->graphMode == 'timeGraph'))
			$query['facet.limit.'.$statField->indexField]=[ 
					'field' => 'f.'.$statField->indexField.'.facet.limit', // keeping offset only on first field
					'value' => 9999
					];
			$query[]=[ 
					'field' => 'facet.field',
					'value' => $statField->indexField
					];
			}
	 
$query['rows']=[ 
		'field' => 'rows',
		'value' => 0
		];
$query[]=[ 
		'field' => 'start',
		'value' => 0
		];		

$this->solr->getQuery('biblio', $query); 
$results = $this->solr->resultsList();
$stat = $this->solr->facetsList();	


$statStr = '';
if (!empty($stat)) {
	#$facetCode = $this->buffer->createFacetsCode(["persons_wiki_str_mv:\"{$this->wiki->getID()}\""]);
	
	$statStr = '<h4>'.$this->transEsc('Summary for all the roles in which the viewed person appears in the bibliography').'.</h4>';
	$statStr .= '<div class="statBox-extended">';
	$Llp = 100;
	foreach ($statBoxes->graphs as $statField) {
		$nstat = [];
		$lp = 0;
		
		$Llp = $Llp+$lp;
		$graphName = $this->configJson->biblio->facets->solrIndexes->{$statField->indexField}->name ?? 'undefined ? ';
		switch ($statField->graphMode) {
			default: 
				if (!empty($stat[$statField->indexField])) {
					foreach ($stat[$statField->indexField] as $k=>$v) {
						$lp++;
						$index = $lp+$Llp;

						$key = $this->buffer->createFacetsCode(["{$statField->indexField}:\"$k\"", "all_wiki:\"{$this->wiki->getID()}\""]);
						$link =$this->buildUri('results', ['core'=>'biblio', 'facetsCode'=>$key] );
						
						$nstat[$index] = [
							'uid' => uniqid(),
							'label' => $this->helper->convert($statField->indexField,$k),
							'label_o' => $k,
							'count' => $v,
							'link' 	=> $link,
							'color' => $this->helper->getGraphColor($lp),
							'index' => $index,
							];
						}
					#$statStr .= $this->helper->drawStatBoxAdvaced($this->transEsc($graphName), $nstat);
					$max = count($stat[$statField->indexField]);
					$lines = [];
					$showOptions = ($graphDefaultRange<$max) ? $graphDefaultRange : $max;
					
					$forSearch = (object) [
							'wikiq' => $wikiq,
							'options' => $stat[$statField->indexField]
							];
					$statStr .= '
						<form class="form">
						<input type="hidden" id="stat_graphMode_'.$statField->indexField.'" value="'.$statField->graphMode.'">
						<input type="hidden" id="stat_listOf_'.$statField->indexField.'" value="'.base64_encode(json_encode($forSearch)).'">
		
						<div class="il-panel">
							<div class="il-panel-header">
								<div class="col1">
									<h4>'.$this->transEsc($graphName).'</h4>
									<div class="form-group has-feedback">
										<input type="text" class="form-control" id="stat_input_lookfor_'.$statField->indexField.'" placeHolder="'.$this->transEsc('search in options').'">
										<span class="glyphicon glyphicon-search form-control-feedback"></span>
									</div>
								</div>
								<div class="col2">
									<label>'.$this->transEsc('Options shown on chart').': 
										<input type="number" class="il-panel-number" min="1" max="'.$max.'" id="stat_graph_number_'.$statField->indexField.'" value="'.$showOptions.'" oninput="$(\'#stat_graph_range_'.$statField->indexField.'\').val(this.value)">
										/<strong title="'.$this->transEsc('max avaible').'">'.$max.'</strong>
									</label>
									<input type="range" min="1" max="'.$max.'" value="'.$showOptions.'" id="stat_graph_range_'.$statField->indexField.'" oninput="$(\'#stat_graph_number_'.$statField->indexField.'\').val(this.value)">
								</div>	
							</div>
							<div class="il-panel-search" id="ajax_stat_'.$statField->indexField.'">
								'.$this->helper->drawStatBoxAdvaced($nstat, $showOptions).'
							</div>
						</div>
						</form>
						'; 
					$this->addJS("stat.lookfor('{$statField->indexField}');");
					}
				break;
			case 'timeLine' :
				$statStr .= $this->helper->drawTimeLineGraph($this->transEsc($graphName), $statField->indexField, $stat[$statField->indexField]);
				break;
			}
		}
	$statStr .="</div>";
	} else {
	$statStr = $this->transEsc('nothing to show here').'.';
	}
echo $statStr;
# echo '<br/><br/>';
# echo $this->helper->pre($stat);

?>
