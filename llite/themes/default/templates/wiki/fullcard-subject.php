<?php
$PRE = '';
$stats = '';
$Tchecked = []; 
$allNames = $this->wiki->getAllNames();
if (!empty($this->wiki->get('aliases')))
	$names = explode(', ', $this->wiki->get('aliases'));
$names[] = $this->wiki->get('labels');
foreach ($names as $name) {
	$clearName = $this->solr->clearStr($name);
	if ($clearName<>'')
		$Tnames[$clearName] = $clearName;
	}
$Tnames = [];
if (!empty($allNames))
	foreach($allNames as $name) {
		$clearName = $this->solr->clearStr($name);
		if ($clearName<>'')
			$Tnames[$name] = $clearName;
		}
$res = $this->solr->getFullList('topic');
if (!empty ($res->results))
	foreach ($res->results as $word=>$count) {
		$clearWord = $this->solr->clearStr($word);
		if (in_array($clearWord, $Tnames) && (floatval($clearWord)==0)) {
			$Tchecked[$word]=$count;
			$Twords[$word] = $word;
			}
		}
if (!empty($Twords))
	$stat = $this->solr->getStats(
					'biblio',
					$Twords, 
					['allfields'], 
					$this->getIniParam('persons','statList','statFields')
					);	
	else
	$stat = [];
			
$PRE = "<pre>".print_R($stat,1)."</pre>";
$stats = '';

$statBoxes = $this->getIniArray('places', 'statBoxes');
$formatters = $this->getIniArray('facets', 'formattedFacets');
	
$stats .= '<div class="statBox">';
$Llp = 0;
if (!empty($statBoxes))
foreach ($statBoxes as $facet=>$facetName) {
	$nstat = [];
	$lp = 0;
	if (!empty($stat->facets[$facet])) {
		foreach ($stat->facets[$facet] as $k=>$v) {
			$lp++;
			$index = $lp+$Llp;

			$key = $this->buffer->createFacetsCode($this->sql, ["$facet:\"$k\""]);
			if (is_array($stat->name)) {
				
				$formsChosen['operator'][1] = 'or';
				foreach ($stat->name as $lookfor)
					$formsChosen[1][] = [
						'lookfor' => $lookfor,
						'meth' => 'contains',
						'type' => 'AllFields'
						];
				
				$searchJson = json_encode($formsChosen);
				$searchKey = md5($searchJson);
				$link = $this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, ['sk'=>$searchKey,'sj'=>$searchJson]);
				} else if (is_string($stat->name))
				$link = $this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, ['lookfor'=>$stat->name, 'type'=>'AllFields']);
			
			$tk = $k;
			if (array_key_exists($facet, $formatters))
				$tk = str_replace('"', "'", $this->helper->{$formatters[$facet]}($k));
			
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
			
		


?>

<div class="graybox">
  <div class="infopage">
    <div class="infopage-header">
	   <h1 property="name"><?= $title = $this->wiki->get('labels') ?> <small><?= $this->wiki->get('aliases') ?></small></h1>
	</div>
	<div class="person-record">
	
		
		<div class="record-left-panel">
			<div class="thumbnail">
				<?= $this->render('helpers/wiki.photo.php') ?>
				<?= $this->render('helpers/wiki.audio.php') ?>
			</div>
		</div>
		<div class="record-main-panel">
			
			<p><?= $this->wiki->get('descriptions') ?></p>
			
			
			
			
			<div class="text-right">
			<small>
				<a href="https://www.wikidata.org/wiki/<?=$this->wiki->getID() ?>" class="text-right"><?= $this->transEsc('Source of information')?> Wikidata</a><br/>
				<a href="<?=$this->wiki->getSiteLink() ?>" class="text-right"><?= $this->transEsc('More information on')?> Wikipedia</a>
			</small>
			</div>
			
			
			<?php
			
			if (count($Tchecked)>0) {
				$max = (int)max($Tchecked);
				$pic = $max/50;
				
				
				foreach ($Tchecked as $name=>$count)
					$Ttl[] = "['$name', ".(round($count/$pic)+20).']';
				
				foreach ($allNames as $name) 
					$Ttl[] = "['".str_replace("'",'',$name)."', 10]";
				
					
					
				$js = '
					const tagList = ['.implode(",\n", $Ttl).'];
					WordCloud(document.getElementById(\'canvas\'), {
						list: tagList,
						});
					';
				$this->addJS($js);
				
				echo '<div id="canvas" style="height:400px; "></div>';
				echo "<h4>Topics found in the bibliography</h4>";
				foreach ($Tchecked as $name=>$count)
					echo "$name <span class=badge>$count</span> ";
				}
			
			
			?>
			
		</div>
		
	</div>
	<?= $stats ?>
  </div>

  <!--div class="infopage">
	Tnames: <pre><?= print_R($names) ?></pre>
	Stats: <?= $PRE ?>  
	Record: <pre><?= print_r($this->wiki->record) ?></pre>  
	Photos: <pre><?= print_r($photo) ?></pre>  
	Maps: <pre><?= print_r($this->maps->getMapsPoints()) ?></pre>  
  </div-->
  
</div>