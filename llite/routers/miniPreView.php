<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.converter.php');
$x = count($this->routeParam)-1;

$tmp = explode('.', $this->routeParam[$x]);

$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('convert', 	new converter()); 

$rec_id = current($tmp);
$format = end($tmp);

$rec_id=str_replace('.html', '', $this->routeParam[$x]);
$record = $this->solr->getRecord('biblio', $rec_id);
if (!empty($record->id)) {
	#$marcJson = $this->buffer->getJsonRecord($record->id, $record->fullrecord);
	$marcJson = $this->convert->mrk2json($record->fullrecord);
	$this->addClass('record', new marc21($marcJson));
		
	echo $this->render('record/mini/preView.php', ['result'=>$record, 'marcJson' => $marcJson]);
	
	} else 
	echo "error!";