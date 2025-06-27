<?php
require_once('functions/class.buffer.php');
require_once('functions/class.wikidata.php');
require_once('functions/class.wikidata.libri.php');

$this->addClass('solr', 	new solr($this)); 
$this->addClass('buffer', 	new buffer()); 
$this->addClass('wiki', 	new wikidata($this)); 

if (empty($this->POST))
	$_SESSION['relationGraph'] = [];

$currentCore = $this->routeParam[1];
$wikiq = $this->routeParam[0];
$this->addJS('$("#related2this").css("opacity", "1");');
$this->wiki->loadRecord($wikiq, false);
$prefix =  $wikiq.'|';


	######################################################################################################################################################################################################################################
	##
	##    first dimension
	##
	######################################################################################################################################################################################################################################
	
$query = [];
$query['q'] 			= ['field' => 'q',				'value' => '*:*' ];
$query['facet'] 		= ['field' => 'facet',			'value' => 'true'];
$query['facet.field'] 	= ['field' => 'facet.field',	'value' => 'with_roles_wiki'];		
$query['facet.limit']	= ['field' => 'facet.limit',	'value' => 9999 ];
$query['facet.prefix']	= ['field' => 'facet.prefix', 	'value' => $prefix ];
$this->solr->getQuery('biblio', $query); 
$results = $this->solr->resultsList();
$allRoles = $this->solr->facetsList();	

$baseConditions = [];

