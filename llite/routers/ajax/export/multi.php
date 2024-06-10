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
			case 'mrk.old' : {
					$this->addClass('exporter', new exporter()); 

					$query[]=[ 
						'field' => 'rows',
						'value' => $recPerStep
						];
					$query[]=[ 
						'field' => 'start',
						'value' => $currentStep->startAt
						];
					$query[]=[ 
						'field' => 'fl',
						'value' => 'id'
						];
					$results = $this->solr->getQuery('biblio',$query); 
					$results = $this->solr->resultsList();
					$lp = $currentStep->startAt;
					foreach ($results as $result) {
						$lp++;
						$id = $result->id;
						$fkey = substr($result->id, 0, 5);
						$jsonFile = "./files/json/{$fkey}/{$result->id}.json";
						if (file_exists($jsonFile))
							file_put_contents(
									$folder.'/'.$currentStep->name.'.mrk.part', 
									$plik = $this->exporter->toMRK(json_decode(file_get_contents($jsonFile))), 
									FILE_APPEND
									);
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
			case 'json.old':	{
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
									foreach ($results[$currentStep->facetName] as $result=>$count) {
										$lp++;
										$t = explode('|', $result);
										$person = new stdClass;
										$person->name = $t[0];
										$person->yearBorn = $t[1];
										$person->yearDeath = $t[2];
										$person->viaf = $t[3];
										$person->wiki = $t[4];
										$person->recCount = $count;
										
										if (!empty($person->wiki)) {
											$wiki = new wikidata('Q'.$person->wiki);
											$person->fromWiki = new stdClass;
											$person->fromWiki->dateB = $wiki->getDate('P569');
											$person->fromWiki->dateD = $wiki->getDate('P570');
											$person->fromWiki->placeB = $wiki->getPropId('P19');
											$person->fromWiki->placeD = $wiki->getPropId('P20');
											$person->fromWiki->sstring = $wiki->getSearchString();
											}
										
										$Tjson [] = json_encode($person);
										}
									file_put_contents($exportFile, implode(",\n",$Tjson), FILE_APPEND);	
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
										
										$Tjson [] = json_encode($event);
										}
									file_put_contents($exportFile, implode(",\n",$Tjson), FILE_APPEND);	
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
										$person->viaf = $t[1];
										$person->wiki = $t[2];
										$person->recCount = $count;
										
										$Tjson [] = json_encode($person);
										}
									file_put_contents($folder.'/'.$currentStep->name.'.json.part', implode(",\n",$Tjson), FILE_APPEND);	
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
								// try to use: facet.pivot={$currentStep->facetName},publishDate,article_resource_related_str_mv
								
								
								$query['facet.limit']=[
									'field' => 'facet.limit',
									'value' => 2 //$recPerStep
									];
								$query['facet.field']=[ 
									'field' => 'facet.field',
									'value' => $currentStep->facetName
									];
								$query['facet.offset']=[ 
									'field' => 'facet.offset',
									'value' => $currentStep->startAt
									];
								$query['rows']=[ 
									'field' => 'rows',
									'value' => 0
									];
								
								$querySub = $query;
								$querySub['facet.mincount']=[
									'field' => 'facet.limit',
									'value' => 1
									];
								$querySub['facet.limit']=[
									'field' => 'facet.limit',
									'value' => 1000
									];
								$querySub['facet.field']=[ 
									'field' => 'facet.field',
									'value' => 'publishDate'
									];
								$querySub['facet.offset']=[ 
									'field' => 'facet.offset',
									'value' => 0
									];
								
								$results = $this->solr->getFacets('biblio', [$currentStep->facetName], $query); 
								#echo "<pre>".print_r($results,1).'<pre>';
								$lp = $currentStep->startAt;
								
								if (!empty($results[$currentStep->facetName])) {
									foreach ($results[$currentStep->facetName] as $result=>$count) {
										$lp++;
										$cresult = str_replace(['{','}'], '', $result);
										$t = explode(', issn=', $cresult);
										$place = new stdClass;
										$place->name = trim(str_replace('name=','', $t[0]));
										if (!empty($t[1]))
											$place->issn = substr($t[1],0,9);
											else 
											$place->issn = '';
										$place->recCount = $count;
										
										$place->years = [];
										
										
										####
										
										$Tuf = [];
										if (!empty($this->buffer->usedFacetsStr)) 
											$Tuf = $this->buffer->usedFacetsStr;
										$Tuf['step'] = $this->buffer->facetLine($currentStep->facetName, $result);
										
										$querySub['fq'] = $this->buffer->getFacetsFromStr(http_build_query($Tuf));	
										
										$tmp = $this->solr->getCleanedYears('biblio', ['publishDate'], $querySub); 
										if (!empty($tmp['publishDate'])) {
											$resSub = $tmp['publishDate'];
											foreach ($resSub as $k=>$v)
												if ($v == 0)
													unset($resSub[$k]);
													else {
													$inYear = new stdClass;
													$inYear->recCount = $v;
													
													
													$Tuf['step2'] = $this->buffer->facetLine('publishDate', $k);
													$querySub['fq'] = $this->buffer->getFacetsFromStr(http_build_query($Tuf));	
													
													$inYear->recList = $this->solr->getFacets('biblio', ['article_resource_related_str_mv'], $querySub)['article_resource_related_str_mv']; 
													
													$resSub[$k] = $inYear;
													}
											$place->years = $resSub;
											}
										
									
										
										
										####
										
										
										$Tjson [] = json_encode($place);
										}
									file_put_contents($folder.'/'.$currentStep->name.'.json.part', implode(",\n",$Tjson), FILE_APPEND);	
									}
								break;
								
						case 'event_places' :
						case 'pub_places' :
						case 'sub_places' :
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
										$place = new stdClass;
										$t = explode('|', $result);
										$place->name = $t[0];
										$place->wiki = $t[1];
										
										$t = $this->psql->querySelect($Q = "SELECT * FROM places_wiki WHERE wiki = {$this->psql->isNull($place->wiki)};");
										if (is_array($t)) {
											$add = current($t);	 
											if (($add['lat']<>'') && ($add['lon']))
												$place->coordinates = $add['lat'].','.$add['lon'];
												else 
												$place->coordinates = '';	
											} else {
											# file_put_contents($folder.'/queries.'.$currentStep->name.'.json.part', $Q."\n", FILE_APPEND);	
											$place->coordinates = '';
											$place->wiki = '';
											}
										$place->recCount = $count;
										
										$Tjson [] = json_encode($place);
										}
									file_put_contents($folder.'/'.$currentStep->name.'.json.part', implode(",\n",$Tjson), FILE_APPEND);	
									}
								break;
								
						case 'biblio' :
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
									# file_put_contents($folder.'/'.$currentStep->name.'.mrc.part', $result->fullrecord, FILE_APPEND);
									# unset($result->fullrecord);
									$Tjson [] = json_encode($result);
									}
								file_put_contents($folder.'/'.$currentStep->name.'.json.part', implode(",\n",$Tjson), FILE_APPEND);	
								#$lp = $currentStep->totalResults;
								break;
						
						default: 
							echo "nie wiem co robić z <b>{$currentStep->name}</b>";
						
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
			case 'json':	{
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
									foreach ($results[$currentStep->facetName] as $result=>$count) {
										$lp++;
										$t = explode('|', $result);
										$person = new stdClass;
										$person->name = $t[0];
										$person->yearBorn = $t[1];
										$person->yearDeath = $t[2];
										$person->viaf = $t[3];
										$person->wiki = $t[4];
										$person->recCount = $count;
										
										/*
										if (!empty($person->wiki)) {
											$wiki = new wikidata('Q'.$person->wiki);
											$person->fromWiki = new stdClass;
											$person->fromWiki->dateB = $wiki->getDate('P569');
											$person->fromWiki->dateD = $wiki->getDate('P570');
											$person->fromWiki->placeB = $wiki->getPropId('P19');
											$person->fromWiki->placeD = $wiki->getPropId('P20');
											$person->fromWiki->sstring = $wiki->getSearchString();
											}
										*/
										$person->roles = $this->solr->getRoles('biblio', 'persons_with_roles', $result);
										$Tjson [] = json_encode($person);
										}
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
									foreach ($results[$currentStep->facetName] as $result=>$count) {
										$lp++;
										$place = new stdClass;
										$t = explode('|', $result);
										$place->wiki = $t[0];
										$place->name = $t[3];
										unset($t[0], $t[3]);
										$place->alterNames = array_unique($t); 
										if (!empty($place->wiki)) {
											$wiki = new wikidata('Q'.$place->wiki);
											$place->fromWiki = new stdClass;
											$place->fromWiki->coordinates = $wiki->getSolrValue('long_lat');
											$place->alterLabelsInBiblioRec = $wiki->getSolrValue('biblio_labels');
											} else {
											# file_put_contents($folder.'/queries.'.$currentStep->name.'.json.part', $Q."\n", FILE_APPEND);	
											$place->coordinates = '';
											$place->wiki = '';
											}
										
										$place->recCount = $count;
										$place->roles = $this->solr->getRoles('biblio', 'places_with_roles', $result);
										$Tjson [] = json_encode($place);
										}
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
										$person->viaf = $t[1];
										$person->wiki = $t[2];
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
									$Tjson [] = json_encode($record);
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
					// echo "<button OnClick='$OC'>next</button>";
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
		exec("cd /var/www/html/lite/files/exports/ && zip -r {$fileName}.zip {$fileName}");
		echo '<div class="text-center">';
		echo '<a href="'.$this->HOST.'files/exports/'.$fileName.'.zip" class="btn btn-success">'.$this->transEsc('Download ZIP file').'</a>';
		echo '</div>';
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
							'persons'=>'persons_str_mv',
							'places'=>'geowikifull_str_mv',
							'corporates'=>'corporate_str_mv',
							'magazines'=>'magazines_str_mv', 
							'events'=>'events_str_mv',
							'series'=>'series_str_mv',
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
	
	
#echo "<br/><br/>{$this->user->cmsKey}";
#echo "<pre>".print_r($this->routeParam,1)."</pre>";
#echo "<pre>".print_r($this->GET,1)."</pre>";
?>


