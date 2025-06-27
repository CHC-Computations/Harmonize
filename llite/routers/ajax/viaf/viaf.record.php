<?php
require_once('functions/class.viafSearcher.php');
$this->addClass('solr', 		new solr($this)); 
$this->addClass('viafSearcher', new viafSearcher($this)); 

$viaf = $this->routeParam[0];

$labels = $this->viafSearcher->getLabels($viaf);



echo '<br/><h4>'.$this->transEsc('Viaf labels').':</h4>';
echo '<div class="list-group">';
if (!empty($labels)) {
	foreach ($labels as $label) {
		$label = (object)$label;
		echo '<a class="list-group-item">'.$label->label.'<span class="badge">'.$this->helper->numberFormat($label->count).'</span></a>';
		}
	}
echo '</div>';	

echo '<small>'.$this->transEsc($this->viafSearcher->dataOrigin.' data source').'.</small>';
$this->solr->curlCommit('persons');	

?>