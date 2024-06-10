<?php
$this->addClass('buffer', 	new buffer()); 


$input = new stdclass;
$input->indexField = $this->POST['solrIndex'];
$input->lookfor = $this->POST['lookfor'] ?? '';
$input->clearlookfor = $this->helper->clearStr($this->POST['lookfor']);
$input->graphRange = $this->POST['graphRange'] ?? 6;
$input->graphMode = $this->POST['graphMode'];
$input->list = json_decode(base64_decode($this->POST['list']));



$statStr = '';
if (!empty($input->list)) {
	
	$Llp = 100;
	foreach ($input->list as $contentValue=>$statList) {
		$nstat = [];
		$lp = 0;
		$statStr = '';
		$statStr .= '<div class="statBox-comparsion">';
	
		$Llp = $Llp+$lp;
		$graphName = $this->configJson->biblio->facets->solrIndexes->{$input->indexField}->name ?? 'undefined ? ';
 
		if (!empty($statList))
			foreach ($statList as $k=>$v) {
				$clearK = $this->helper->clearStr($k);
				$convertedClearK = $this->helper->clearStr($this->helper->convert($input->indexField,$k));
				if (empty($input->clearlookfor) or (stristr($convertedClearK, $input->clearlookfor) or stristr($clearK, $input->clearlookfor))) {
				
					$lp++;
					$index = $lp+$Llp;

					$key = $this->buffer->createFacetsCode(["{$input->indexField}:\"$k\"", "with_roles_wiki:\"{$contentValue}\""]);
					$link =$this->buildUri('results', ['core'=>'biblio', 'facetsCode'=>$key] );
					
					$nstat[$index] = [
						'label' => $this->helper->convert($input->indexField,$k),
						'label_o' => $k,
						'count' => $v,
						'link' 	=> $link,
						'color' => $this->helper->getGraphColor($lp),
						'index' => $index,
						];
					}
				}
		$statStr .= $this->helper->drawStatBoxComparison($this->transEsc($graphName), $nstat, $input->graphRange);
			
		$statStr .="</div>";
		$statStrRow[$contentValue] = $statStr;
		}
	echo '<div class="col-sm-4">'.implode('</div><div class="col-sm-4">', $statStrRow).'</div>';
	} 

#echo '<div class="col-sm-12">';
#echo $this->helper->pre($input);
#echo '</div>';



?>