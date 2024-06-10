<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.persons.php');

$rec_id = str_replace('.html', '', $this->routeParam[1]);


$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 

$this->buffer->setSql($this->sql);
$marcJson = $this->buffer->getRecord('persons', $rec_id);

if (!empty($marcJson)) {
	$this->addClass('record', new marc21($marcJson));
	$this->setTitle( $this->transEsc("Person").": ".$this->record->getMainAuthor()['name'] );
	$this->record->id = str_replace('viaf_id', '', $rec_id);
	###########
	
	$rec = $marcJson;
	
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

	$stat = $this->solr->getPersonStats($rec_id, $this->record->getMainAuthor()['name'], $this->record->getMainAuthor()['d']);
	
	if (!empty($marcJson->LEADER)) 
		echo $this->render('persons/onCloud.php', ['coreFields'=>$coreFields, 'author'=>$coreFields, 'stat'=>$stat]);
	} else {
		
	$persJson = $this->buffer->loadPerson($rec_id);
	if (!empty($persJson)) {
		$this->addClass('person', new person($persJson));
		$stat = $this->solr->getPersonStats($rec_id, $this->person->getLName(), $this->person->getDateRange());
		$coreFields = $this->person->getCoreFields();
		echo $this->render('persons/personOnCloud.php', ['coreFields'=>$coreFields, 'stat'=>$stat]);
		}
	
	}
	

?>