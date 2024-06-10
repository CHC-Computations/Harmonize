<?php 
if (empty($this)) die;
require_once('functions/class.forms.php');

$this->addClass('forms', 	new forms($this)); 
$this->addClass('solr', 	new solr($this)); 

$this->forms->values($this->GET);
$facets = $this->getConfig('search');

require_once('functions/class.helper.php');
$this->addClass('helper', new helper()); 

echo "<div style=''>";
echo $this->render('searchBoxes/biblio-searchbox.php');

echo "</div>";

$query = [];
$query[] = ['field' => 'q',	'value' => "*:*"];
$query[] = ['field' => 'rows',	'value' => "0"];
$query[] = ['field' => 'stats',	'value' => "true"];
$query[] = ['field' => 'stats.field',	'value' => "biblio_count"];
$query[] = ['field' => 'stats.facet',	'value' => "record_type"];
$results = $this->solr->getQuery('orphans', $query); 
$total = $this->solr->totalResults();
if (!empty($this->solr->stats->stats_fields->biblio_count->facets->record_type))
	foreach ($this->solr->stats->stats_fields->biblio_count->facets->record_type as $key => $values) {
		$nkey = $key.'s';
		$orphans[$nkey] = $values->sum;
		}
#echo $this->helper->pre($results);



echo '<div class="home-page-menu">';
foreach ($this->configJson->settings->homePage->coresNames as $core=>$params) {
	$query = [];
	$query[] = ['field' => 'q',	'value' => "*:*"];
	$query[] = ['field' => 'rows',	'value' => "0"];
	if ($core!='biblio') {
		$query[] = ['field' => 'stats',	'value' => "true"];
		$query[] = ['field' => 'stats.field',	'value' => "biblio_count"];
		} else {
		$query[] = ['field' => 'facet', 'value' => "true"];	
		$query[] = ['field' => 'facet.field', 'value' => "record_contains"];	
		$query[] = ['field' => 'facet.limit', 'value' => "999999"];	
		}

	$total = 0;
	$results = $this->solr->getQuery($core, $query); 
	$total = $this->solr->totalResults();
	$totalWikiBiblioRec = $this->solr->stats->stats_fields->biblio_count->sum ?? 0;
	
	if ($total>0) {
		echo '<a class="core-menu-block" href="'.$this->baseUrl('results/'.$params->url.'/').'">';
		if ($core=='biblio') {
			$biblioContains = $this->solr->facetsList()['record_contains'] ?? [];
			$biblioTotal = $total;
			#echo $this->helper->pre($biblioContains);
			} else {
			$biblioRecWithLinks = $biblioContains[$core] ?? 0;	
			$orphansInCore = $orphans[$core] ?? 0;
			$sumOfLinks = $totalWikiBiblioRec + $orphansInCore;
			if ($sumOfLinks<$biblioRecWithLinks)
				$sumOfLinks = $biblioRecWithLinks;
			
			$corePercent = round(($biblioRecWithLinks/$biblioTotal)*100);
			$wikiPercent = round(($totalWikiBiblioRec/$sumOfLinks)*100);
			
			$wikiInCore = $corePercent*($wikiPercent/100);
			$coreWithOutWiki = $corePercent-$wikiInCore;
			
			$graph['empty'] = [
					'color' => '#eee',
					'title' => $this->transEsc('records without '.$core),
					'count' => 100-$corePercent,
					];
			$graph['links'] = [
					'color' => '#d6cae2',
					'title' => $this->transEsc('records with '.$core.', without wikidata links'),
					'count' => $coreWithOutWiki,
					];
			$graph['wiki'] = [
					'color' => '#5c517b',
					'title' => $this->transEsc('Bibliographic records reprezented in this '.$core.' collection'),
					'count' => $wikiInCore,
					];
			//'.$this->helper->drawSVGPie($graph, ['width'=>80, 'height' =>80]).'
			echo '
				<div class="core-menu-popup" id="popup-'.$core.'">	
					<div class="core-menu-popup-content">
						'.$this->helper->progressThinMulti($graph,100).'
						<b>'.$corePercent.'%</b> ('.$this->helper->numberFormat($biblioRecWithLinks).') of bibliografic records contains informations about '.$core.'.<br/>
						This creates '.$this->helper->numberFormat($sumOfLinks).' links between bibliographic records and '.$core.'.</br>
						<b>'.$wikiPercent.'%</b> ('.$this->helper->numberFormat($totalWikiBiblioRec).') of these links are represented in the '.$core.' collection. <br/>
						
						
					</div>
				</div>
				';
			}
		
		echo '
			<div class="core-menu-item" href="'.$this->baseUrl('results/'.$params->url.'/').'">
			<span class="core-menu-icon"><i style="font-size:1.8em;" class="'.$params->ico.'"></i></span><br/>'.
			$this->transESC($params->name).'<br/>
			<span class="core-menu-number count">'.$total.'</span><br/>
			</div>
			';
		echo '</a>';	
		#echo '<br/>'.$this->helper->pre($this->solr->response);
		}
	}
echo '</div>';
?>
<script>
$('.count').each(function () {
    $(this).prop('Counter', 0).animate({
        Counter: $(this).text()
    }, {
        duration: 2000,
        easing: 'swing',
        step: function (now) {
            const formattedNumber = Math.ceil(now).toLocaleString();
            $(this).text(formattedNumber);
        }
    });
});

</script>



<?php 
/*
q=*:*&q.op=OR&rows=0&stats=true&stats.field=biblio_count&stats.facet=record_type
q=*:*&q.op=OR&rows=0&stats=true&stats.field=biblio_count
*/
?>