if (!empty($allRoles['with_roles_wiki'])) {
	$objectRolesStr = '<div><strong>'.$this->wiki->get('labels').'</strong> '.$this->transEsc('appears in roles').':<br/><div class="list-group">';
	
	if (empty($_SESSION['relationGraph']['role']))
		foreach ($allRoles['with_roles_wiki'] as $role=>$count) 
			$_SESSION['relationGraph']['role'][$role] = true;
	$useForSearch = [];
	foreach ($allRoles['with_roles_wiki'] as $role=>$count) {
		$roleStr = str_replace($prefix, '', $role);
		
		if (!empty($this->POST['pdata']['field']) && ($this->POST['pdata']['field'] == $role))
			$_SESSION['relationGraph']['role'][$role] = !$_SESSION['relationGraph']['role'][$role];
		if ($_SESSION['relationGraph']['role'][$role]) {
			$class = 'active';
			$useForSearch[] = $role;
			$baseConditions[] = '~with_roles_wiki:"'.$role.'"';
			} else 
			$class = '';
		$action = (object)[
			'field' => $role,
			'action' => 'change'
			];
			
		$objectRolesStr.='
			<button 
				class="list-group-item '.$class.'" 
				type="button" 
				onClick=\'page.postLT("related2this", "wiki/related/'.$this->wiki->getID().'/'.$currentCore.'", '.json_encode($action).');\' 
				title="'.$this->transEsc('Click to disable').'"> 
					'.$this->transEsc($roleStr).' <span class="badge">'.$this->helper->numberFormat($count).'</span>
			</button>
			';
		}
	$objectRolesStr.='</div></div>';
	
	 

	######################################################################################################################################################################################################################################
	##
	##    2-th dimension
	##
	######################################################################################################################################################################################################################################
	
	$statBoxes = $this->configJson->$currentCore->statBoxes ?? new stdClass;
	$query = [];
	$query['q'] = [
			'field' => 'q',
			'value' => '*:*'
			];
	$query['fq'] = [
			'field' => 'fq',
			'value' => 'with_roles_wiki:"'.implode('" OR with_roles_wiki:"', $useForSearch).'"'
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
	$query[]=[ 
			'field' => 'facet.field',
			'value' => 'with_roles_wiki'
			];
	$query['rows']=[ 
			'field' => 'rows',
			'value' => 0
			];
	$query[]=[ 
			'field' => 'start',
			'value' => 0
			];		

	$results = $this->solr->getQuery('biblio', $query); 
	#echo implode('<br>',$this->solr->alert);
	$results = $this->solr->resultsList();
	$facets = $this->solr->facetsList();		
	#echo $this->helper->pre($query);	
	
	$conversionTable = [
		'subjectPerson' => 'Persons',
		'mainAuthor' => 'Persons',
		'coAuthor' => 'Persons',

		'subjectPlace' => 'Places',
		'publicationPlace' => 'Places',
		'eventPlace' => 'Places',	
		
		'subjectEvent' => 'Events',

		'subjectCorporate' => 'Corporates',
		];

	if (!empty($facets['with_roles_wiki']) && is_array($facets['with_roles_wiki']))
		foreach ($facets['with_roles_wiki'] as $resultStr=>$count) {
			$tmp = explode('|', $resultStr);
			$group = $tmp[1];
			$value = $tmp[0];
			$nGroup = $conversionTable[$group] ?? $group;
			@$relationGroups[$nGroup]+=$count;
			
			if ($value !== $wikiq) {
				@$recInGroups[$nGroup][$group] += $count;
				$recInGroupsHasRoles[$nGroup][$value][$group] = $count;
				}
			}

	if (!empty($this->POST['pdata']['group']))
		$_SESSION['relationGraph']['relatedWith'] = $this->POST['pdata']['group'];
		
	if (empty($_SESSION['relationGraph']['relatedWith']))
		$_SESSION['relationGraph']['relatedWith'] = key($relationGroups);

	$activePanel = $_SESSION['relationGraph']['relatedWith'];
	
	$relatedWithStr = $this->transEsc('Show relations with').':<br/>';	
	$relatedWithStr.= '<div class="list-group">';	
	if (!empty($relationGroups) && is_array($relationGroups))
		foreach ($relationGroups as $group=>$countInGroup) {
			
			$class = ($group == $activePanel) ? 'active' : '';
			$action = (object)['group' => $group];
			$relatedWithStr.= '
				<button class="list-group-item '.$class.'" 
					onClick=\'page.postLT("related2this", "wiki/related/'.$this->wiki->getID().'/'.$currentCore.'", '.json_encode($action).');\'>
						'.$this->transEsc($group).' <span class="badge">'.$this->helper->numberFormat($countInGroup).'</span>
				</button> ';
			}
	$relatedWithStr.= '</div>';		

	
	
	######################################################################################################################################################################################################################################
	##
	##    3-th dimension
	##
	######################################################################################################################################################################################################################################
		
	if (!empty($_SESSION['relationGraph']['relatedWith'])) {
		$currentGroup = $_SESSION['relationGraph']['relatedWith'];
		$relatedWithRolesStr = '<strong>'.$currentGroup.'</strong> '.$this->transEsc('appears in roles:').'<br/>';
		$relatedWithRolesStr.= '<div class="list-group">';
		if (!empty($recInGroups[$currentGroup])) {
			
			if (empty($_SESSION['relationGraph']['subInRole'][$currentGroup]))
				foreach ($recInGroups[$currentGroup] as $role => $countInRole) 
					$_SESSION['relationGraph']['subInRole'][$currentGroup][$role] = true;
			
			foreach ($recInGroups[$currentGroup] as $role => $countInRole) {
				if (!empty($this->POST['pdata']['subInRole']) && ($this->POST['pdata']['subInRole'] == $role))
					$_SESSION['relationGraph']['subInRole'][$currentGroup][$role] = !$_SESSION['relationGraph']['subInRole'][$currentGroup][$role];
				if (!empty($_SESSION['relationGraph']['subInRole'][$currentGroup][$role]) && $_SESSION['relationGraph']['subInRole'][$currentGroup][$role]) {
					$class = 'active';
					$useForDisplay[] = $role;
					} else 
					$class = '';
				$action = (object)[
					'subInRole' => $role,
					];
				
				$relatedWithRolesStr.= '
					<button class="list-group-item '.$class.'" 
						onClick=\'page.postLT("related2this", "wiki/related/'.$this->wiki->getID().'/'.$currentCore.'", '.json_encode($action).');\'>
							'.$this->transEsc($role).' <span class="badge">'.$this->helper->numberFormat($countInRole).'</span>
					</button> ';
				}
			}
		$relatedWithRolesStr.='</div>';
		
		} else {
		$relatedWithRolesStr = $this->transEsc('select something from the "show relations with"');
		}


	echo '<br/><p>'.$this->transEsc('Relation were created on the basis of entries in bibliographic records. Number of records in which the '.$this->wiki->get('labels').' was found').': <strong>'.$totalResults = $this->solr->totalResults().'</strong>.</p>';	
	echo '<div class="row">';
	echo '<div class="col-sm-3">';
	echo $objectRolesStr;
	echo '</div>';
	echo '<div class="col-sm-1">';
	echo '</div>';
	echo '<div class="col-sm-3">';
	echo $relatedWithStr;
	echo '</div>';
	echo '<div class="col-sm-1">';
	echo '</div>';
	echo '<div class="col-sm-3">';
	echo $relatedWithRolesStr;
	echo '</div>';
	echo '</div>';
	
	######################################################################################################################################################################################################################################
	##
	##    geting & drawing results
	##
	######################################################################################################################################################################################################################################
	
	if (!empty($currentGroup) && !empty($recInGroupsHasRoles[$currentGroup])) {
		$maxToShow = 18;
			
		$wikiqToShow = [];
		$controlStr = '';
		$i = 0;
		foreach ($recInGroupsHasRoles[$currentGroup] as $resWikiq=>$rolesArray) {
			$showRecord = false;
			$rolesToShow = [];
			foreach ($_SESSION['relationGraph']['subInRole'][$currentGroup] as $role=>$toShow)
				if ($toShow && array_key_exists($role, $rolesArray)) {
					$showRecord = true;
					$countInRole = $rolesArray[$role];
					$rolesToShow["$resWikiq|$role"] = $countInRole;
					}
			
			if ($showRecord) {
				if ($i<=$maxToShow) {
					$wikiqToShow[$resWikiq] = $rolesToShow;
					#$wikiqRolesToShow[$resWikiq] = $rolesToShow;
					$controlStr.= '<h4>'.$resWikiq.'</h4>';
					$controlStr.= implode(' ', $rolesToShow);
					}
				$i++;	
				}
			}
		
		if (count($wikiqToShow)>0) {
			$query = [];
			$query['q'] = [
					'field' 	=> 'q',
					'value' 	=> 'id:('.implode(' OR ',array_keys($wikiqToShow)).')'
					];
			$query['rows'] = [
					'field' 	=> 'rows',
					'value' 	=> count($wikiqToShow)
					];
			$query['start'] = [
					'field' 	=> 'start',
					'value' 	=> 0
					];
			$currentCore = strtolower($currentGroup);
			$currentView = 'default-box';
			
			$results = $this->solr->getQuery($currentCore, $query); 
			$results = $this->solr->resultsList();
			$totalToShow = $i; //$this->solr->totalResults();
			# echo $this->helper->pre($this->solr->alert);
			foreach ($results as $i=>$result) {
				$results[$result->wikiq] = $result;
				unset($results[$i]);
				}
			#echo $this->helper->pre($results);
			if ($totalToShow>$maxToShow)
				echo $this->transEsc('Total results').' <b>'.$totalToShow.'</b> '.$this->transEsc('showing').' <b>'.$maxToShow.'</b> ';
			
			/*
			0=record_contains:"wikidata 1"&
			1=~author_facet:"Suchý, Lothar|1873|1959|4485185|Q12033959|(1873-1959)"&
			2=~author_facet:"Trojan, Josef|1905|1965|306139112|Q12026678|(1905-1965)"
			*/	
				
			echo '<div id="resultsBox" class="showcase-list wiki-related-list ">';
			$i = 0;
			foreach ($wikiqToShow as $wikiq=>$rolesToShow) {
				if (!empty($results[$wikiq])) {
					$i++;
					$result = $results[$wikiq];
					$resultObj = new wikiLibri($this->user->lang['userLang'], $result);
					$rolesToShowLinks = [];
					foreach ($rolesToShow as $withRoleStr=>$countInRole) {
						$stepArray = $baseConditions;
						$stepArray[] = 'with_roles2_wiki:"'.$withRoleStr.'"';
						$key = $this->buffer->createFacetsCode($stepArray);
						$tmp = explode('|',$withRoleStr);
						$role = $tmp[1];
						
						
						$rolesToShowLinks[] = '<a href="'.$this->buildURL('results', ['core'=>'biblio', 'facetsCode'=>$key]).'" >'.$this->transEsc($role).' <span class="badge">'.$countInRole.'</span></a>';
					
						}
					
					echo '<div class="result-box '.$currentCore.'-result " id="'.$currentCore.'_'.$result->wikiq.'">';
					$resultObj->solrRecord->bottomLinks = 
						'<div class="slide-panel">'.
						$this->transEsc('Show bibliograhic record with __label1__ and with __label2__ as', ['label1'=>$this->wiki->get('labels'), 'label2'=>$resultObj->getStr('labels')])
						.'</div>'.
						implode(' ', $rolesToShowLinks);
					echo $this->render('wikiResults/resultBoxes/'.$currentView.'.php',['result'=>$resultObj]);
					echo '</div>';
					} else 
					echo '<div class="hidden">('.$wikiq.')</div>';
				if ($i>=$maxToShow) break;
				}
			echo '</div>';	
			}
		# echo '<hr>'.$controlStr.'<hr>';
		}

	} else {
	echo '<p style="margin:50px;">'.$this->transEsc('There is nothing to show here').'.</p>';
	}

# echo 'post'.$this->helper->pre($_POST);
# echo 'session'.$this->helper->pre($_SESSION['relationGraph']);
# echo 'recInGroups'.$this->helper->pre($recInGroups);


?>
<script>$("#related2this").css("opacity", "1");</script>