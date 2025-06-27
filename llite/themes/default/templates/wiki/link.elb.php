<?php 
$linkPanelValues = $this->coreRecord->linkPanel();
$wikiQ = $linkPanelValues->wikiId;
$baseConditions = [];
$linksHeader = '';
$linksToShow = $this->transEsc('Item not found in bibliographic records'); 

if (!empty($facets['with_roles_wiki'])) {
	$sum = array_sum($facets['with_roles_wiki']);
	$roleMax = max($facets['with_roles_wiki']);
	$linksToShow = '';		
	foreach ($facets['with_roles_wiki'] as $roleFullName=>$roleCount) {
		$tmp = explode('|', $roleFullName);
		$roleName = end($tmp);
		$baseConditions[] = '~with_roles_wiki:"'.$roleFullName.'"';
		$key = $this->buffer->createFacetsCode(['with_roles_wiki:"'.$roleFullName.'"']);
		$displayParams = $this->helper->formatMajorRole($roleName);
		#echo $this->helper->pre($roleFullName);
		#echo $this->helper->pre($wikiQ);
		#echo $this->helper->pre($linkPanelValues);
		$linksToShow .= '
			<a href="'.$this->buildURL('results', ['core'=>'biblio', 'facetsCode'=>$key]).'" class="statMini">
				<div class="name"><i class="'.$displayParams->ico.'"></i> '.$this->transEsc($displayParams->title).' </div>
				<div class="number">
					<b>'.$this->helper->numberFormat($roleCount).'</b>
					'.$this->helper->progressThin($roleCount, $sum).'
				</div>
			</a>
			';
		}
	$key = $this->buffer->createFacetsCode($baseConditions);
	$linksHeader = '
			<a href="'.$this->buildURL('results', ['core'=>'biblio', 'facetsCode'=>$key]).'" class="statMini">
				<div class="name">'.$this->transEsc('appears in the following roles').':</div>
				<div class="number"><b>'.$this->helper->numberFormat($sum).'</b> '.$this->transEsc('total results').'</div>
			</a>
			';
	
	}


echo '
		<dl class="detailsview-item">
		  <dt class="dv-label">'.$label.':</dt>
		  <dd class="dv-value">'.$linksHeader.$linksToShow.'</dd>
		</dl>
	';
		
?>