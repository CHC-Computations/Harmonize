<?php 
if (empty($this)) die;
require_once('functions/klasa.helper.php');
require_once('functions/klasa.buffer.php');
require_once('functions/klasa.wikidata.php');
require_once('functions/klasa.maps.php');
$this->addClass('helper', new helper()); 
$this->addClass('maps',	new maps()); 
# $this->addClass('buffer',	new marcbuffer()); 


$bufferFileName = './files/maps/'.$this->langCode.'-fullMap.js';

# echo $this->helper->pre($this->GET);
# echo $this->helper->pre($this->POST);

if ($this->POST['total'] == $this->POST['visible']) die;
if (($this->POST['zoomOld']<3)&($this->POST['zoom']<3)) die;


// map.removeLayer(marker)
echo "<script>
  map.eachLayer( function(layer) {
    if(layer instanceof L.Marker) {
	  map.removeLayer(layer)
      }
    });

</script>
";





$WAR = "lon IS NOT NULL AND lat IS NOT NULL AND a.wikiq IS NOT NULL";
if (!empty ($this->GET['lookfor'])) {
	$sstring = $this->urlName2($this->GET['lookfor']);
	$WAR .= " AND sstring ILIKE '%{$sstring}%'";
	}
$WAR.=" AND lat<'{$this->POST['bN']}' AND lat>'{$this->POST['bS']}' AND lon<'{$this->POST['bE']}' AND lon>'{$this->POST['bW']}' ";
	###########################################################################################################################################
	##
	##										FIRST STEP
	##
	###########################################################################################################################################
	
$t = $this->psql->querySelect($Q = "SELECT count(*) as recsum, max(subjecthits+pubplacehits+personhits) as recmax FROM places_on_map a WHERE $WAR;");
#echo "$Q";
if (is_array($t)) {
	$res = current($t);
	$recSum = $res['recsum'];
	$recMax = $res['recmax'];
		
	###########################################################################################################################################
	##
	##										Drawing points 
	##
	###########################################################################################################################################
	$step = 100;
	
	$colorTop = '#f3984e';
	$colorMiddle = '#f0cc41';
	$colorBottom = '#88d167';
	
	// meybe usefull? https://github.com/moravcik/Leaflet.TextIcon
	
	$PlaceCircleBasic = "color: '#76679B', fillColor: '#5F3D8D', weight: 2, fillOpacity: 0.5";
	$PlaceCircleHover = "color: 'red', fillColor: 'yellow', weight: 1, fillOpacity: 0.5";
	$emptyStr = ''; //$this->transEsc('Point on map to see details');
		
	$t = $this->psql->querySelect($Q = "SELECT DISTINCT a.*, (subjecthits+pubplacehits+personhits) as totalhits, b.value as wikiname 
				FROM places_on_map a
				LEFT JOIN wiki_labels b ON a.wikiq=b.wikiq AND b.lang='{$this->userLang}'
				WHERE $WAR ORDER BY totalhits DESC LIMIT $step;"); 
	if (is_array($t)) {
		$lp = 0;
		$partCount = count($t);
		$last = end($t);
		$recMin = $last['totalhits'];
		
		$colStep = ($recMax-$recMin)/3;
		$col1 = $recMax-$colStep;
		$col2 = $recMin+$colStep;
		
				
		foreach ($t as $res) {
			
			############################################################ adding point to map
			$lp++;
			$place = $res;
			$pjs = [];
			
			$icon = 'SBottom';
			if ($lp<10) { $label = 'Bottom'; $icon = 'None'; }
			if ($lp<6) { $label = 'Middle'; $icon = 'None'; }
			if ($lp<3) { $label = 'Top'; $icon = 'None'; }
			
			$tlat[] = $place['lat'];
			$tlon[] = $place['lon'];
			
			if (!empty($res['wikiname']))
				$wikiName = $res['wikiname'];
				else
				$wikiName = $res['name'];	
				#$wikiName = $this->helper->formatWiki($res['wikiq']);
				
				
			$place['link'] = "<h3><a href='{$this->buildUrl('wiki/record/Q'.$res['wikiq'])}'>{$wikiName}</a></h3>";
			#if (!empty($res['names'])) 	$place['link'] .= $this->transEsc('Other names: ').$res['names'].'<br/><br/>';
			$place['link'] .= $this->transEsc('Exists as').':<br/>';
			$place['link'] .= $this->transEsc('Subject place').': '.$res['subjecthits'].'<br/>';
			$place['link'] .= $this->transEsc('Publication place').': '.$res['pubplacehits'].'<br/>';
			$place['link'] .= $this->transEsc('Person place').': '.$res['personhits'].'<br/>';
			$key = $res['wikiq'];
			
			$pjs[] = "var smarker_$res[wikiq] = L.marker([$place[lat], $place[lon]], {icon: Circle$icon }); ";
			$pjs[] = "smarker_$res[wikiq].addTo(map);";
			if ($lp<10) $pjs[] = "smarker_$res[wikiq].bindTooltip('".$this->helper->badgeFormat($place['totalhits'])."' , {permanent: true, direction: 'center', className: 'label-{$label}' });";
			$pjs[] = "smarker_$res[wikiq].on({click: function () { $('#poitedDet').html(\"$place[link]\");	}});";
			$pjs[] = "smarker_$res[wikiq].bindPopup(\"{$place['link']}\")";
			$js[] = implode("\n", $pjs);
			}
		
		$this->addJS(implode("\n", $js));	

		echo $this->helper->numberFormat($lp);
		}
		
		
		
	} else {
	echo $this->tranEsc('No results');	
	}
		
	
	


?>