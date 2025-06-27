<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.forms.php');
require_once('functions/class.exporter.php');
require_once('functions/class.wikidata.php');

$recPerStep = 321;

$export = $this->getConfig('export');
$facets = $this->getConfig('search');
$facets = $this->getConfig('facets');

$this->addClass('buffer', 	new buffer()); 
$this->addClass('solr', 	new solr($this)); 
$this->addClass('helper', 	new helper()); 
$this->addClass('wikidata', 	new wikidata($this)); 

$this->buffer->bufferTime = 86400*360; // we don't want to update external records during export (because it cost time)

 
$query['q'] = $this->solr->lookFor(
			$lookfor = $this->getParam('GET', 'lookfor'), 
			$type = $this->getParam('GET', 'type') 
			);	
if (!empty($this->GET['sj'])) 
	$query['q'] = [ 
			'field' => 'q',
			'value' => $this->solr->advandedSearch($this->getParam('GET', 'sj'))
			];
if (!empty($this->routeParam[1])) {
		$this->facetsCode = $this->routeParam[1];	
		$query['fq'] = $this->buffer->getFacets($this->facetsCode);	
		} else 
		$this->facetsCode = 'null';		

$path = './files/exports/';
$fileName = $this->user->cmsKey;				
$folder = $path.$fileName;
if (!is_dir($folder)) {
	mkdir($folder);
	chmod($folder, 0775);
	}
	
