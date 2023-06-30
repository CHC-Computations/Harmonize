<?php 
	$as_author_facet = $this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->author_facet_s\""]);


// $this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->author_facet_s\" OR topic_person_str_mv:\"$stat->topic_person_str_mv\""])

	$PRE = $stat->fullRecivedName."<pre>".print_R($stat,1)."</pre>";
	
	
	$facetCode = $this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->fullRecivedName\" OR topic_person_str_mv:\"$stat->fullRecivedName\""]);
	$stats = '<div class="statBox">';
	$stats .= '
			<div class="il-panel">
				<div class="il-panel-header"><h4>'.$this->transEsc('Roles summary') .'</h4></div>
				<div class="il-panel-bottom">
					<table class="list">
						<tbody>
							<tr> 
								<td colspan=3>'. $this->transEsc('We have').' <b><A href="'. $link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$facetCode ) .'">'. $stat->numFound .'</a></b> '. $this->transEsc('bibliographic records').'.</td>
							</tr>';
	if (!empty($stat->as_author)) {
		$link = $this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$as_author_facet );
		$stats .='<tr>
						<td><a href="'.$link.'">'.$this->transEsc('As an author').': <b>'. $stat->as_author .'</b></a></td>
						<td>'. $this->helper->percentBox($stat->as_author_pr,100,'#AC9FC2') .'</td>
						<td>%</td>
					</tr>';				
		} 
	if (!empty($stat->as_topic_person)) {
		$link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$this->buffer->createFacetsCode($this->sql, ["topic_person_str_mv:\"$stat->topic_person_str_mv\""]) );
		$stats .= '<tr>
						<td><a href="'. $link .'">'. $this->transEsc('As a topic person').': <b>'. $stat->as_topic_person.'</b></a></td>
						<td>'. $this->helper->percentBox($stat->as_topic_person_pr,100,'#AC9FC2') .'</td>
						<td>%</td>
					</tr>';
		}
	$stats.= '				
						</tbody>
					</table>
					
				</div>
				<div class="il-panel-bottom" id="otherRolesBox">
					'.$this->helper->loader2().'
					'.$this->transESC('Searching for other roles (as translator, as illustrator, etc)').'...
					
				</div>
			</div>
	
			';
	
	
	$_SESSION['tr'] = [];
	$_SESSION['tid'] = [];
	$this->JS[] = "page.ajax('otherRolesBox','persons/person.roles.stat/{$stat->as_author}/{$as_author_facet}/');";
	
	$statBoxes = $this->getIniArray('person-card', 'statBoxes');
	$Llp = 0;
	foreach ($statBoxes as $facet=>$facetName) {
		$nstat = [];
		$lp = 0;
		if (!empty($stat->facets[$facet]))
			foreach ($stat->facets[$facet] as $k=>$v) {
				$lp++;
				$index = $lp+$Llp;

				# $key = $this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->author_facet_s\" OR topic_person_str_mv:\"$stat->topic_person_str_mv\"", "$facet:\"$k\""]);
				$key = $this->buffer->createFacetsCode($this->sql, ["$facet:\"$k\""]);
				$link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, ['lookfor'=>'viaf/'.$this->person->record->viafid, 'type'=>'AllFields']);
				
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
		$stats .= $this->helper->drawStatBox($this->transEsc($facetName), $nstat);
		}
	$stats .="</div>";
	
?>
	
	

<!--	
	<div class="text-right"><button class="btn btn-primary" OnClick='page.ajax("viafBox","load.from.viaf/<?= $this->record->id ?>");'>Update Viaf Id:<?= $this->record->id ?></button></div>
	<div id="viafBox">
		
	</div>
	-->			
<div class="graybox">
  <div class="infopage">
    <div class="infopage-header">
		<h1 property="name"><?= $this->person->getName() ?> <small></small></h1>
		
	</div>
	<div class="person-record">
	
		
		<div class="record-left-panel">
			<div class="thumbnail">
			<?= $this->person->getImage() ?>
			</div>
			
			<div style="float:right;"><?= $this->person->getSound() ?></div>
			
			
		</div>
		<div class="record-main-panel">
			<?php if (!empty($this->person->getDescription())): ?>
				<p><?= $this->person->getDescription() ?></p>
			<?php endif; ?>	
			
			<ul class="detailsview">
				<?php foreach ($coreFields as $func=>$current): ?>
					<dl class="detailsview-item">
					  <dt class="dv-label"><?=$this->transEsc($current['label'])?>:</dt>
					  <dd class="dv-value"><?=$current['content']?></dd>
					</dl>
				<?php endforeach; ?>
			</ul>
			<div class="addedview">
				<?= $this->transEsc('More on the source pages')?>:
				<?= $this->person->getLinkPanelBig() ?>
			</div>
		</div>
		
	</div>	
	
	<div class="tabs-panel">
		<?= 
		$this->helper->tabsCarousel([
			'stats' 	=> ['label'=>'Charts', 'content'=>$stats ],
			'details' 	=> ['label'=>'Marc view', 'content'=>$this->person->drawMarc()],
			'jsonview' 	=> ['label'=>'Json (tmp tech)', 'content'=>"<pre style='background-color:transparent; border:0px;' id='JsonView'>".print_r($this->person->getPre(), 1)."</pre>" ],
			'map' 		=> ['label'=>'Map preview', 'content'=> $this->maps->drawEuropeMap($Tmap) ],
			], 'map');
		?>
	  
    </div>
	  
	 
  </div>
</div>


<?php 
$tabId = $this->helper->lastId;
$this->addJS("
	$('.ind_{$tabId}').removeClass('active'); 
	$('#ind_{$tabId}_0').addClass('active');
	$('#myCarousel{$tabId}').carousel(0);
	");

?>


<?php ##echo "<pre>".print_R($this->person,1)."</pre>";?>