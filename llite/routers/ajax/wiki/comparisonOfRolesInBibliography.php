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


######################################################################################################################################################################################################################################
##
##    2-th step : take data for each role
##
######################################################################################################################################################################################################################################
if (!empty($allRoles['with_roles_wiki'])) {
	$totalRoles = count($allRoles['with_roles_wiki']);
	echo '
		<br/>
		<p><strong>'.$this->wiki->get('labels').'</strong> '.$this->transEsc('appears in roles').':</p>
		';
	$blockClass = 'col-sm-4';
	if ($totalRoles==4)
		$blockClass = 'col-sm-3';
	
	$statBoxes = $this->configJson->$recCore->statBoxes ?? new stdClass;
	foreach ($allRoles['with_roles_wiki'] as $withRoleSearchString => $count) {
		
		$baseCondition = 'with_roles_wiki:"'.$withRoleSearchString.'"';
		
		$query = [];
		$query['q'] = [
				'field' => 'q',
				'value' => '*:*'
				];
		$query['fq'] = [
				'field' => 'fq',
				'value' => $baseCondition
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

								$key = $this->buffer->createFacetsCode(["{$statField->indexField}:\"$k\"", $baseCondition]);
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
						if (!empty($stat[$statField->indexField])) {
							$statOption[$statField->indexField] = 'noSearchBar';
							$statStr .= $this->helper->drawTimeLineGraph('', $statField->indexField, $stat[$statField->indexField], $baseCondition);
							}
						break;
					}
				$statStr .="</div>";
				
				if (!empty($statStr))
					$statStrRow[$withRoleSearchString][$statField->indexField] = $statStr;
				$statRow[$statField->indexField][$withRoleSearchString] = $stat[$statField->indexField] ?? [];
				
				}
			
			} else {
			# $statStrRow[$withRoleSearchString] = $this->transEsc('not found in the bibliography').'.';
			}
		}
		
	echo '<div class="statComparsion">';
	echo '<div class="row statComparsion-Header" id="statComparsionHeader">';
	foreach ($allRoles['with_roles_wiki'] as $withRoleSearchString => $count) {
		echo '<div class="'.$blockClass.'">';
		$roleStr = str_replace($prefix, '', $withRoleSearchString);
		echo '<h4 class="text-center">'.$this->transEsc($roleStr).' <span class="badge">'.$this->helper->numberFormat($count).'</span></h4>';
		echo '</div>';
		}		
	echo '</div>';	
	
	echo "<script>
			$(document).ready(function () {
				const targetDiv = $('#statComparsionHeader');
				const otherDiv = $('#fixedHeaders'); 
				
				$('#fixedHeaders').html('<div class=\"row statComparsion-Header-fixed \" id=\"statComparsionHeaderFixed\">' + targetDiv.html() + '</div>');
				
				
				function checkVisibility() {
					const targetOffset = targetDiv.offset();
					const targetHeight = targetDiv.outerHeight();
					const windowScrollTop = $(window).scrollTop();
					const windowHeight = $(window).height();

					const isAboveViewport = targetOffset.top + targetHeight < windowScrollTop;
					const isBelowViewport = targetOffset.top > windowScrollTop + windowHeight;

					if (isAboveViewport || isBelowViewport) {
						otherDiv.removeClass('hidden');
					} else {
						otherDiv.addClass('hidden');
					}
				}

				$(window).on('scroll resize', checkVisibility);
				checkVisibility();
				
			});
			</script>
			";
	
	
	if (!empty($statBoxes->graphs) && (is_array($statBoxes->graphs) or is_object($statBoxes->graphs)))
		
		foreach ($statBoxes->graphs as $statField) {
			$blocksToShow = 0;
			foreach ($allRoles['with_roles_wiki'] as $withRoleSearchString => $count) {
				if (!empty($statRow[$statField->indexField][$withRoleSearchString])){
					$blocksToShow++;
					}
				}
			
			if ($blocksToShow>0) {
				$graphName = $this->configJson->biblio->facets->solrIndexes->{$statField->indexField}->name ?? 'undefined ? ';
				echo '<form name="" id="">';
				echo '<div class="row statComparsion-rowHeader">';
				echo '<div class="'.$blockClass.'">';
				echo '<h4>'.$this->transEsc($graphName).'</h4>';
				echo '</div>';
				if (empty($statOption[$statField->indexField]) or ($statOption[$statField->indexField] !== 'noSearchBar')) {
					echo '<div class="'.$blockClass.'">';
					echo '
						<div class="form-group has-feedback">
							<input type="text" class="form-control" value="" placeHolder="'.$this->transEsc('search in').' '.strtolower($this->transEsc($graphName)).'" id="comp_input_lookfor_'.$statField->indexField.'">
							<span class="glyphicon glyphicon-search form-control-feedback"></span>
						</div>
						';
					echo '</div>';
					echo '<div class="'.$blockClass.'">';

					$max = 0;
					foreach ($statRow[$statField->indexField] as $stat) {
						$max = ($max<count($stat)) ? count($stat) : $max;
						}
					
					$uid = uniqid();
					$showOptions = ($graphDefaultRange<$max) ? $graphDefaultRange : $max;
					echo $this->transEsc('Show options on chart').': <b id="str_'.$uid.'">'.$showOptions.'</b><br/>';
					echo '<input type="range" min="1" max="'.$max.'" value="'.$graphDefaultRange.'" oninput="$(\'#str_'.$uid.'\').html(this.value);"  id="comp_input_range_'.$statField->indexField.'">';
					echo '</div>';
					}
				echo '</div>';
				
				echo '<input type="hidden" id="comp_graphMode_'.$statField->indexField.'" value="'.$statField->graphMode.'">';
				echo '<input type="hidden" id="comp_listOf_'.$statField->indexField.'" value="'.base64_encode(json_encode($statRow[$statField->indexField])).'">';
				
				echo '<div class="row" id="ajax_comparsion_'.$statField->indexField.'">';
				foreach ($allRoles['with_roles_wiki'] as $withRoleSearchString => $count) {
					echo '<div class="'.$blockClass.'">';
					echo $statStrRow[$withRoleSearchString][$statField->indexField];
					echo '</div>';
					}	
				echo '</div>';
				# echo '<div class="row" id="ajax_comparsion_'.$statField->indexField.'"></div>';
				echo '</form>';
				if ($statField->indexField != 'publishDate')
					$this->addJS("stat.comparsion('{$statField->indexField}');");
				}
			}
	echo '</div>';
	echo '<br/><br/>';
	# echo $this->helper->pre($stat);
	}
?>
