<?php 

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
		if (!empty($stat[$statField->indexField]))
			foreach ($stat[$statField->indexField] as $k=>$v) {
				$lp++;
				$index = $lp+$Llp;

				$key = $this->buffer->createFacetsCode(["{$statField->indexField}:\"$k\"", "all_wiki:\"{$this->wiki->getID()}\""]);
				$link =$this->buildUri('results', ['core'=>'biblio', 'facetsCode'=>$key] );
				
				$nstat[$index] = [
					'label' => $this->helper->convert($statField->indexField,$k),
					'label_o' => $k,
					'count' => $v,
					'link' 	=> $link,
					'color' => $this->helper->getGraphColor($lp),
					'index' => $index,
					];
				}
		$Llp = $Llp+$lp;
		$graphName = $this->configJson->biblio->facets->solrIndexes->{$statField->indexField}->name ?? 'undefined ? ';
		$statStr .= $this->helper->drawStatBoxAdvaced($this->transEsc($graphName), $nstat);
		}
	$statStr .="</div>";
	} else {
	$statStr = $this->transEsc('Person not found in the bibliography').'.';
	}
echo $statStr;
echo '<br/><br/>';
echo $this->helper->pre($stat);

?>