if (!empty ($this->postParam('options'))) {
	$exportParams = (object) $this->postParam('options');
	
	#echo '<pre>'.print_r($exportParams,1).'</pre>';
	

	if (!empty($exportParams->exportTable)) {
		##############################################################################################################################################################
		##
		##										steeps 
		##
		##############################################################################################################################################################
		
		
		$currentStep = (object)current($exportParams->exportTable);
		#echo '<pre>'.print_r($currentStep,1).'</pre>';
		
		switch ($exportParams->fileFormat) {
			case 'mrk' : {
					$query[]=[ 
						'field' => 'rows',
						'value' => $recPerStep
						];
					$query[]=[ 
						'field' => 'start',
						'value' => $currentStep->startAt
						];
					$results = $this->solr->getQuery('biblio',$query); 
					$results = $this->solr->resultsList();
					$lp = $currentStep->startAt;
					foreach ($results as $result) {
						$lp++;
						file_put_contents($folder.'/'.$currentStep->name.'.mrk.part', $result->fullrecord, FILE_APPEND);
						}
					
					$total = $currentStep->totalResults;
					if ($lp < $total) {
						$exportParams->exportTable[$currentStep->name]['startAt'] = $lp;
						} else 
						unset($exportParams->exportTable[$currentStep->name]);
						
					
					echo "{$currentStep->name} ($lp / $total)<br/>";
					echo $this->helper->percent($lp,$total);
					$thin = base64_encode($this->helper->progressThin($lp,$total));
					$this->addJS("$('#exportField_{$currentStep->name}').html(atob('{$thin}'))");
					
					$OC = "results.ExportStart(\"{$exportParams->fileFormat}\", \"{$this->facetsCode}\", ".json_encode($exportParams).");";
					$this->addJS($OC);
		
					if (count($exportParams->exportTable)==0)
						echo $this->helper->loader($this->transEsc("Compressing..."));
					
					}
					break;
			case 'mrc' : {
					$query[]=[ 
						'field' => 'rows',
						'value' => $recPerStep
						];
					$query[]=[ 
						'field' => 'start',
						'value' => $currentStep->startAt
						];
					$results = $this->solr->getQuery('biblio',$query); 
					$results = $this->solr->resultsList();
					$lp = $currentStep->startAt;
					foreach ($results as $result) {
						$lp++;
						file_put_contents($folder.'/'.$currentStep->name.'.mrc.part', $result->fullrecord, FILE_APPEND);
						}
					
					$total = $currentStep->totalResults;
					if ($lp < $total) {
						$exportParams->exportTable[$currentStep->name]['startAt'] = $lp;
						} else 
						unset($exportParams->exportTable[$currentStep->name]);
						
					
					echo "{$currentStep->name} ($lp / $total)<br/>";
					echo $this->helper->percent($lp,$total);
					$thin = base64_encode($this->helper->progressThin($lp,$total));
					$this->addJS("$('#exportField_{$currentStep->name}').html(atob('{$thin}'))");
					
					$OC = "results.ExportStart(\"{$exportParams->fileFormat}\", \"{$this->facetsCode}\", ".json_encode($exportParams).");";
					$this->addJS($OC);
		
					if (count($exportParams->exportTable)==0)
						echo $this->helper->loader($this->transEsc("Compressing..."));
					
					}
					break;
			
			case 'json':	{
					$currentPack = $Tjson = [];
					$exportFile = $folder.'/'.$currentStep->name.'.json.part';
					if (!file_exists($exportFile))
						file_put_contents($exportFile, '[');	
					
					
					switch ($currentStep->name) {
						case 'persons' :
								$query['limit']=[
									'field' => 'facet.limit',
									'value' => $recPerStep
									];
								$query[]=[ 
									'field' => 'facet.field',
									'value' => $currentStep->facetName
									];
								$query[]=[ 
									'field' => 'facet.offset',
									'value' => $currentStep->startAt
									];
								$query[]=[ 
									'field' => 'rows',
									'value' => 0
									];
								
								$results = $this->solr->getFacets('biblio', [$currentStep->facetName], $query); 
								#echo "<pre>".print_r($results,1).'<pre>';
								$lp = $currentStep->startAt;
								if (!empty($results[$currentStep->facetName])) {
									$Tid = [];
									foreach ($results[$currentStep->facetName] as $result=>$count) {
										$lp++;
										$t = explode('|', $result);
										$person = new stdClass;
										$person->name = $t[0];
										$person->yearBorn = $t[1];
										$person->yearDeath = $t[2];
										$person->viaf = $t[3];
										$person->wiki = $t[4];
										$Tid[] = $person->wiki;
										$person->recCount = $count;
										
										if (!empty($person->wiki))
											$currentPack[$person->wiki] = $person;
											else
											$Tjson[] = json_encode($person);
										}
									
									getRoles($this, $currentPack);
									
									
									$query = [];
									$query['rows']=[
											'field' => 'rows',
											'value' => count($Tid)
											];
									$query['q'] = [
											'field' 	=> 'q',
											'value' 	=> 'id:('.implode(' OR ',$Tid).')'
											];
									$query['start'] = [
											'field' 	=> 'start',
											'value' 	=> 0
											];
									
									$results = $this->solr->getQuery($currentStep->name, $query); 
									$results = $this->solr->resultsList();
									#echo $this->helper->pre($results);
									
									foreach ($results as $result) {
								
										$person = $currentPack[$result->wikiq];
										$person->fromWiki = new stdClass;
										$person->fromWiki->dateB = $result->birth_date ?? '';
										$person->fromWiki->dateD = $result->death_date ?? '';
										$person->fromWiki->placeB = $result->birth_place ?? '';
										$person->fromWiki->placeD = $result->death_place ?? '';
										if (!empty($result->picture))
											$person->fromWiki->picture = current($result->picture);
										$person->fromWiki->otherIDs = $result->eids ?? [];
										$person->fromWiki->sstring = $result->labels_search ?? '';
										$person->fromWiki->native_labels = $result->native_labels ?? [];
										
										
										
										$Tjson[] = json_encode($person);
										}
									
									$Tjson = array_values(array_unique($Tjson));
									file_put_contents($exportFile, implode(",\n",$Tjson), FILE_APPEND);	
									}
								break;
						
						case 'places' :
								$query['limit']=[
									'field' => 'facet.limit',
									'value' => $recPerStep
									];
								$query[]=[ 
									'field' => 'facet.field',
									'value' => $currentStep->facetName
									];
								$query[]=[ 
									'field' => 'facet.offset',
									'value' => $currentStep->startAt
									];
								$query[]=[ 
									'field' => 'rows',
									'value' => 0
									];
								
								$results = $this->solr->getFacets('biblio', [$currentStep->facetName], $query); 
								#echo "<pre>".print_r($results,1).'<pre>';
								$lp = $currentStep->startAt;
								
								if (!empty($results[$currentStep->facetName])) {
									$Tid = [];
									foreach ($results[$currentStep->facetName] as $result=>$count) {
										$lp++;
										$place = new stdClass;
										$t = explode('|', $result);
										$place->wiki = $t[0];
										$Tid[] = $place->wiki;
										unset($t[0]);
										$place->names = array_unique($t);
										$place->recCount = $count;
										#$place->roles = $this->solr->getRoles('biblio', 'places_with_roles', $result);
										
										if (!empty($place->wiki))
											$currentPack[$place->wiki] = $place;
											else
											$Tjson[] = json_encode($place);
										}
									
									getRoles($this, $currentPack);
										
									$query = [];
									$query['rows']=[
											'field' => 'rows',
											'value' => count($Tid)
											];
									$query['q'] = [
											'field' 	=> 'q',
											'value' 	=> 'id:('.implode(' OR ',$Tid).')'
											];
									$query['start'] = [
											'field' 	=> 'start',
											'value' 	=> 0
											];
									$results = $this->solr->getQuery($currentStep->name, $query); 
									$results = $this->solr->resultsList();
									
									#echo $this->helper->pre($results);
									if (!empty($facets->with_roles_wiki)) {
										foreach ($facets->with_roles_wiki as $facetValue=>$facetCount) {
											$line = explode('|', $facetValue);
											$wikiq = $line[0];
											$rolw = $line[1];
											$currentPack[$wikiq]->roles[$role] = $facetCount;
											}
										}
									
									foreach ($results as $result) {
										
										$place = $currentPack[$result->wikiq];
										$place->fromWiki = new stdClass;
										$place->fromWiki->latitiude = $result->latitiude ?? '';
										$place->fromWiki->longitiude = $result->longitiude ?? '';
										if (!empty($result->picture))
											$place->fromWiki->picture = current($result->picture);
										$place->fromWiki->otherIDs = $result->eids ?? [];
										$place->fromWiki->native_labels = $result->native_labels ?? [];
										
										
										
										$Tjson[$place->wiki] = json_encode($place);
										}
									$Tjson = array_values(array_unique($Tjson));
									
									file_put_contents($folder.'/'.$currentStep->name.'.json.part', implode(",\n",$Tjson), FILE_APPEND);	
									}
								break;
						
						case 'corporates' :
								$query['limit']=[
									'field' => 'facet.limit',
									'value' => $recPerStep
									];
								$query[]=[ 
									'field' => 'facet.field',
									'value' => $currentStep->facetName
									];
								$query[]=[ 
									'field' => 'facet.offset',
									'value' => $currentStep->startAt
									];
								$query[]=[ 
									'field' => 'rows',
									'value' => 0
									];
								
								$results = $this->solr->getFacets('biblio', [$currentStep->facetName], $query); 
								#echo "<pre>".print_r($results,1).'<pre>';
								$lp = $currentStep->startAt;
								if (!empty($results[$currentStep->facetName])) {
									foreach ($results[$currentStep->facetName] as $result=>$count) {
										$lp++;
										$t = explode('|', $result);
										$person = new stdClass;
										$person->name = $t[0];
										$person->wiki = $t[1];
										$person->recCount = $count;
										$person->roles = $this->solr->getRoles('biblio', 'corporates_with_roles', $result);
										$Tjson [] = json_encode($person);
										}
									file_put_contents($folder.'/'.$currentStep->name.'.json.part', implode(",\n",$Tjson), FILE_APPEND);	
									}
								break;								
						case 'events' :
								$query['limit']=[
									'field' => 'facet.limit',
									'value' => $recPerStep
									];
								$query[]=[ 
									'field' => 'facet.field',
									'value' => $currentStep->facetName
									];
								$query[]=[ 
									'field' => 'facet.offset',
									'value' => $currentStep->startAt
									];
								$query[]=[ 
									'field' => 'rows',
									'value' => 0
									];
								
								$results = $this->solr->getFacets('biblio', [$currentStep->facetName], $query); 
								#echo "<pre>".print_r($results,1).'<pre>';
								$lp = $currentStep->startAt;
								
								if (!empty($results[$currentStep->facetName])) {
									foreach ($results[$currentStep->facetName] as $result=>$count) {
										$lp++;
										$t = explode('|', $result);
										$event = new stdClass;
										$event->name = $t[0];
										$event->year = $t[1];
										$event->place = $t[2];
										$event->recCount = $count;
										$event->roles = $this->solr->getRoles('biblio', 'events_with_roles', $result);
										$Tjson [] = json_encode($event);
										}
									file_put_contents($exportFile, implode(",\n",$Tjson), FILE_APPEND);	
									}
								break;
						
						
						case 'series' :
								$query['limit']=[
									'field' => 'facet.limit',
									'value' => $recPerStep
									];
								$query[]=[ 
									'field' => 'facet.field',
									'value' => $currentStep->facetName
									];
								$query[]=[ 
									'field' => 'facet.offset',
									'value' => $currentStep->startAt
									];
								$query[]=[ 
									'field' => 'rows',
									'value' => 0
									];
								
								$results = $this->solr->getFacets('biblio', [$currentStep->facetName], $query); 
								#echo "<pre>".print_r($results,1).'<pre>';
								$lp = $currentStep->startAt;
								if (!empty($results[$currentStep->facetName])) {
									foreach ($results[$currentStep->facetName] as $result=>$count) {
										$lp++;
										$seria = new stdClass;
										$seria->name = $result;
										$seria->recCount = $count;
										
										$Tjson [] = json_encode($seria);
										}
									file_put_contents($folder.'/'.$currentStep->name.'.json.part', implode(",\n",$Tjson), FILE_APPEND);	
									}
								break;
						
						case 'magazines' :
								$query['facet.limit']=[
									'field' => 'facet.limit',
									'value' => $recPerStep
									];
								$query['facet.field']=[ 
									'field' => 'facet.field',
									'value' => $currentStep->facetName
									];
								$query['facet.offset']=[ 
									'field' => 'f.magazines_str_mv.facet.offset', // keeping offset only on first field
									'value' => $currentStep->startAt
									];
								$query['rows']=[ 
									'field' => 'rows',
									'value' => 0
									];
								
								$query['facet.pivot.mincount'] = [
									'field' => 'facet.pivot.mincount',
									'value' => 1
									];
								$facetPivot = 'magazines_str_mv,publishDate,article_resource_related_str_mv';
								$query['facet.pivot'] = [
									'field' => 'facet.pivot',
									'value' => $facetPivot
									];
								$results = $this->solr->getFacets('biblio', [$currentStep->facetName], $query); 
								#echo "<pre>".print_r($results,1).'<pre>';
								$lp = $currentStep->startAt;
								
								if (!empty($results[$currentStep->facetName])) {
									foreach ($results[$currentStep->facetName] as $result=>$count) {
										$lp++;
										$cresult = str_replace(['{','}'], '', $result);
										$t = explode('|', $cresult);
										
										$magazine = new stdClass;
										$magazine->name = $t[0];
										if (!empty($t[1]))
											$magazine->issn = substr($t[1],0,9);
											else 
											$magazine->issn = '';
										$magazine->recCount = $count;
										$magazine->years = [];
										
										if (!empty($this->solr->facet_pivot->$facetPivot)) {
											foreach ($this->solr->facet_pivot->$facetPivot as $magazinePivot) {
												if (!empty($magazinePivot->pivot)) 
													foreach ($magazinePivot->pivot as $yearValues) {
														$Tres = [];
														if (!empty($yearValues->pivot))
															foreach ($yearValues->pivot as $res) 
																$Tres[$res->value] = $res->count;
														$magazine->years[$yearValues->value] = (object)[
															'recCount' => $yearValues->count,
															'recList' => $Tres
															];
														}
												}
											}
										
										
										####
										
										
										$Tjson [] = json_encode($magazine);
										}
									file_put_contents($folder.'/'.$currentStep->name.'.json.part', implode(",\n",$Tjson), FILE_APPEND);	
									}
								break;
								

						case 'biblio' :
								$query[]=[
									'field' => 'fl',
									'value' => 'id',
									];
								$query[]=[ 
									'field' => 'rows',
									'value' => $recPerStep
									];
								$query[]=[ 
									'field' => 'start',
									'value' => $currentStep->startAt
									];
								$results = $this->solr->getQuery('biblio',$query); 
								$results = $this->solr->resultsList();
								$lp = $currentStep->startAt;
								foreach ($results as $result) {
									$lp++;
									
									$record = $this->solr->getRecord('biblio', $result->id);
									file_put_contents($folder.'/'.$currentStep->name.'.'.$record->record_format.'.part', $record->fullrecord, FILE_APPEND);
									# unset($result->fullrecord);
									if (isset($result->user_list))
										unset($result->user_list);
									#$Tjson [] = json_encode($record);
									$Tjson [] = $record->relations;
									}
								file_put_contents(
										$folder.'/'.$currentStep->name.'.json.part', 
										implode(",\n",$Tjson), 
										FILE_APPEND
										);	
													
								break;
						
						default: 
							echo "I don't have instrutions for: <b>{$currentStep->name}</b>";
						
						} // switch table name
					$total = floatval($currentStep->totalResults);
					
					if ($lp < $total) {
						file_put_contents($folder.'/'.$currentStep->name.'.json.part', ",\n", FILE_APPEND);	
						$exportParams->exportTable[$currentStep->name]['startAt'] = $lp;
						} else {
						file_put_contents($exportFile, ']', FILE_APPEND);	
						unset($exportParams->exportTable[$currentStep->name]);
						}
						
					
					echo "{$currentStep->name} ($lp / $total)<br/>";
					echo $this->helper->percent($lp,$total);
					$thin = base64_encode($this->helper->progressThin($lp,$total));
					$this->addJS("$('#exportField_{$currentStep->name}').html(atob('{$thin}'))");
					
					$OC = "results.ExportStart(\"{$exportParams->fileFormat}\", \"{$this->facetsCode}\", ".json_encode($exportParams).");";
					$this->addJS($OC);
					# echo "<button OnClick='$OC'>next</button>";
					if (count($exportParams->exportTable)==0)
						echo $this->helper->loader($this->transEsc("Compressing..."));
					} 		
					break;
			} // switch fileFormat
		
		} else {
		##############################################################################################################################################################
		##
		##										last screen
		##
		##############################################################################################################################################################
			
		
		$list = glob ($folder.'/*.part');
		foreach ($list as $file) {
			rename($file, str_replace('.part', '', $file));
			}
		exec("cd /var/www/html/files/exports/ && zip -r {$fileName}.zip {$fileName}");
		
		if (file_exists('./files/exports/'.$fileName.'.zip')) {
			echo '<div class="text-center">';
			echo '<a href="'.$this->HOST.'files/exports/'.$fileName.'.zip" class="btn btn-success">'.$this->transEsc('Download ZIP file').'</a>';
			echo '</div>';
			} else {
			echo $this->helper->alert('danger', $this->transEsc('Error during compression to zip. Try again in a while.'));
			}
		$this->addJS('$("#exportBtn").html(" ");');
		}
	
	} else if (!empty($this->routeParam[0])) {
		$list = glob ($folder."/*");
		foreach ($list as $file)
			unlink($file);
		if (file_exists($folder.'.zip'))
			unlink($folder.'.zip');
		
		## clear old exports
		$list = glob ($path.'*.*');
		foreach ($list as $file) {
			$time = filemtime($file);
			if ((time()-$time)>86500)
				unlink($file);
			}
		$list = glob ($path.'*/*.*');
		foreach ($list as $file) {
			$time = filemtime($file);
			if ((time()-$time)>86500) {
				unlink($file);
				}
			}
		$list = glob ($path.'*');	
		foreach ($list as $dir) 
			if (is_dir($dir)) {
				$isDirEmpty = !(new \FilesystemIterator($dir))->valid();
				if ($isDirEmpty)
					rmdir ($dir);
				}
		if (!file_exists($path.'index.php'))
			file_put_contents($path.'index.php', '');
		#echo '<pre>'.print_r($list,1).'</pre>';
		
		##############################################################################################################################################################
		##
		##										First screen
		##
		##############################################################################################################################################################
		
		
		$exports = $this->getIniParam('export', 'ExportList');
		$exportParams = new stdClass;
		$exportParams->fileFormat = $this->routeParam[0];
		$exportParams->formatName = $exports[$exportParams->fileFormat];
		$exportParams->exportTable = new stdClass;
		 
		
		$query[]=[ 
				'field' => 'rows',
				'value' => $recPerStep
				];

		$query[]=[ 
				'field' => 'facet.limit',
				'value' => '0'
				];		
				
		$query[]=[ 
				'field' => 'start',
				'value' => 0
				];		
		
		switch ($exportParams->fileFormat) {
			case 'json' : 
						$indexes = [
							'persons'=>'persons_ac',
							'places'=>'places_ac',
							'corporates'=>'coporates_ac', //corporates_ac
							'magazines'=>'magazines_ac', 
							'events'=>'events_ac',
							#'series'=>'series_str_mv',
							];
						$indexesD = [
							'places'=>'Places',
							'persons'=>'Persons',
							'corporates'=>'Corporates',
							'magazines'=>'Magazines', // publication_place_str_mv ?
							'events'=>'Events',
							'series'=>'Publication series',
							];
						
						foreach ($indexes as $exportName=>$indexName)
							$query[] =  $this->solr->facetsCountCode($indexName);
						
						$res = $this->solr->getFacets('biblio', $indexes, $query);
						
						foreach ($indexes as $exportName=>$indexName) {
							$exportParams->exportTable->$exportName = new stdClass;
						
							$exportParams->exportTable->$exportName->name = $exportName;
							$exportParams->exportTable->$exportName->displayName = $indexesD[$exportName];
							$exportParams->exportTable->$exportName->facetName = $indexName;
							$exportParams->exportTable->$exportName->startAt = 0;
							$exportParams->exportTable->$exportName->totalResults = $this->solr->getFacetsCount($indexName);
							}
						break;	
			
			}
		$results = $this->solr->getQuery('biblio',$query); 

		$exportParams->exportTable->biblio = new stdClass;
		$exportParams->exportTable->biblio->name = 'biblio';
		$exportParams->exportTable->biblio->displayName = 'Bibliographic';
		$exportParams->exportTable->biblio->startAt = 0;
		$exportParams->exportTable->biblio->totalResults = $this->solr->totalResults();


		#echo "<pre>".print_r($_SERVER,1)."</pre>";

		echo $this->render('export/multi.php', ['exportParams' => $exportParams] );
		
		}
	
	
	
	
