<?php
require_once('functions/class.wikidata.php');
$this->addClass('solr', 	new solr($this));  
$this->addClass('buffer', 	new buffer()); 
$this->addClass('wiki', 	new wikidata($this)); 


$wikiq = $this->routeParam[0];
$recType = $this->routeParam[1];
$recCore = $recType.'s';

$graphDefaultRange = 6;


######################################################################################################################################################################################################################################
##
##    first step : take all roles aviable 
##
######################################################################################################################################################################################################################################
$this->wiki->loadRecord($wikiq, false);
$prefix =  $wikiq.'|';	
$query = [];
$query['q'] 			= ['field' => 'q',				'value' => '*:*' ];
$query['facet'] 		= ['field' => 'facet',			'value' => 'true'];
$query['facet.field'] 	= ['field' => 'facet.field',	'value' => 'with_roles_wiki'];		
$query['facet.limit']	= ['field' => 'facet.limit',	'value' => 9999 ];
$query['facet.prefix']	= ['field' => 'facet.prefix', 	'value' => $prefix ];
$this->solr->getQuery('biblio', $query); 
$results = $this->solr->resultsList();
$allRoles = $this->solr->facetsList();	


echo '
	<br/>
	<p><strong>'.$this->wiki->get('labels').'</strong> '.$this->transEsc('appears in roles').':</p>
	';
	
######################################################################################################################################################################################################################################
##
##    2-th step : take data for each role
##
######################################################################################################################################################################################################################################
if (!empty($allRoles['with_roles_wiki'])) {
	$statBoxes = $this->configJson->$recCore->statBoxes ?? new stdClass;
	foreach ($allRoles['with_roles_wiki'] as $withRoleSearchString => $count) {
		
		$query = [];
		$query['q'] = [
				'field' => 'q',
				'value' => '*:*'
				];
		$query['fq'] = [
				'field' => 'fq',
				'value' => 'with_roles_wiki:"'.$withRoleSearchString.'"'
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
			
			$Llp = 100;
			foreach ($statBoxes->graphs as $statField) {
				$nstat = [];
				$lp = 0;
				$statStr = '';
				$statStr .= '<div class="statBox-comparsion">';
			
				$Llp = $Llp+$lp;
				$graphName = $this->configJson->biblio->facets->solrIndexes->{$statField->indexField}->name ?? 'undefined ? ';
				switch ($statField->graphMode) {
					default: 
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
						$statStr .= $this->helper->drawStatBoxComparison($this->transEsc($graphName), $nstat);
						break;
					case 'timeLine' :
						if (!empty($stat[$statField->indexField]))
							$statStr .= $this->helper->drawTimeLineGraph($this->transEsc($graphName), $statField->indexField, $stat[$statField->indexField]);
						break;
					}
				$statStr .="</div>";
				$statStrRow[$withRoleSearchString][$statField->indexField] = $statStr;
				$statRow[$statField->indexField][$withRoleSearchString] = $stat[$statField->indexField] ?? [];
				
				}
			
			} else {
			$statStrRow[$withRoleSearchString] = $this->transEsc('not found in the bibliography').'.';
			}
		}
		
	echo '<div class="statComparsion">';
	echo '<div class="row statComparsion-Header">';
	foreach ($allRoles['with_roles_wiki'] as $withRoleSearchString => $count) {
		echo '<div class="col-sm-4">';
		$roleStr = str_replace($prefix, '', $withRoleSearchString);
		echo '<h4 class="text-center">'.$this->transEsc($roleStr).' <span class="badge">'.$this->helper->numberFormat($count).'</span></h4>';
		echo '</div>';
		
		}		
	echo '</div>';	
	
	foreach ($statBoxes->graphs as $statField) {
		$graphName = $this->configJson->biblio->facets->solrIndexes->{$statField->indexField}->name ?? 'undefined ? ';
		echo '<form name="" id="">';
		echo '<div class="row statComparsion-rowHeader">';
		echo '<div class="col-sm-4">';
		echo '<h4>'.$this->transEsc($graphName).'</h4>';
		echo '</div>';
		echo '<div class="col-sm-4">';
		echo '
			<div class="form-group has-feedback">
				<input type="text" class="form-control" placeHolder="'.$this->transEsc('search in').' '.strtolower($this->transEsc($graphName)).'" id="comp_input_lookfor_'.$statField->indexField.'">
				<span class="glyphicon glyphicon-search form-control-feedback"></span>
			</div>
			';
		echo '</div>';
		echo '<div class="col-sm-4">';

		$max = 0;
		foreach ($statRow[$statField->indexField] as $stat) {
			$max = ($max<count($stat)) ? count($stat) : $max;
			}
		
		$uid = uniqid();
		$showOptions = ($graphDefaultRange<$max) ? $graphDefaultRange : $max;
		echo $this->transEsc('Show options on chart').': <b id="str_'.$uid.'">'.$showOptions.'</b><br/>';
		echo '<input type="range" min="1" max="'.$max.'" value="'.$graphDefaultRange.'" oninput="$(\'#str_'.$uid.'\').html(this.value);"  id="comp_input_range_'.$statField->indexField.'">';
		echo '</div>';
		echo '</div>';
		
		echo '<input type="hidden" id="comp_graphMode_'.$statField->indexField.'" value="'.$statField->graphMode.'">';
		echo '<input type="hidden" id="comp_listOf_'.$statField->indexField.'" value="'.base64_encode(json_encode($statRow[$statField->indexField])).'">';
		
		echo '<div class="row" id="ajax_comparsion_'.$statField->indexField.'">';
		foreach ($allRoles['with_roles_wiki'] as $withRoleSearchString => $count) {
			echo '<div class="col-sm-4">';
			echo $statStrRow[$withRoleSearchString][$statField->indexField];
			echo '</div>';
			}	
		echo '</div>';
		# echo '<div class="row" id="ajax_comparsion_'.$statField->indexField.'"></div>';
		echo '</form>';
		$this->addJS("stat.comparsion('{$statField->indexField}');");
		}
	echo '</div>';
	# echo '<br/><br/>';
	# echo $this->helper->pre($stat);
	}
?>
