<?php 
$biblioBox = '';
$viafBox = '';
$ids = [];	

if (!empty($this->coreRecord->solrRecord->biblio_labels)) {
	if ($this->user->isLoggedIn() && $this->user->hasPower('editor')) {
		$extraTools = true;
		$extraToolBtn = '
			<div class="btn-group">
				<button class="toolbar-btn dropdown-toggle" data-toggle="dropdown" data-original-title="'.$this->transEsc('Options').'" aria-expanded="false">
					<i class="ph ph-plus-minus"></i>
					<span class="hidden">'.$this->transEsc('Options').'</span>
				</button>
					<ul class="dropdown-menu">
					<li><a onclick="">'.$this->transEsc('Add black list rule').'</a></li>
					<li><a onclick="">'.$this->transEsc('Add manual matching rule').'</a></li>
					</ul>			
			</div>	
			';
		} else 
		$extraTools = false;
	
	$recType = $this->wiki->recType();
	
	$biblioLabels = json_decode(current($this->coreRecord->solrRecord->biblio_labels), true);
	if (!empty($biblioLabels)) {
		if (!empty($this->wiki->getViafId())) {
			$viaf = $this->wiki->getViafId();
			$ids = $this->viafSearcher->getAllIds($viaf);
			$labels = $this->viafSearcher->getLabels($viaf);

			$viafBox .= '<br/><h4>'.$this->transEsc('Labels on VIAF').':</h4>';
			$viafBox .= '<div class="list-group">';
			if (!empty($labels)) {
				foreach ($labels as $label) {
					$label = (object)$label;
					$compareTable[] = $label->label;
					$viafBox .= '<a class="list-group-item">'.$label->label.'<span class="badge">'.$this->helper->numberFormat($label->count).'</span></a>';
					}
				}
			$viafBox .= '</div>';	
			$viafBox .= '<small>'.$this->transEsc($this->viafSearcher->dataOrigin.' data source').'.</small>';
			}
		if (empty($compareTable) && !empty($this->wiki->solrRecord->labels)) {
			$viafBox .= '<br/><h4>'.$this->transEsc('Labels on wikidata').':</h4>';
			$viafBox .= '<div class="list-group">';
			if (is_string($this->wiki->solrRecord->labels))
				$this->wiki->solrRecord->labels = json_decode($this->wiki->solrRecord->labels);
			foreach ($this->wiki->solrRecord->labels as $label) {
				@$compareTableTmp[$label]++;
				}
			foreach ($compareTableTmp as $label=>$count) {
				$compareTable[] = $label;
				$viafBox .= '<a class="list-group-item">'.$label.'<span class="badge">'.$this->helper->numberFormat($count).'</span></a>';
				}
			$viafBox .= '</div>';	
			}
		
		// uwaga wiki->solrRecord nie ma tego co powinien. Wróć tu i napraw. 
		if (empty($ids))
			$ids = $this->wiki->solrRecord->eids_any ?? [];
		
		$columnsParams = [
			'name' => [
					'title' => $this->transEsc('Name'),
					],
			'dates' => [
					'title' => $this->transEsc('Dates range'),
					],
			'viaf' => [
					'title' => $this->transEsc('Has VIAF'),
					],
			'eids' => [
					'title' => $this->transEsc('Other ID'),
					],
			'appearances' => [
					'title' => $this->transEsc('Appearances'),
					'class' => 'text-right'
					],
			'certainty' => [
					'title' => $this->transEsc('Certainty of fit'),
					],
			];
		
		$columnsToShow = [];
		$fieldsToShow = [];
		$i = 0;
		foreach ($biblioLabels as $label=>$count) {
			$tLabel = explode('|', $label);
			if (count($tLabel)==3)
				$result = (object)[
					'name' => $tLabel[0],
					'dates' => null,
					'viaf' => $tLabel[1] ?? null,
					'eids' => $tLabel[2] ?? null,
					];
				else
				$result = (object)[
					'name' => $tLabel[0],
					'dates' => $tLabel[1] ?? null,
					'viaf' => $tLabel[2] ?? null,
					'eids' => $tLabel[3] ?? null,
					];
			$i++;
			foreach ($result as $fieldName=>$fieldValue) {
				
				if (!empty($fieldValue)) 
					@$columnsToShow[$fieldName]++;
				$fieldsToShow[$i][$fieldName] = $fieldValue;
				}
			
			}
		
		
		#$biblioBox .= '<textarea>'.print_r( $this->wiki->solrRecord, 1).'</textarea>';
		$biblioBox.= '<br/><h4>'.$this->transEsc('Pre-hamonisation and unification bibliographic record labels linked to this record').':</h4>';
		
		$biblioBox.= '<table class="table table-hover">';
		$biblioBox.= '<tr>';
		if ($extraTools) 
			$biblioBox.= '<td> </td>';
		$biblioBox.= '<td>'.$this->transEsc('Name').' (a)</td>';
		if (!empty($columnsToShow['dates']))
			$biblioBox.= '<td>'.$this->transEsc('Dates range').' (d)</td>';
		if (!empty($columnsToShow['viaf']))
			$biblioBox.= '<td>'.$this->transEsc('Has VIAF').'</td>';
		if (!empty($columnsToShow['eids']))
			$biblioBox.= '<td>'.$this->transEsc('Other ID').'</td>';
		$biblioBox.= '<td>'.$this->transEsc('Appearances').'</td>';
		$biblioBox.= '<td> </td>';
		$biblioBox.= '<td>'.$this->transEsc('Certainty of fit').'</td>';
		$biblioBox.= '</tr>';
		$biblioBox.= '<tbody>';
		$i = 0;
		foreach ($biblioLabels as $label=>$count) {
			$tLabel = explode('|', $label);
			if (count($tLabel)==3)
				$result = (object)[
					'name' => $tLabel[0],
					'dates' => null,
					'viaf' => $tLabel[1] ?? null,
					'eids' => $tLabel[2] ?? null,
					];
				else
				$result = (object)[
					'name' => $tLabel[0],
					'dates' => $tLabel[1] ?? null,
					'viaf' => $tLabel[2] ?? null,
					'eids' => $tLabel[3] ?? null,
					];
			$hasViaf = '';
			
			if (!empty($result->viaf))
				$hasViaf = '<i class="ph ph-check" title="VIAF: '.$result->viaf.'" data-toggle="tooltip" ></i>';
			$key = $this->buffer->createFacetsCode(["orgin_labels:\"{$label}\""]);
			$biblioBox.= '<tr>';
			if ($extraTools) 
				$biblioBox.= '  <td>'.$extraToolBtn.'</td>';
			$biblioBox.= '	<td><a href="'.$this->buildUri('results', ['core'=>'biblio', 'facetsCode'=>$key]).'">'.$result->name.'</a></td>';
			if (!empty($columnsToShow['dates'])) 	
				$biblioBox.= '<td>'.$result->dates.'</td>';
			if (!empty($columnsToShow['viaf']))
				$biblioBox.= '<td>'.$hasViaf.'</td>';
			if (!empty($columnsToShow['eids']))
				$biblioBox.= '<td>'.$result->eids.'</td>';
			$biblioBox.= '	<td class="text-right">'.$this->helper->numberFormat($count).'</td>';
			$percent = $this->helper->matchLevelStr($result, $compareTable, $ids);
			$biblioBox.= '<td class="text-right"><small>'.$this->helper->numberFormat($percent*100,0).'%</small></td>';
			$biblioBox.= '<td style="vertical-align:middle;">'.$this->render('helpers/matchLevel.php', ['matchLevel' => $percent]).'</td>';
			$biblioBox.= '</tr>';
			}
		$biblioBox.= '</tbody>';
		$biblioBox.= '</table>';
		$biblioBox.= $this->helper->alertIco('info', 'ph ph-info', $this->transEsc('Above you can see a list of all the variants of how this record was written in the bibliographic records.<br/>
				The <b OnMouseOver="" OnMouseOut="">Certainty of fit</b> column shows how &quot;confident&quot; we are that this label should be here.<br/><br/>
				Only records containing a viaf or other identifier receive 100%. In other cases, the indicator shows a match between the label recorded in the bibliographic record and the labels recorded in the VIAF or wikidata database.<br/><br/>
				<b>Warning:</b> even when a label exactly matches records in external databases, we cannot be sure that there is not, for example, another person with the same name, surname and year of birth. Therefore, whenever we do not have an ID we consider the match at most 90% certain. 
				') );
		# $biblioBox.= $this->helper->pre($ids);
		}
	}
?>		
<?= $biblioBox ?>