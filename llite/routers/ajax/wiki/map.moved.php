<?php 
if (empty($this)) die;


require_once('functions/class.helper.php');
require_once('functions/class.buffer.php');
require_once('functions/class.wikidata.php');
require_once('functions/class.wikidata.libri.php');
require_once('functions/class.maps.php');
require_once('functions/class.solr.php');

$this->addClass('helper', 	new helper()); 
$this->addClass('maps',		new maps()); 
$this->addClass('buffer',	new buffer()); 
$this->addClass('solr',		new solr($this)); 


#echo $this->helper->pre($this->POST);
#echo $this->helper->pre($this->GET);

# if (!empty($this->POST['total']) && !empty($this->POST['visible']) && ($this->POST['total'] == $this->POST['visible'])) die; // to ma sens tylko jeśli first.run rysuje punkty!
# if (!empty($this->POST['zoomOld']) && ($this->POST['zoomOld']<3)&($this->POST['zoom']<3)) die;

$currentCore = 'places';
echo "<script> map.eachLayer( function(layer) {if(layer instanceof L.Marker) {map.removeLayer(layer)}});</script>";

$lookfor = $this->postParam('lookfor');
if (empty($lookFor) && !empty($this->GET['lookfor'])) {
	$lookfor = $this->GET['lookfor'];
	$query['q']= $this->solr->lookFor($lookfor);
	} else 
	$query['q']=[ 
			'field' => 'q',
			'value' => '*:*'
			];
$query['sort']=[ 
		'field' => 'sort',
		'value' => 'biblio_count desc'
		];
$query[] = [
		'field' => 'q.op', 
		'value' => 'OR'	
		];

if (!empty($this->POST['bE'])) {
	// map moved run
	$firstRun = false;
	$lon = $lat = [];
	$lon[] = $this->POST['bE'];
	$lon[] = $this->POST['bW'];
	$lat[] = $this->POST['bS'];
	$lat[] = $this->POST['bN'];
	sort($lon);
	sort($lat);
	$query[] = [
				'field' => 'fq', 
				'value' => 'longitiude:['.implode(' TO ',$lon).']'	
				];
	$query[] = [
				'field' => 'fq', 
				'value' => 'latitiude:['.implode(' TO ',$lat).']'	
				];
	}  
$query['rows']=[ 
		'field' => 'rows',
		'value' => 50
		];

$times = [];

$results = $this->solr->getQuery($currentCore, $query); 
$results = $this->solr->resultsList();
$facets = $this->solr->facetsList();
$totalResults = $recSum = $this->solr->totalResults();

if ($totalResults>0) {
	
	$first = current($results);
	$recMax = $first->biblio_count;
	$last = end($results);
	$recMin = $last->biblio_count;
	
	echo $this->transEsc('Showing results').': <strong>'.$this->solr->visibleResults().'</strong>';
	$this->addJS('$("#visibleResults").val("'.$this->solr->visibleResults().'")');
	###########################################################################################################################################
	##
	##										Drawing points 
	##
	###########################################################################################################################################
	
	$colorTop = '#f3984e';
	$colorMiddle = '#f0cc41';
	$colorBottom = '#88d167';
	
	// meybe usefull? https://github.com/moravcik/Leaflet.TextIcon
	
	$PlaceCircleBasic = "color: '#76679B', fillColor: '#5F3D8D', weight: 2, fillOpacity: 0.5";
	$PlaceCircleHover = "color: 'red', fillColor: 'yellow', weight: 1, fillOpacity: 0.5";
	$emptyStr = ''; //$this->transEsc('Point on map to see details');
	
	$lp = 0;
	foreach ($results as $place) {
		$lp++;
		$pjs = [];
		
		$icon = 'SBottom';
		if ($lp<10) { $label = 'Bottom'; $icon = 'None'; }
		if ($lp<6) { $label = 'Middle'; $icon = 'None'; }
		if ($lp<3) { $label = 'Top'; $icon = 'None'; }
		
		$tlat[] = $place->latitiude;
		$tlon[] = $place->longitiude;
		$key = $place->wikiq;
		$placeWiki = new wikiLibri($this->userLang, $place);
		$wikiName = $placeWiki->getStr('labels');
		if (empty($wikiName))
			$wikiName = $place->wikiq;
		
		$place->link = "<div id='placeBox_{$key}' class='mapPlaceBox'>";
		$place->link .= "<h3><a href='{$this->buildUrl('wiki/record/'.$place->wikiq)}'>{$wikiName}</a></h3>";
		$place->link .= '<p>'.$placeWiki->getStr('descriptions').'</p>';
		$place->link .= "</div>";
		
		$pjs[] = "var smarker_$key = L.marker([$place->latitiude, $place->longitiude], {icon: Circle$icon }); ";
		$pjs[] = "smarker_$key.addTo(map);";
		if ($lp<10) $pjs[] = "smarker_$key.bindTooltip('".$this->helper->badgeFormat($place->biblio_count)."' , {permanent: true, direction: 'center', className: 'label-{$label}' });";
		$pjs[] = "smarker_$key.on({click: function () { results.maps.currentPlace('{$place->wikiq}')}});";
		$pjs[] = "smarker_$key.bindPopup(\"{$place->link}\")";
		
		$js[] = implode("\n", $pjs);
		}
	
	
	
	$this->addJS(implode("\n", $js));	

	} else {
	echo $this->transEsc('No results');	
	}
	
	
	
#echo $this->helper->pre($this->GET);
#echo $this->helper->pre($this->POST);
#echo $this->helper->pre($this->routeParam);
#echo '<br/><small>map moved!<br/>'.date(DATE_ATOM).'</small><br/>';

$this->addJS('console.log("'.date("Y-m-d H:i:s.U").'");');
?>