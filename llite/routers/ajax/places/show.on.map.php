<?php
if (empty($this)) die;
require_once('functions/klasa.helper.php');
require_once('functions/klasa.places.php');

$this->addClass('buffer', new marcBuffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('places', 	new places($this->sql)); 
$this->buffer->setSql($this->sql);
$this->places->baseURL($this->baseURL('places/record'));

$placesList = $this->places->getFullList();
$lp = 0;
echo "<script> 
		var myIcon = L.icon({
				iconUrl: 'https://zasoby.kominkowo.pl/images/maps/po_blue.png',
				iconSize: [7, 7],
				iconAnchor: [3, 3],
				popupAnchor: [0, -3]
			});
		</script>";
foreach ($placesList as $k=>$place) {
	$lp++;
	$key = $place['lat'].$place['lon'];
	$Tpoints[$key] = $place;
	$dn = explode(', ', $place['display_name']);
	$sortdn = array_reverse($dn);
	$deep = 0;
	$keys = [];
	foreach ($sortdn as $agregateName) {
		$keys[$deep] = $agregateName;
		switch ($deep) {
			case '0' : $Tagr[$keys[0]]['__places'][$key][]=$place; break;
			case '1' : $Tagr[$keys[0]][$keys[1]]['__places'][$key][]=$place; break;
			case '2' : $Tagr[$keys[0]][$keys[1]][$keys[2]]['__places'][$key][]=$place; break;
			case '3' : $Tagr[$keys[0]][$keys[1]][$keys[2]][$keys[3]]['__places'][$key][]=$place; break;
			}
		$deep++;
		
		}
	$JS[$k] = $this->places->prepareMapPoint($place);
	#if ($lp>10) break;
	}

?>
<script><?= implode("\n", $JS) ?></script>
<small><?=$this->transEsc("Places names")?>: <b><?= count($placesList) ?></b>.</small>
<small><?=$this->transEsc("Points on map")?>: <b><?= count($Tpoints) ?></b>.</small>
<small><?=$this->transEsc("Agregated areas")?>: <b><?= count($Tagr) ?></b></small>

<?php 
ksort($Tagr);
echo '<div style="margin:30px;">';
echo drawHierarchy($Tagr);
echo '</div>';
#echo '<pre>'.print_R($Tagr,1).'</pre>';



	function drawHierarchy($table) {
		$res = '<ol>';
		foreach ($table as $groupName=>$v) {
			
			$points = '';
			if (!empty($v['__places'])) {
				$key2 = uniqid();
				$points .= '<button data-toggle="collapse" data-target="#box_'.$key2.'" class="toolbar-btn"><i class="ph-map-pin-line-bold"></i> <span class="badge">'.count($v['__places']).'</span></button>';
				$points .= drawPlaces($v['__places'],$key2);
				unset($v['__places']);
				}
			
			$key = uniqid();
			if (is_array($v) && (count($v)>0)) {
				$res .= '<li><button data-toggle="collapse" data-target="#box_'.$key.'" class="toolbar-btn">'.$groupName.' <i class="ph-caret-down-bold"></i></button>'.$points;
				$res .= drawHierarchy2($v,$key);
				} else {
				$res .= '<li><span class="toolbar-btn">'.$groupName.'</span>'.$points;	
				}
			$res .= '</li>';
			}
		$res.="</ol>";
		return $res;
		}
		
	function drawHierarchy2($table,$key) {
		$res = '';
		$res .= '<div class="collapse" id="box_'.$key.'"><ol>';
		foreach ($table as $groupName=>$v) {
			
			$points = '';
			if (!empty($v['__places'])) {
				$key2 = uniqid();
				$points .= '<button data-toggle="collapse" data-target="#box_'.$key2.'" class="toolbar-btn""><i class="ph-map-pin-line-bold"></i> <span class="badge">'.count($v['__places']).'</span></button>';
				$points .= drawPlaces($v['__places'],$key2);
				unset($v['__places']);
				}
			
			$key2 = uniqid();
			
			if (is_array($v) && (count($v)>0)) {
				$res .= '<li><button data-toggle="collapse" data-target="#box_'.$key2.'" class="toolbar-btn"">'.$groupName.' <i class="ph-caret-down-bold"></i></button>'.$points;
				$res .= drawHierarchy2($v,$key2);
				} else {
				$res .= '<li><span class="toolbar-btn">'.$groupName.'</span>'.$points;	
				}
			$res .= '</li>';
			}
		
		$res .='</ol></div>';
		return $res;
		}	

	function drawPlaces($table,$key) {
		$res = '<div class="collapse" id="box_'.$key.'">';
		foreach ($table as $st) {
			$res .= '<i class="ph-map-pin-bold"></i> ';
			foreach ($st as $place) {
				$res .= '<a href="http://testlibri.ucl.cas.cz/lite/pl/places/record/?place='.urlencode($place['name']).'" title="">'.$place['name'].'</a>, ';
				}
			$res .= '<br/>';
			}
		$res .='</div>';
		return $res;
		}	
	
?>