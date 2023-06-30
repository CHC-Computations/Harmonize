<?php 
if (empty($this)) die;
require_once('functions/klasa.persons.2.php');
require_once('functions/klasa.wikidata.php');

$this->setTitle($this->transEsc('Persons'));

$this->addClass('buffer', 	new marcBuffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('persons', 	new persons($this->config)); 

$this->persons->register('psql', $this->psql);
$this->buffer->setSql($this->sql);

if (empty($this->POST['pdata'])) {
	$wikiIDint = $this->routeParam[0];
	$t = $this->psql->querySelect("SELECT * FROM persons WHERE wikiq={$this->psql->isNull($wikiIDint)};");
	if (is_array($t))
		$activePerson = (object)current($t);
		else 
		echo $this->transEsc("Error while reading person");	
	} else 
	$activePerson = (object)$this->POST['pdata'];

#echo $this->helper->pre($this->POST['pdata']);
	

$wikiId = 'Q'.$activePerson->wikiq;
$activePerson->wiki = new wikidata($wikiId); 
$activePerson->wiki->setUserLang($this->user->lang['userLang']);

#$photo = $this->buffer->loadMediaFromWikidata($wikiId);
$photo = $this->buffer->loadWikiMediaUrl($activePerson->wiki->getStrVal('P18'));

echo $this->render('persons/results/list-wiki.php',['activePerson'=>$activePerson, 'photo'=>$photo]);
#$this->addJS("results.personBox2Class('{$activePerson->field}');");

?>