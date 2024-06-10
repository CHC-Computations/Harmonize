<?php 
//if (empty($this)) die;

require_once('functions/class.helper.php');
require_once('functions/class.buffer.php');
require_once('functions/class.wikidata.php');
require_once('functions/class.wikidata.libri.php');
require_once('functions/class.solr.php');

$this->addClass('helper', 	new helper()); 
$this->addClass('buffer',	new buffer()); 
$this->addClass('solr',		new solr($this)); 

$wikiq = $this->routeParam[0];


$record = $this->solr->getWikiRecord('places', $wikiq);
$result = new wikiLibri($this->user->lang['userLang'], $record);

echo $this->render('wikiResults/resultBoxes/map-box.php', ['result' => $result]);

echo '????';
print_r($_POST);
?>