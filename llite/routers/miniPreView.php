<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.buffer.php');
require_once('functions/class.converter.php');
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.bookcart.php');


$x = count($this->routeParam)-1;
$currentCore = 'biblio';

$this->addClass('solr', new solr($this));
$this->addClass('convert', 	new converter()); 
$this->addClass('bookcart',	new bookcart());
$this->addClass('buffer',	new buffer());


$current_view = $this->getUserParam($currentCore.':view') ?? 'list';
$recFile = $this->routeParam[0];
$recId = str_replace('.html', '', $recFile);

$result = $this->solr->getRecord($currentCore, $recId);
if (!empty($result->id)) {
	
	$this->addClass('record', new bibliographicRecord($result, $this->convert->mrk2json($result->fullrecord)));
	echo $this->render('search/results/'.$current_view.'.php', ['result'=>$result, 'record'=>json_decode($result->relations)] );
	} 