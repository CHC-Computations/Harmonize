<?php 

#$coreFields = $this->record->getCoreFields('persons');
/*
$Tmap[] = [	'name' => 'Ustrzyki dolne', 'lat' => 49.43027,	'lon' => 22.56074,	'color' => 'red'];
$Tmap[] = [	'name' => 'Świnioujście',	'lat' => 53.847,	'lon' => 14.242,	'color' => 'red'];
$Tmap[] = [	'name' => 'Hel',			'lat' => 54.636,	'lon' => 18.739,	'color' => 'red'];
$Tmap[] = [	'name' => 'Oslo',			'lat' => 59.893,	'lon' => 10.645,	'color' => 'red'];
$Tmap[] = [	'name' => 'Londyn',			'lat' => 51.5,		'lon' => 0.0,		'color' => 'red'];
$Tmap[] = [	'name' => 'Tornio',			'lat' => 65.8451041,'lon' => 24.121,	'color' => 'red'];
*/


$as_author_facet = $this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->author_facet_s\""])

?>
<div class="graybox">
  <div class="infopage">
    <div class="infopage-header">
		<h1 property="name"><?= $this->record->MainAuthor['full_name'] ?> <small></small></h1>
		
	</div>
	<div class="person-record">
	
		
		<div class="record-left-panel">
			
			<div class="thumbnail">
			<?= $this->record->getImage() ?>
			</div>
			
			<div style="float:right;"><?= $this->record->getSound() ?></div>
			
		</div>
		<div class="record-main-panel">
			
			<?= $this->record->getDescription() ?>
			
			<ul class="detailsview">
				<?php foreach ($coreFields as $func=>$current): ?>
					<dl class="detailsview-item">
					  <dt class="dv-label"><?=$this->transEsc($current['label'])?>:</dt>
					  <dd class="dv-value"><?=$current['content']?></dd>
					</dl>
				<?php endforeach; ?>
			</ul>
			
			

			
		</div>
		
	</div>
	
	
	<div class="statBox">
		<div class="il-panel">
			<div class="il-panel-header"><h4><?= $this->transEsc('Roles summary') ?></h4></div>
			<div class="il-panel-bottom">
				<table class="list">
					<tbody>
						<tr>
							<td colspan=3><?= $this->transEsc('We have')?> <b><A href="<?= $link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->author_facet_s\" OR topic_person_str_mv:\"$stat->topic_person_str_mv\""]) ) ?>"><?= $stat->numFound ?></a></b> <?= $this->transEsc('bibliographic records')?>.</td>
						</tr>
						<?php if (!empty($stat->as_author)): ?>
							<tr>
								<td><a href="<?= $link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$as_author_facet ); ?>"><?= $this->transEsc('As an author')?>: <b><?= $stat->as_author ?></b></a></td>
								<td><?= $this->helper->percentBox($stat->as_author_pr,100,'#AC9FC2') ?></td>
								<td>%</td>
							</tr>
						<?php endif; ?>
						<?php if (!empty($stat->as_topic_person)): ?>
							<tr>
								<td><a href="<?= $link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$this->buffer->createFacetsCode($this->sql, ["topic_person_str_mv:\"$stat->topic_person_str_mv\""]) ); ?>"><?= $this->transEsc('As a topic person')?>: <b><?= $stat->as_topic_person ?></b></a></td>
								<td><?= $this->helper->percentBox($stat->as_topic_person_pr,100,'#AC9FC2') ?></td>
								<td>%</td>
							</tr>
						<?php endif; ?>
						
					</tbody>
				</table>
				
			</div>
			<div class="il-panel-bottom" id="otherRolesBox">
				<?= $this->helper->loader2()?>
				<?= $this->transESC('Searching for other roles (as translator, as illustrator, etc)')?>...
				
			</div>
		</div>
	
		<?php 
		$_SESSION['tr'] = [];
		$_SESSION['tid'] = [];
		$this->JS[] = "page.ajax('otherRolesBox','persons/person.roles.stat/{$stat->as_author}/{$as_author_facet}/');";
		
		$statBoxes = $this->getIniArray('person-card', 'statBoxes');
		$Llp = 0;
		foreach ($statBoxes as $facet=>$facetName) {
			$nstat = [];
			$lp = 0;
			foreach ($stat->facets[$facet] as $k=>$v) {
				$lp++;
				$index = $lp+$Llp;
				
				
				
				$key = $this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->author_facet_s\" OR topic_person_str_mv:\"$stat->topic_person_str_mv\"", "$facet:\"$k\""]);
				$link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key);
				
				$nstat[$index] = [
					'label' => $this->transEsc($k),
					'label_o' => $k,
					'count' => $v,
					'link' 	=> $link,
					'color' => $this->helper->getGraphColor($lp),
					'index' => $index,
					];
				}
			$Llp = $Llp+$lp;
			$stats = $this->helper->drawStatBox($this->transEsc($facetName), $nstat);
			}
		
		
		?>
	
	</div>
	
	<div class="text-right"><button class="btn btn-primary" OnClick='page.ajax("viafBox","load.from.viaf/<?= $this->record->id ?>");'>Update Viaf Id:<?= $this->record->id ?></button></div>
	<div id="viafBox">
		
	</div>
				
	
	
	
	<div class="tabs-panel">
		<?= 
		$this->helper->tabsCarousel([
			'details' 	=> ['label'=>'Marc view', 'content'=>$this->record->drawMarc()],
			'jsonview' 	=> ['label'=>'Json (tmp tech)', 'content'=>"<pre style='background-color:transparent; border:0px;' id='JsonView'>".print_r($this->record->fullRecord, 1)."</pre>" ],
			'stats' 	=> ['label'=>'Charts', 'content'=>$stats ],
			'map' 		=> ['label'=>'Map preview', 'content'=> $this->maps->drawEuropeMap($Tmap) ],
			], 'details');
		?>
	  
    </div>
	  
	 
  </div>
</div>

<!--script>
	var input = <?= json_encode($this->record->fullRecord) ?>;
	$('#JsonView').jsonViewer(input, {collapsed: true, rootCollapsable: false});
</script-->


<?php 
# <b><A href="<?= $link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$this->buffer->createFacetsCode($this->sql, ["~author_facet_s:\"$stat->author_facet_s\"", "~topic_person_str_mv:\"$stat->topic_person_str_mv\""]) ) 
?>
