<?php
require_once('functions/class.buffer.php');
require_once('functions/class.wikidata.php');
require_once('functions/class.wikidata.libri.php');

$this->addClass('solr', 	new solr($this)); 
$this->addClass('buffer', 	new buffer()); 
$this->addClass('wiki', 	new wikidata($this)); 


$currentCore = 'persons';
$currentView = 'default-box';

$wikiq = $activePersonId = $this->routeParam[0];
$recType = $this->routeParam[1];

$this->addJS('$("#related2this").css("opacity", "1");');
$this->wiki->loadRecord($wikiq, false);
$prefix =  $wikiq.'|';
$personsToTake[] = $wikiq;
$photo = '';


switch ($recType) {
	case 'person' : 
			$relationToTake = [
				'subjectPerson' => [
						'wiki' => 'mainAuthor',
						'all' => 'author'
						],
				'mainAuthor' => [
						'wiki' => 'subjectPerson',
						'all' => 'subject_person_str_mv'
						],
				];
	
			# echo '<div class="row">';
			foreach ($relationToTake as $itemRole=>$relationRole) {
				$query = [];
				$query['q'] 			= ['field' => 'q',				'value' => '*:*' ];
				$query['rows'] 			= ['field' => 'rows',			'value' => '0' ];
				$query['fq'] 			= ['field' => 'fq',				'value' => 'with_roles_wiki:"'.$prefix.$itemRole.'"'];
				$query['facet'] 		= ['field' => 'facet',			'value' => 'true'];
				$query[] 				= ['field' => 'facet.field',	'value' => 'with_roles_wiki'];		
				$query[] 				= ['field' => 'facet.field',	'value' => $relationRole['all']];		
				$query['facet.limit']	= ['field' => 'facet.limit',	'value' => 99 ];
				$query['facet.mincount']	= ['field' => 'facet.mincount', 	'value' => 1 ];
				$query['facet.contains']	= ['field' => 'f.with_roles_wiki.facet.contains', 	'value' => '|'.$relationRole['wiki'] ];
				$this->solr->getQuery('biblio', $query); 
				$results = $this->solr->resultsList();
				$allRoles = $this->solr->facetsList();	
				$i = 0;
				if (!empty($allRoles['with_roles_wiki']))
					foreach ($allRoles['with_roles_wiki'] as $personStr => $count) {
						$personWikiId = explode('|', $personStr)[0];
						$personsToTake[] = $personWikiId;
						$personsToShow[$itemRole][$personWikiId] = $count;
						}
				if (!empty($allRoles[$relationRole['all']]))
					foreach ($allRoles[$relationRole['all']] as $personStr => $count) {
						$personsNoWiki[$itemRole][$personStr] = $count;
						}
				
				# echo '<div class="col-sm-6">';
				# echo $this->helper->pre($allRoles);
				# echo '</div>';
				}
			# echo '</div>';

			if (!empty($personsToTake)) {
				$personsToTake = array_unique($personsToTake);
				$query = [];			
				$query['q'] = [
						'field' 	=> 'q',
						'value' 	=> 'id:('.implode(' OR ',$personsToTake).')'
						];
				$query['rows'] = [
						'field' 	=> 'rows',
						'value' 	=> count($personsToTake)
						];
				$query['start'] = [
						'field' 	=> 'start',
						'value' 	=> 0
						];
							
				$results = $this->solr->getQuery('persons', $query); 
				$results = $this->solr->resultsList();
				
				foreach ($results as $result) {
					$resultObj = new wikiLibri($this->user->lang['userLang'], $result);
					$readyItems[$result->id] = $resultObj;
					$resultBiblioLabels = json_decode($resultObj->solrRecord->biblio_labels[0], true);
					
					if (($result->id == $activePersonId) & !empty($resultObj->solrRecord->picture)) {
						$photos = (array)$resultObj->solrRecord->picture;
						$photo = current($photos);
						}
					
					foreach ($resultBiblioLabels as $label=>$labelCount) {
						$clearLabel = $this->helper->clearLatin($label);
						foreach ($personsNoWiki as $role=>$noWikiList)
							foreach ($noWikiList as $personStr=>$count) {
								if ($this->helper->clearLatin($personStr) == $clearLabel) {
									#echo 'usuwam: '.$personStr.'  == '.$result->id.'<br/>';
									unset($personsNoWiki[$role][$personStr]);
									}
								}
						}
					}
				}
				
			// $personsToShow[$itemRole][$personWikiId] = $count;	
			// $personsNoWiki[$itemRole][$personStr] = $count;
			if (empty($photo)) 
				$centerImage = '<div class="relGraph-centerImage-empty" id="centerImage"><span>'.$this->wiki->get('labels').'</span></div>';
				else 
				$centerImage = '<div class="relGraph-centerImage" id="centerImage" style="background-image: url('.$photo.')"><span>'.$this->wiki->get('labels').'</span></div>';
			
			$maxBoxes = 4;
			
			echo '<div class="showcase-list wiki-related-list ">';
			echo '<div id="ropesBlock"></div>';
			echo '<br/><div class="relGraph">';
			echo '<div class="relGraph-cell">';
			if (!empty($personsToShow['subjectPerson'])) {
				$code = $this->buffer->createFacetsCode(["with_roles_wiki:\"{$prefix}subjectPerson\""]);
				echo '<a href="'.$this->buildUri('results', ['core'=>'biblio', 'facetsCode'=>$code]).'" title="'.$this->transEsc('Go to bibliographic records').'"><b>'.$this->wiki->get('labels').' '.$this->transEsc('was the subject of publications in which the main author was').':</b></a><br/>';
				$i = 0;
				foreach ($personsToShow['subjectPerson'] as $wikiQ=>$count) {
					# echo $activePersonId.' ?? '.$wikiQ.' '.$count.'<br/>';
					if (!empty($readyItems[$wikiQ]) & ($wikiQ != $activePersonId)) {
						$i++;
						$resultObj = $readyItems[$wikiQ];
						$goToRecCode = $this->buffer->createFacetsCode(["with_roles_wiki:\"{$prefix}subjectPerson\"", "with_roles_wiki:\"{$wikiQ}|mainAuthor\"", ]);
						$goToRecLink = $this->buildUri('results', ['core'=>'biblio', 'facetsCode'=>$goToRecCode]);
						if ($i <= $maxBoxes) {
							$resultObj->solrRecord->bottomLink = $goToRecLink;
							$resultObj->solrRecord->bottomTitle = 
							$resultObj->solrRecord->bottomStr = $this->transEsc("Go to results");
							
							$boxId = 'subjectPersonBox_'.$wikiQ;
							
							#$this->addJS("results.mapsMenu.drawRelationLine('".uniqid()."', '{$boxId}', 'centerImage');");
							
							echo '<div class="result-box '.$currentCore.'-result " id="'.$boxId.'">';
							echo $this->render('wikiResults/resultBoxes/'.$currentView.'.php',['result'=>$resultObj]);
							echo '</div>';
							} else {
							echo '<div class="person-info-single-line">
								<a href="'. $this->buildUri('wiki/record/'.$wikiQ) .'" title="'. $this->transEsc('card of').'...">'. $resultObj->getStr('labels') .'</a>
								<a class="bibLink" href="'.$goToRecLink.'" title="'.$this->transEsc('Go to results').'"><i class="'.$this->helper->formatMajorRole('mainAuthor')->ico.'"></i> '.$count.'</a>
								</div>';
							}
						}
					}
				}
			echo '</div>';
			echo '<div class="relGraph-cell-middle">';
			echo $centerImage;
			echo '</div>';
			echo '<div class="relGraph-cell">';
			if (!empty($personsToShow['mainAuthor'])) {
				$code = $this->buffer->createFacetsCode(["with_roles_wiki:\"{$prefix}mainAuthor\""]);
				echo '<a href="'.$this->buildUri('results', ['core'=>'biblio', 'facetsCode'=>$code]).'" title="'.$this->transEsc('Go to bibliographic records').'"><b>'.$this->transEsc('They were the subject of publications in which the main author was').' '.$this->wiki->get('labels').':</b></a><br/>';
				$i = 0;
				foreach ($personsToShow['mainAuthor'] as $wikiQ=>$count) {
					# echo $activePersonId.' ?? '.$wikiQ.' '.$count.'<br/>';
					if (!empty($readyItems[$wikiQ]) & ($wikiQ != $activePersonId)) {
						$i++;
						$resultObj = $readyItems[$wikiQ];
						$goToRecCode = $this->buffer->createFacetsCode(["with_roles_wiki:\"{$prefix}mainAuthor\"", "with_roles_wiki:\"{$wikiQ}|subjectPerson\"", ]);
						$goToRecLink = $this->buildUri('results', ['core'=>'biblio', 'facetsCode'=>$goToRecCode]);
						
						if ($i <= $maxBoxes) {
							$resultObj->solrRecord->bottomLink = $goToRecLink;
							$resultObj->solrRecord->bottomTitle = 
							$resultObj->solrRecord->bottomStr = $this->transEsc("Go to results");
							$boxId = 'mainAuthorBox_'.$wikiQ;
							
							#$this->addJS("results.mapsMenu.drawRelationLine('".uniqid()."', 'centerImage', '{$boxId}');");
							
							echo '<div class="result-box '.$currentCore.'-result " id="'.$boxId.'">';
							echo $this->render('wikiResults/resultBoxes/'.$currentView.'.php',['result'=>$resultObj]);
							echo '</div>';
							} else {
							echo '<div class="person-info-single-line">
								<a href="'. $this->buildUri('wiki/record/'.$wikiQ) .'" title="'. $this->transEsc('card of').'...">'. $resultObj->getStr('labels') .'</a>
								<a class="bibLink" href="'.$goToRecLink.'" title="'.$this->transEsc('Go to results').'"><i class="'.$this->helper->formatMajorRole('subjectPerson')->ico.'"></i> '.$count.'</a>
								</div>';
							}
						}
					}
						
				}
			echo '</div>';
			echo '</div>';
			echo '</div>';
			#echo $this->helper->pre($this->JS);
		break;
	default :
			
			$query = [];
			$query['q'] 			= ['field' => 'q',				'value' => '*:*' ];
			$query['rows'] 			= ['field' => 'rows',			'value' => '0' ];
			$query['fq'] 			= ['field' => 'fq',				'value' => 'all_wiki:"'.$activePersonId.'"'];
			$query['facet'] 		= ['field' => 'facet',			'value' => 'true'];
			$query[] 				= ['field' => 'facet.field',	'value' => 'all_wiki'];		
			$query['facet.limit']	= ['field' => 'facet.limit',	'value' => 27 ];
			$query['facet.mincount']	= ['field' => 'facet.mincount', 	'value' => 1 ];
			$this->solr->getQuery('biblio', $query); 
			$results = $this->solr->resultsList();
			$allRoles = $this->solr->facetsList();	
			$i = 0;
			if (!empty($allRoles['all_wiki']))
				foreach ($allRoles['all_wiki'] as $personWikiQ => $count) 
					if ($personWikiQ != $activePersonId)
						$personsToTake[] = $personWikiQ;
					
				

			if (!empty($personsToTake)) {
				$personsToTake = array_unique($personsToTake);
				$query = [];			
				$query['q'] = [
						'field' 	=> 'q',
						'value' 	=> 'id:('.implode(' OR ',$personsToTake).')'
						];
				$query['rows'] = [
						'field' 	=> 'rows',
						'value' 	=> count($personsToTake)
						];
				$query['start'] = [
						'field' 	=> 'start',
						'value' 	=> 0
						];
							
				$results = $this->solr->getQuery('persons', $query); 
				$results = $this->solr->resultsList();
				
				echo '<br/>';
				echo $this->helper->alertIco('info', 'ph ph-info',
					'<p style="padding-top:20px;">'.$this->transEsc('Just a few of the strongest relations are shown below').'. '
					.$this->transEsc('A relation means the joint appearance of the viewed object and the selected person in bibliographic records (in any role).')
					.'<div class="text-right"><a href="'.$this->buildUrl('wiki/record/'.$activePersonId.'/more').'" class="btn btn-link"><i class="ph ph-bookmarks"></i> '.$this->transEsc('More relations and options in the advanced version of the tab panel').'</b></a></div>'
					.'</p>'
					);
				
				echo '<br/><div class="showcase-list wiki-related-list ">';
				foreach ($results as $result) 
					if ($result->wikiq != $activePersonId) {
						$resultObj = new wikiLibri($this->user->lang['userLang'], $result);
						
						$goToRecCode = $this->buffer->createFacetsCode(["all_wiki:\"{$activePersonId}\"", "all_wiki:\"{$result->id}\"", ]);
						$goToRecLink = $this->buildUri('results', ['core'=>'biblio', 'facetsCode'=>$goToRecCode]);
							
						$resultObj->solrRecord->bottomLink = $goToRecLink;
						$resultObj->solrRecord->bottomTitle = 
						$resultObj->solrRecord->bottomStr = $this->transEsc("Go to results");
						$boxId = 'mainAuthorBox_'.$result->wikiq;
						
						echo '<div class="result-box '.$currentCore.'-result " id="'.$boxId.'">';
						echo $this->render('wikiResults/resultBoxes/'.$currentView.'.php',['result'=>$resultObj]);
						echo '</div>';
						
						
						}
				echo '</div>';	
				
				} else 
				echo $this->transEsc('Nothing to show here');
		break;
	}









?>