function getRoles($system, &$currentPack) {
	// get roles	
	$Tid = array_keys($currentPack);
	$query = [];
	$query['rows']=[
			'field' => 'rows',
			'value' => 0
			];
	$query['q'] = [
			'field' 	=> 'q',
			'value' 	=> 'all_wiki:('.implode(' OR ',$Tid).')'
			];
	$query['start'] = [
			'field' 	=> 'start',
			'value' 	=> 0
			];
	$query['facet'] = [
			'field' 	=> 'facet',
			'value' 	=> true
			];
	$query['facet.field'] = [
			'field' 	=> 'facet.field',
			'value' 	=> 'with_roles_wiki'
			];
	$results = $system->solr->getQuery('biblio', $query); 
	$facets = $system->solr->facetsList();
	if (!empty($facets->with_roles_wiki)) {
		foreach ($facets->with_roles_wiki as $facetValue=>$facetCount) {
			$line = explode('|', $facetValue);
			$wikiq = $line[0];
			$rolw = $line[1];
			$currentPack[$wikiq]->roles[$role] = $facetCount;
			}
		}
	}										
	
#echo "<br/><br/>{$this->user->cmsKey}";
#echo "<pre>".print_r($this->routeParam,1)."</pre>";
#echo "<pre>".print_r($this->GET,1)."</pre>";
?>


