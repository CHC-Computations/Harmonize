<?php
if (!empty($stat)) {
 
	$as_author_facet = $this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->author_facet_s\""]);

	$stats = '<div class="statBox">';
	$stats .= '
			<div class="il-panel">
				<div class="il-panel-header"><h4>'.$this->transEsc('Roles summary') .'</h4></div>
				<div class="il-panel-bottom">
					<table class="list">
						<tbody>
							<tr> 
								<td colspan=3>'. $this->transEsc('We have').' <b><A href="'. $link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/', ['lookfor'=>$rec['name'], 'type'=>'AllFields'] ) .'">'. $stat->numFound .'</a></b> '. $this->transEsc('bibliographic records').'.</td>
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
				$link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, ['lookfor'=>$rec['name'], 'type'=>'AllFields']);
				
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
	}	

?>
<div class="graybox">
  <div class="infopage">
	<?php if (!empty($rec->id)): ?>
		<div class="infopage-header">
			<h1 property="name"><?= $this->transEsc('Person unknown') ?> <small></small></h1>
		</div>
		<div class="person-record">
		
		<div><?= $this->transEsc('Searching for information in external databases') ?>.</div>
		<?= $this->helper->loader2() ?>
		
		<?php 
		
		if (stristr($rec->id,'viaf_id')) {
			
			$id = str_replace('viaf_id', '', strtolower($rec->id) );
			
			
			
			
			echo '
				<div id="viafBox">'.$this->helper->loader( $this->transEsc('getting information from').' VIAF').'</div>
				
				<br/><br/>
				
				<script>
				page.ajax("viafBox","load.from.viaf/'.$id.'");
				</script>
				';
			}

		?>
	<?php else: ?>	
		<div class="infopage-header">
			<h1 property="name"><?= $rec['name'] ?> <small></small></h1>
		</div>
		<div class="person-record">
	
		<?= $this->helper->alertIco(
					'warning', 
					'ph-bug-bold', 
					'<h2>'.$this->transEsc('Sorry').'!</h2><p>'.$this->transEsc('We are still thinking how to get information about this person').'.</p>'
					) ?>		
		<?= $this->helper->alertIco(
					'info', 
					'ph-warning-circle-bold', 
					$this->transEsc('Below is a summary related to the text').': <strong>'.$this->getParam('GET', 'name').'</strong>.<br/>'.$this->transEsc('May not always apply to the same person').'!'
					) ?>
		
		<?= $stats ?>

		
	<?php endif; ?>	
	
	
	 
  </div>
</div>

