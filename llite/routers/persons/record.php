<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.persons.php');
require_once('functions/class.maps.php');

$marcRecord = false;
$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->buffer->setSql($this->sql);
$export = $this->getConfig('person-card');
	
if (!empty($this->routeParam[1])) {
	$rec_id = str_replace('.html', '', $this->routeParam[1]);

	$marcJson = $this->buffer->getRecord('persons', $rec_id);
	$persJson = $this->buffer->loadPerson($rec_id);

	$coreFields = [];

	

	if (!empty($marcJson)) {
		
		
		$marcRecord = true;
		$this->addClass('record', new marc21($marcJson));
		$this->addClass('person', new person($persJson));
		$this->setTitle( $this->transEsc("Person").": ".$this->record->getMainAuthor()['name'] );
		$this->record->id = str_replace('viaf_id', '', $rec_id);
		###########
		
		$rec = $marcJson;
		$Tmap = [];
		if (!empty($rec = $this->record->getDateOfBrith()))
			$coreFields['dateOfBirth'] = [
					'label'=>$this->transEsc('Date of Birth'), 
					'content'=>$rec
					];

		if (!empty($rec = $this->record->getPlaceOfBrith())) {
			$gps = $this->buffer->getGPS($rec);
			$nrec = array_merge($rec, $gps);
			$nrec['map_label'] = $this->transEsc('Place of Birth').': '.$nrec['name'];
			$Tmap[] = $nrec;
			$coreFields['placeOfBirth'] = [
					'label'=>$this->transEsc('Place of Birth'), 
					'content'=>$this->render('record/place-link.php', ['gps' => $nrec] )
					];
			}

		if (!empty($rec = $this->record->getDateOfDeath()))
			$coreFields['dateOfDeath'] = [
					'label'=>$this->transEsc('Date of Death'), 
					'content'=>$rec
					];

		if (!empty($rec = $this->record->getPlaceOfDeath())) {
			$gps = $this->buffer->getGPS($rec);
			
			if (is_array($gps)) {
				$nrec = array_merge($rec, $gps);
				$nrec['map_label'] = $this->transEsc('Place of Death').': '.$nrec['name'];
				$Tmap[] = $nrec;
				
				$link = $this->render('record/place-link.php', ['gps' => $nrec] );
				} else 
				$link = $rec['name'];
			
			$coreFields['placeOfDeath'] = [
					'label'=>$this->transEsc('Place of Death'), 
					'content' => $link
					];
			}
			
			
		if (!empty($recs = $this->record->getRelationship())) {
			foreach ($recs as $rec) {
				$gps = $this->buffer->getGPS($rec);
				if (is_array($gps)) {
					$gps['name'] = $rec['f'];
					$Tmap[] = $gps;
					$link = "<a href='$rec[1]'>$rec[f]</a> <span class='label label-info'>GPS: $gps[lat],$gps[lon]</span>";
					} else 
					$link = $rec['f'];
				$coreFields[$rec['i']] = [
						'label'=>$this->transEsc($rec['i']), 
						'content' => $link,
						];
				}
			}
			
			
		if (!empty($rec = $this->record->getCitizenship())) {
			$coreFields['citizenship'] = [
					'label'=>$this->transEsc('Citizenship'), 
					'content' => $this->helper->list($rec)
					];
			}

		if (!empty($rec = $this->record->getOccupation())) {
			$coreFields['occupation'] = [
					'label'=>$this->transEsc('Occupation'), 
					'content' => $this->helper->list($rec)
					];
			}

		if (!empty($rec = $this->record->getGender())) {
			$coreFields['gender'] = [
					'label'=>$this->transEsc('Gender'), 
					'content' => $this->helper->list($rec)
					];
			}

		if (!empty($rec = $this->record->getLanguages())) {
			$coreFields['languages'] = [
					'label'=>$this->transEsc('Languages spoken'), 
					'content' => $this->helper->list($rec)
					];
			}
			
		if (!empty($rec = $this->record->getRelations())) {
			$coreFields['relations'] = [
					'label'=>$this->transEsc('Relations'), 
					'content' => $this->helper->list($rec)
					];
			}

		if (!empty($rec = $this->record->getSources())) {
			$coreFields['sources'] = [
					'label'=>$this->transEsc('Source of information'), 
					'content' => $this->helper->list($rec)
					];
			}

		
		###########
		$stat = $this->solr->getPersonStats($rec_id, $this->record->getMainAuthor()['name'], $this->record->getMainAuthor()['d']);
		
		
		} else if (!empty($persJson)) {
		####################################################################################
		##
		####################################################################################
		$this->addClass('person', new person($persJson));
		$this->setTitle( $this->transEsc("Person").": ".$this->person->getName() );
		
		$coreFields = $this->person->getCoreFields();
		$Tmap = [];
		$stat = $this->solr->getPersonStats($rec_id, $this->person->getLName(), $this->person->getDateRange());
		
		if (!empty($stat->facets['geographic_facet']))
			foreach ($stat->facets['geographic_facet'] as $placeName=>$placeCount) {
				$place = $this->buffer->getPlaceParams($placeName);
				$Tmap[] = $this->maps->addPoint($place);
				#echo "$placeName<pre>".print_r($place,1).'</pre>';
				}

		#echo "STAT:<pre>".print_R($stat,1)."</pre>";
		#echo "<prE>".print_R($persJson,1)."</prE>";
		$marcJson = $persJson;
		} else {
		
		
		$marcJson = new stdclass;
		$marcJson->LEADER = null;
		$marcJson->id = $rec_id;
		$this->setTitle( $this->transEsc("Person unknown") );
		$stat = [];
		
		}
		
	} else {
	$marcJson = $this->GET;
	
	$stat = $this->solr->getPersonStatsNoID($this->getParam('GET','name'), $this->getParam('GET','date'));
	$this->setTitle( $this->transEsc("No personal details").': '.$this->getParam('GET','name') );
	}
?>

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>

<div class='main'>
	<?php 
	if (!empty($persJson))
			echo $this->render('persons/personCore.php', ['coreFields'=>$coreFields, 'Tmap'=>$Tmap, 'stat'=>$stat ]);
		else if ($marcRecord) 
			echo $this->render('persons/core.php', ['coreFields'=>$coreFields, 'Tmap'=>$Tmap, 'stat'=>$stat, 'rec'=>$persJson]);
			else 
			echo $this->render('persons/person-unknown.php', ['rec'=>$marcJson, 'stat'=>$stat ]);	
	?>
	
</div>

<?= $this->render('core/footer.php') ?>


