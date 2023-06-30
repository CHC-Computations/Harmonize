<?php
if (empty($this)) die;
require_once('functions/klasa.maps.php');
require_once('functions/klasa.persons.php');
require_once('functions/klasa.places.php');
require_once('functions/klasa.wikidata.php');


$wikiId = $this->routeParam[0];
$wikiIdInt = substr($wikiId,1);
$this->clearGET();

$this->addClass('buffer', 	new marcBuffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->addClass('solr', 	new solr($this->config));  
$this->addClass('wiki', 	new wikidata($wikiId)); 

$this->buffer->setSQL($this->sql);
$this->wiki->setUserLang($this->user->lang['userLang']);


$photo = $this->buffer->loadWikiMediaUrl($this->wiki->getStrVal('P18'));
$audio = $this->buffer->loadWikiMediaUrl($this->wiki->getStrVal('P443'));

$this->setTitle($this->wiki->get('labels'));


function personFromStr($str, $field, $count) {
	$rec = explode('|', $str); 
	$desc['name'] = $rec[0];
	$desc['year_born'] = $rec[1];
	$desc['year_death'] = $rec[2];
	$desc['viaf_id'] = $rec[3];
	$desc['wikiq'] = trim($rec[4]);
	$desc['date'] = trim($rec[5]);
	$desc['solr_str'] = $str;
	if (!empty($desc['wikiq'])) {
		$desc['field'] = "personBoxQ".$desc['wikiq'];
		} else {
		$desc['field'] = "personBoxB".hash('crc32b',$desc['name'].$desc['date']);	
		}
	$desc[$field] = $count;
	return (object)$desc;
	}

$stat = [];
switch ($this->wiki->recType()) {
	case 'person' :

			$rec_id = $this->wiki->getViafId();
			$this->addClass('person', 
					new person(
						$persJson = $this->buffer->loadPerson($rec_id)
							)
						);
						
			
			$heWroteAbout =
			$theyWroteAbout = [];
			$statFields = $this->getIniParam('persons','statList','statFields');
			
			$activePerson = $this->wiki->getActivePersonValues();
			
			$stats = [];
			
			$query['q'] = ['field' => 'q', 'value' => 'author_facet:"'.$activePerson->solr_str.'"'];
			$query['limit'] = ['field' => 'facet.limit', 'value' => 100];
			$results = $this->solr->getFacets('biblio', ['subject_person_str_mv'], $query);
			if (!empty($results['subject_person_str_mv'])) {
				foreach ($results['subject_person_str_mv'] as $person=>$count) {
					if ($person<>$activePerson->solr_str) {
						$AP = personFromStr($person, 'as_topic', $count);
						$AP->bottomLink = $this->buildUri('search/results/1/r/'.$this->buffer->createFacetsCode($this->sql, ["author_facet:\"{$activePerson->solr_str}\"", "subject_person_str_mv:\"{$AP->solr_str}\""]));
						$AP->bottomStr = $this->transEsc('Go to bibliographic records');
						$heWroteAbout[] = $AP;
						}
					}
				}
			
			$query['q'] = ['field' => 'q', 'value' => 'subject_person_str_mv:"'.$activePerson->solr_str.'"'];
			$results = $this->solr->getFacets('biblio', ['author_facet'], $query);
			if (!empty($results['author_facet'])) {
				foreach ($results['author_facet'] as $person=>$count) {
					if ($person<>$activePerson->solr_str) {
						$AP = personFromStr($person, 'as_author', $count);
						$AP->bottomLink = $this->buildUri('search/results/1/r/'.$this->buffer->createFacetsCode($this->sql, ["subject_person_str_mv:\"{$activePerson->solr_str}\"", "author_facet:\"{$AP->solr_str}\""]));
						$AP->bottomStr = $this->transEsc('Go to bibliographic records');
						$theyWroteAbout[] = $AP;
						}
					}
				}
			
			
			
			$query['q'] = ['field' => 'q', 'value' => 'author_wiki:"'.$wikiIdInt.'"'];
			$query['limit'] = ['field' => 'facet.limit', 'value' => 6];
			$stats['author_wiki'] = $this->solr->getFacets('biblio', $statFields, $query);
			
			$query['q'] = ['field' => 'q', 'value' => 'coauthor_wiki:"'.$wikiIdInt.'"'];
			$stats['coauthor_wiki'] = $this->solr->getFacets('biblio', $statFields, $query);
			
			$query['q'] = ['field' => 'q', 'value' => 'subject_person_wiki:"'.$wikiIdInt.'"'];
			$stats['subject_person_wiki'] = $this->solr->getFacets('biblio', $statFields, $query);
			
				 
					
	
			$stat = $this->solr->getStats(
								'bibliocore',
								[$wikiIdInt], 
								['persons_wiki_str_mv'],
								$this->getIniParam('persons','statList','statFields')
								);	
			
			$renderer = 'wiki/fullcard-person.php';
			$params = [ 'photo'=>$photo, 'audio'=>$audio, 'stat'=>$stat, 'compareStats'=>$stats, 'activePerson'=>$activePerson, 'heWroteAbout' => $heWroteAbout, 'theyWroteAbout' => $theyWroteAbout];
			break;
	
	case 'institution' :

			$statFields = $this->getIniParam('institutions','statList','statFields');
			$activeInstitution = $this->wiki->getActivePersonValues();
			
			$stats = [];
			$relatedPersons = [];
			
			$query['q'] = ['field' => 'q', 'value' => 'corporate_wiki:"'.$wikiIdInt.'"'];
			$query['limit'] = ['field' => 'facet.limit', 'value' => 100];
			$query['sort'] = ['field' => 'facet.sort', 'value' => 'count'];
			$results = $this->solr->getFacets('biblio', ['persons_str_mv'], $query);
			if (!empty($results['persons_str_mv'])) {
				foreach ($results['persons_str_mv'] as $person=>$count) {
					$AP = personFromStr($person, 'totalSharedRecords', $count);
					$AP->bottomLink = $this->buildUri('search/results/1/r/'.$this->buffer->createFacetsCode($this->sql, ["corporate_wiki:\"{$wikiIdInt}\"", "persons_str_mv:\"{$AP->solr_str}\""]));
					$AP->bottomStr = $this->transEsc('Shared bibliographic records');
					$relatedPersons[] = $AP;
					}
				}
			
			$query['q'] = ['field' => 'q', 'value' => 'corporate_wiki:"'.$wikiIdInt.'"'];
			$query['limit'] = ['field' => 'facet.limit', 'value' => 6];
			$stats['corporate_wiki'] = $this->solr->getFacets('biblio', $statFields, $query);
					
	
			$stat = $this->solr->getStats(
								'bibliocore',
								[$wikiIdInt], 
								['corporate_wiki'],
								$this->getIniParam('persons','statList','statFields')
								);	
			
			$renderer = 'wiki/fullcard-institution.php';
			$params = [ 'photo'=>$photo, 'audio'=>$audio, 'stat'=>$stat, 'compareStats'=>$stats, 'activeInstitution'=>$activeInstitution, 'relatedPersons'=>$relatedPersons ];
			break;
			
	case 'place' : 
			
			$t = $this->psql->querySelect("SELECT name FROM places_wiki WHERE wiki='$wikiIdInt' ORDER BY subjecthits DESC;");
			if (is_array($t)) {
				foreach ($t as $line) 
					$biblioNames[] = $line['name'];
				}
			if (empty($biblioNames))
				$biblioNames[] = $this->wiki->get('labels');
			
			$stat = $this->solr->getStats(
							'bibliocore',
							[$wikiIdInt], 
							['geowiki_str_mv'],
							$this->getIniParam('places','statList','statFields')
							);
		
			$popPersons = $this->psql->querySelect("
						SELECT viaf_id, name, wikiq, year_born, year_death, place_born, place_death , rec_total, as_author, as_author2, as_topic, solr_str 
							FROM persons WHERE place_born = '$wikiIdInt' OR place_death = '$wikiIdInt' ORDER BY rec_total DESC LIMIT 9;
							");	
			
			$t = $this->psql->querySelect("SELECT count(*) FROM persons WHERE place_born = '$wikiIdInt' OR place_death = '$wikiIdInt';");	
			if (is_array($t)) {
				$popPersons['count'] = current($t);
				$popPersons['count']['wikiq'] = $wikiIdInt;
				}
			$renderer = 'wiki/fullcard-place.php';
			$params = [ 'photo'=>$photo, 'audio'=>$audio, 'stat'=>$stat, 'popPersons' => $popPersons ]; 
			break;
			
	default :
			$Tchecked = [];
			$allNames = $this->wiki->getAllNames();
			$names = explode(', ', $this->wiki->get('aliases'));
			$names[] = $this->wiki->get('labels');
			foreach ($names as $name) {
				$clearName = $this->solr->clearStr($name);
				if ($clearName<>'')
					$Tnames[$clearName] = $clearName;
				}
			$Tnames = [];
			if (!empty($allNames))
				foreach($allNames as $name) {
					$clearName = $this->solr->clearStr($name);
					if ($clearName<>'')
						$Tnames[$name] = $clearName;
					}
			$res = $this->solr->getFullList('topic');
			if (!empty ($res->results))
				foreach ($res->results as $word=>$count) {
					$clearWord = $this->solr->clearStr($word);
					if (in_array($clearWord, $Tnames) && (floatval($clearWord)==0)) {
						$Tchecked[$word]=$count;
						$Twords[$word] = $word;
						}
					}
			if (!empty($Twords))
				$stat = $this->solr->getStats(
								'bibliocore',
								$Twords, 
								['allfields'], 
								$this->getIniParam('persons','statList','statFields')
								);	
				else
				$stat = [];
			
			$renderer = 'wiki/fullcard-subject.php';
			$params = [ 'photo'=>$photo, 'audio'=>$audio, 'stat'=>$stat, 'names'=>$Tchecked, 'allNames'=>$allNames, 'res'=>$res];
			break;
	}


echo $this->render('head.php');
echo $this->render('core/header.php');
echo "<div class='main'>";
echo $this->render($renderer, $params); 
echo "</div>";
echo $this->render('core/footer.php');


?>


