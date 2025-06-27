<?php 
if (empty($this)) die;
$lookFor = $this->POST['sstring'] ?? '';
$lookForTable = explode(' ',$lookFor);

require_once('functions/class.helper.php');

$this->addClass('solr', 	new solr($this)); 
$this->addClass('helper', new helper()); 
if (!$this->isMobile()) {
$fieldsToSearch = [
	'author_facet', 
	'author2_facet', 
	'author_corporate', 
	'author_events_str_mv', 
	'title_ac', 
	'subjects_str_mv', 
	'persons_ac', 
	'places_ac', 
	'corporates_ac', 
	'magazines_ac',
	'events_ac'
	];
	
echo '<h4>'.$this->transEsc('or in a collection of your choice').': <small><a href="#" data-toggle="popover" 
				title="'.$this->transEsc('Expected number of results').'" 
				data-content="'.$this->transEsc('Click on the number to make the search take place in the selected collection.').'"><i class="ph ph-info"></i></a></small>
		</h4>';
#echo '<div class="home-page-suggestions">';

foreach ($this->configJson->settings->homePage->coresNames as $core=>$params) {
	
	$query = [];
	$query[] = ['field' => 'rows',	'value' => "0"];
	
	$query[] = $this->solr->lookFor($lookFor, 'allfields');
	$query[] = ['field' => 'facet',	'value' => "false"];
	$results = $this->solr->getQuery($core, $query); 
	$total = $this->solr->totalResults();
	
	#echo $this->helper->pre($query);
	if (!empty($lookFor))
		$link = $this->buildUrl('results/'.$params->url.'/', ['lookfor'=>$lookFor, 'type'=>'allfields']);
		else 
		$link = $this->buildUrl('results/'.$params->url.'/');	
	$this->addJS($q = "$('#count_$core').html('$total');");
	$this->addJS($q = "$('#link_$core').attr('href', '$link');");
	#echo '<a class="suggestionBox" href="'.$this->buildUrl('results/'.$params->url.'/', ['lookfor'=>$lookFor, 'type'=>'allfields']).'"><span class="number">'.$total.'</span></a> ';
	
	}
#echo '</div>';
}

/*
q=*:*&q.op=OR&rows=0&stats=true&stats.field=biblio_count&stats.facet=record_type
q=*:*&q.op=OR&rows=0&stats=true&stats.field=biblio_count
*/
?>

<script>
$(document).ready(function(){
  $('[data-toggle="popover"]').popover();
});
</script>