<?php 
if (empty($this)) die();

require_once('functions/class.wikidata.libri.php');
require_once('functions/class.solr.php');
$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer());
$hasBoxes = false;




$currentCore = $this->routeParam[0];

if (!empty($this->POST['pdata'])) {
	$withWikiQ = [];
	foreach ($this->POST['pdata'] as $key=>$data) {
		$data = (object)$data;
		if (!empty($data->wikiQ) && ($data->wikiQ !== 'not found')) {
			$withWikiQ[] = $data->wikiQ;
			unset($this->POST['pdata'][$key]);
			
			}
		}
	if (count($withWikiQ)>0) {	
		
		$query['q'] = [
				'field' 	=> 'q',
				'value' 	=> 'id:('.implode(' OR ',$withWikiQ).')'
				];
		$query['rows'] = [
				'field' 	=> 'rows',
				'value' 	=> count($withWikiQ)
				];
		
		if (empty($this->getUserParam($currentCore.':view')))
			$this->saveUserParam($currentCore.':view', $this->configJson->$currentCore->summaryBarMenu->view->default);
		$currentView = $this->getUserParam($currentCore.':view'); 
		
		$results = $this->solr->getQuery($currentCore, $query); 
		$results = $this->solr->resultsList();
		
		echo '<div id="resultsBox" class="results-list '.$currentCore.'-list '.$currentView.'-list">';
		foreach ($results as $result) {
			#echo $this->helper->pre($result);
			
			$resultObj = new wikiLibri($this->user->lang['userLang'], $result);
			
			echo '<div class="result-box '.$currentCore.'-result " id="'.$currentCore.'_'.$result->wikiq.'">';
			echo $this->render('wikiResults/resultBoxes/'.$this->getUserParam($currentCore.':view').'.php',['result'=>$resultObj]);
			echo '</div>';
			$this->addJS("$('.personBox{$result->wikiq}').html($('#{$currentCore}_{$result->wikiq}').html())");
			}
		echo '</div>';	
		$hasBoxes = true;
		}
		
	if (!empty($this->POST['pdata'])) {	
		if ($hasBoxes) echo $this->transEsc('Also').':<br/>';
		echo '<div class="row"><div class="col-sm-7"><div class="table-responsive">';
		echo '<table class="table table-hover">';
		foreach ($this->POST['pdata'] as $key=>$data) {
			echo '<tr>';
			if (is_string($data)) {
				echo '<td>'.$data.'</td>';
				} else {
				$data = (object)$data;
				
				$link = '';
				$linkTitle = '';
				
					
				echo '<td>';
				$data->name = $data->name ?? $data->title;
				if (!empty($data->biblio_label)) {
					$link = $this->buildUrl('results', ['core'=>'biblio', 'facetsCode'=> $this->buffer->createFacetsCode(['orgin_labels:"'.$data->biblio_label.'"'])]);
					$linkTitle = $this->transEsc('Use for narrow searching');
					} else {
					$link = $this->buildUrl('results', ['core'=>'biblio', 'lookfor'=>$data->name, 'type'=>'allFields']);
					$linkTitle = $this->transEsc('Look for');
					}
				
				
				if (!empty($data->name)) {
					echo '<a href="'.$link.'" title="'.$linkTitle.'">'.$data->name.'</a>';
					} 
					
				echo '</td>';	
				echo '<td>';	
				if (!empty($data->dates)) {
					echo '<span class="date">'.$data->dates.'</span>';
					}
				echo '</td>';	
				echo '<td>';	
				if (!empty($data->role)) {
					$data->role = (array)$data->role;
					echo ' <span class="label label-info">'.implode(', ',$data->role).'</span>';
					}
				if (!empty($data->roles)) {
					$data->roles = (array)$data->roles;
					echo ' <span class="label label-success">'.implode(', ',$data->roles).'</span>';
					}
				echo '</td>';
				}
			echo '</tr>';
			}
		echo '</table></div></div></div>';	
		}
	
	} else {
	echo '<p class="largeCenterBox">'.$this->transEsc('There is nothing to show here').'.</p>';
	}

#echo $this->helper->pre($this->POST['pdata']);

?>