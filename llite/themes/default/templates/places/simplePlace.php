<?php 

// $this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->author_facet_s\" OR topic_person_str_mv:\"$stat->topic_person_str_mv\""])
	$stats = '';
	

	if (!empty($place['other_names']) && is_array($place['other_names'])) {
		
		$stats .= '<div class="detailsview-pack"><h4 class="detailsview-h">'.$this->transEsc('The same place appears in bibliographic records under different names').':</h4>';
		foreach ($place['other_names'] as $placeName) {
			$stats.= $this->render('record/place-link-simple.php', ['place'=>$placeName]);
			}
		$stats .= '</div>';
		}
		
	if (!empty($stat->facets['geographic_facet']) && (count($stat->facets['geographic_facet'])>1)) {
		$stats.= '<div class="detailsview-pack"><h4 class="detailsview-h">'.$this->transEsc('Other places that appear most frequently in bibliographic records, along with the place you are viewing').':</h4>';
		foreach ($stat->facets['geographic_facet'] as $placeName=>$placeCount)
			if ($placeName!==$place['name'])
				$stats .= $this->render('record/place-link-simple.php', ['place'=>$placeName]);
		$stats .= '</div>';
		
		}	
		
	if (!empty($neighborhood))  {
		$stats.= '<div class="detailsview-pack"><h4 class="detailsview-h">'.$this->transEsc('Nearby places').':</h4>';
		foreach ($neighborhood as $placeName)
			if ($placeName!==$place['name'])
				$stats .= $this->render('record/place-link-simple.php', ['place'=>$placeName]);
		$stats .= '</div>';
		
		}	
	if ($stats<>'') 
		$stats = '
			<div class="row">
			<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-body">
					'.$stats.'
				</div>
			</div>
			</div>
			</div>';
		
	$PRE = "<pre>".print_R($stat,1)."</pre>";
	
	$statBoxes = $this->getIniArray('place-card', 'statBoxes');
	$authorFormat = $this->getIniArray('facets', 'facetOptions','authorFormats');
	
	$stats .= '<div class="statBox">';
	$Llp = 0;
	foreach ($statBoxes as $facet=>$facetName) {
		$nstat = [];
		$lp = 0;
		if (!empty($stat->facets[$facet])) {
			foreach ($stat->facets[$facet] as $k=>$v) {
				$lp++;
				$index = $lp+$Llp;

				$key = $this->buffer->createFacetsCode($this->sql, ["$facet:\"$k\""]);
				$link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, ['lookfor'=>$stat->name, 'type'=>'AllFields']);
				
				$tk = $k;
				if (in_array($facet, $authorFormat))
					$tk = $this->helper->authorFormat($k);
				
				if ($v>0)
					$nstat[$index] = [
						'label' => $this->transEsc($tk),
						'label_o' => $k,
						'count' => $v,
						'link' 	=> $link,
						'color' => $this->helper->getGraphColor($lp),
						'index' => $index,
						];
				}
			}
		$Llp = $Llp+$lp;
		$stats .= $this->helper->drawStatBox($this->transEsc($facetName), $nstat);
		}
		
	
		
	$stats .="</div>";
	

$Tmap[] = $this->maps->addPoint($place);


?>

<div class="graybox">
  <div class="infopage">
    <div class="infopage-header">
		<h1 property="name"><?= $place['name'] ?> <small>(<?= $place['display_name'] ?>)</small></h1>
		
	</div>
	<div class="person-record">
	
		<?= $this->maps->drawWorldMapMap($Tmap) ?>
		<small><?= $this->transEsc("The point on the map is automatically selected based on the geonames.org or nominatim.openstreetmap.org geocoding. The proper location may be different.") ?></small>
		<?= $stats ?>
	</div>	
	
  </div>
</div>

