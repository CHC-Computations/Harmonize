<?php
if (empty($this)) die;
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.helper.php');
require_once('functions/class.converter.php');
require_once('functions/class.maps.php');
require_once('functions/class.wikidata.php');
require_once('./functions/class.bookcart.php');


$id = $this->routeParam[0];


$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->addClass('convert', 	new converter()); 
$this->addClass('wikiData', new wikiData($this)); 
$this->addClass('bookcart', new bookcart);

$solrRecord = $this->solr->getRecord('biblio', $id);

if (!empty($solrRecord->id)) {
	if (!empty($solrRecord->fullrecord) && ($solrRecord->record_format == 'mrk')) {
		$marcJson = $this->convert->mrk2json($solrRecord->fullrecord);
		$this->addClass('record', new bibliographicRecord($solrRecord, $marcJson));
		} else 
		$this->addClass('record', new bibliographicRecord($solrRecord));
	echo $this->render('record/inmodal/cite.php', ['rec'=>$solrRecord]);
					
	}


?>