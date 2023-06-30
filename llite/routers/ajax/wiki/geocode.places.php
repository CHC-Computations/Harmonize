<?php 
if (empty($this)) die;
# echo "<pre>".print_R($this->routeParam,1)."</pre>";
require_once('functions/klasa.wikidata.php');

$this->addClass('solr', new solr($this->config));
$this->addClass('buffer', 	new marcBuffer()); 



$facetName = $this->routeParam[0];
$hitField = $this->routeParam[1];
$offset = $this->routeParam[2];
$step = $offset+1;
$totalResults = $this->routeParam[3];

# if ($offset == 0) $this->psql->query("TRUNCATE TABLE places_wiki;");

# $offset = 14246; 

$query['limit']=[
	'field' => 'facet.limit',
	'value' => 1
	];
$query['facet.sort']=[ 
	'field' => 'facet.sort',
	'value' => 'count'
	];
$query['facet.field']=[ 
	'field' => 'facet.field',
	'value' => $facetName
	];
$query['facet.offset']=[ 
	'field' => 'facet.offset',
	'value' => $offset
	];
$query['rows']=[ 
	'field' => 'rows',
	'value' => 0
	];

$results = $this->solr->getFacets('biblio', [$facetName], $query); 
$placeName = key($results[$facetName]);
$placeCount = current($results[$facetName]);

echo "<p></p>";
echo $this->helper->percent($step,$totalResults);
echo "<p></p>";
echo "Checking: $step. <b>".$placeName.'</b> <span class="badge">'.$placeCount.'</span><br/><br/>';

if ($placeName == '')
	$placeName = 'error';

$W = 'nie zapisuje';
$save = new stdClass;
$save->name = $placeName;
$save->$hitField = $placeCount;


$t = $this->psql->querySelect("SELECT * FROM places_wiki WHERE name={$this->psql->isNull($placeName)};");
if (is_array($t)) {
	$myRec = current($t);
	}
	
if (empty($myRec) or ($myRec['wiki'] == '')) {
	if (stristr($placeName, '(')) {
		$tmp = explode('(', $placeName);
		$sstring = 'inlabel:'.urlencode($this->solr->clearStr($tmp[0])); 
		} else 
		$sstring = 'inlabel:'.urlencode( $this->solr->clearStr($placeName) );
	echo "$sstring<br/>";
	$res = json_decode(file_get_contents($F = 'https://www.wikidata.org/w/api.php?action=query&format=json&list=search&srsearch='.$sstring.'&srlimit=6&srprop=size&formatversion=2'));
	$hit = false;
	
	if (!empty($res->batchcomplete) && ($res->query->searchinfo->totalhits>0)) {
		foreach ($res->query->search as $key=>$result) {
			
			$wikiRecord = $this->buffer->loadFromWikidata($result->title);
			$wikiClass  = new wikidata(json_decode($wikiRecord)); 
			
			echo "checkig result: {$result->title}";
			if (!empty($value = $wikiClass->getCoordinates('P625'))) { 
				$save->lon = str_replace(',','.',$value->longitude);
				$save->lat = str_replace(',','.',$value->latitude);
				$save->wiki = str_replace('Q', '', $result->title);
				$save->country = str_replace('Q', '', $wikiClass->getPropId('P17'));
				$save->continent = str_replace('Q', '', $wikiClass->getPropId('P30'));
				echo " - <b>OK</b> ".$save->lon.', '.$save->lat;
				$hit = true;
				break;
				}
			echo "<br/>";
			}
		
		$save->wikihits = $res->query->searchinfo->totalhits;
		} 
	}  
if (!empty($myRec) && (($myRec['wiki']<>'') & (($myRec['country']=='')or($myRec['continent']=='')))) {
	echo "looking for more info with Q$myRec[wiki]<br/>";
	$wikiRecord = $this->buffer->loadFromWikidata('Q'.$myRec['wiki']);
	$wikiClass = new wikidata(json_decode($wikiRecord)); 
	$save->country = str_replace('Q', '', $wikiClass->getPropId('P17'));
	$save->continent = str_replace('Q', '', $wikiClass->getPropId('P30'));
	}	
	




if (!empty($myRec)) {
	
	foreach ($save as $k=>$v) {
		$ch[] = $k.'='.$this->psql->isNull($v);
		}
	$W = $this->psql->query($Q = "UPDATE places_wiki SET ".implode(', ',$ch)." WHERE name={$this->psql->isNull($placeName)};");
	echo "<div style='display:block; position:fixed; bottom:200px;'>UPDATE</div>"; 
	} else {
	foreach ($save as $k=>$v) {
		$keys[$k] = $k;
		$vals[$k] = $this->psql->isNull($v);
		}
	$W = $this->psql->query($Q = "INSERT INTO places_wiki (".implode(',', $keys).") VALUES 
		(".implode(',', $vals).");");
	echo "<div style='display:block; position:fixed; bottom:200px;'>INSERT</div>";
	}


# echo "<pre>".print_r($F,1).'</pre>';
# echo "<pre>".print_r($res,1).'</pre>';


if ($step<$totalResults) {
	$OC = "page.ajax('apiCheckBox', 'wiki/geocode.places/$facetName/$hitField/$step/$totalResults');";	
	if ($W == 1) 
		$this->addJS ($OC);
		#echo '<button class="btn btn-succes" onClick="'.$OC.'">next</button>';
		else {
		echo '<button class="btn btn-succes" onClick="'.$OC.'">next</button>';
		echo "<div class='alert alert-info'>";				
		echo "$Q<br/>";
		echo "zapis: $W <br/>";
		echo '</div>';
		}
	}


?>