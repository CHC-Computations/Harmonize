<?php 
if (empty($this)) die;
require_once('functions/class.persons.2.php');
require_once('functions/class.wikidata.php');

$this->setTitle($this->transEsc('Persons'));

$this->addClass('buffer', 	new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('persons', 	new persons($this->config)); 

$wikiId = 'Q'.$this->routeParam[0];
$activeCorpo = new wikidata($wikiId); 
$activeCorpo->setUserLang($this->user->lang['userLang']);

if (!empty($this->POST['pdata'])) {
	$activeCorpo->bottomLink = $this->POST['pdata']['bottomLink'];
	$activeCorpo->bottomStr = $this->POST['pdata']['bottomStr'];
	$activeCorpo->bottomCount = $this->POST['pdata']['bottomCount'];
	}

$photo = $this->buffer->loadWikiMediaUrl($activeCorpo->getStrVal('P18'));

echo $this->render('corporates/results/box-wiki.php',['activeCorpo'=>$activeCorpo, 'photo'=>$photo]);

?>