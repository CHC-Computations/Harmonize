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
$this->setTitle($title = $this->wiki->get('labels'));



echo $this->render('head.php');
echo $this->render('core/header.php');
echo "<div class='main'>";

echo $this->render('helpers/photo.php', ['photo'=>$photo, 'title'=>$title ]);
echo "</div>";


echo $this->render('core/footer.php');


?>


<script>

</script>