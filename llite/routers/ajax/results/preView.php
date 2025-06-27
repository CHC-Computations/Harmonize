<?php 
if (empty($this)) die;
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.helper.php');
require_once('functions/class.converter.php');
require_once('functions/class.maps.php');
require_once('functions/class.wikidata.php');
require_once('./functions/class.bookcart.php');



$recordCalled = $fn = end($this->routeParam);
$tmp = explode('.', $recordCalled);
$format = end($tmp);
$x = count($tmp)-1;
unset($tmp[$x]);
$rec_id = implode('.',$tmp);



$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->addClass('convert', 	new converter()); 
$this->addClass('wikiData', new wikiData($this)); 
$this->addClass('bookcart', new bookcart);

$solrRecord = $this->solr->getRecord('biblio', $rec_id);

			
if (!empty($solrRecord->id)) {
	$marcJson = $this->convert->mrk2json($solrRecord->fullrecord);
	$this->addClass('record', new bibliographicRecord($solrRecord, $marcJson));
	$title = $this->record->getTitle();
	echo $this->render('record/inmodal/core.php', ['record' => $this->record ]);
	} else {
	$title = $this->transEsc("No data");
	echo $this->render('record/no-core.php', ['rec'=>$rec_id, 'Tmap'=>$Tmap]);
	}
	
$this->addJS("$('#inModalTitle').html('$title');");
?>