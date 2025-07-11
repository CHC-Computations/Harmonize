<?php
#echo $this->helper->pre($persons);
$uid = uniqid();
$person = $persons;

if (!empty($person->wikiQ) && ($person->wikiQ !== 'not found')) {
	$OMO = "OnMouseOver=\"page.ajax('personBox".$uid."', '/wiki/person/box/{$person->wikiQ}'); \"";
	$boxClass = 'personBox'.$person->wikiQ;
	$ico = 'ph ph-info';
	} else {
	$OMO = ''; 
	$boxClass = 'personBoxEmpty';
	$ico = 'ph ph-question';
	}


?>
<?php 
if (!empty($person->name)) {
	$displayName = $this->helper->formatPerson($person->bestLabel);
	$onlyName = $this->helper->formatMultiLangStr($person->nameML);
	if (!empty($facetField)) {
		$facetsCode = $this->buffer->createFacetsCode([$facetField.':"'.$person->bestLabel.'"']);
		echo '<a href="'. $this->buildUrl('results', ['core'=>'biblio', 'facetsCode' =>$facetsCode ]) .'" data-toggle="tooltip" title="'. $this->transEsc('Show results using filter').': '.$this->helper->facetName('biblio', $facetField).' = '. $onlyName .'">'. $displayName .'</a> ';
		} else {
		echo '<a href="'. $this->buildUrl('results/biblio/', ['lookfor' =>$onlyName, 'type'=> 'allfields' ]) .'" data-toggle="tooltip" title="'. $this->transEsc('Look for').': '. $onlyName .'">'. $displayName .'</a> ';	
		}
	}
?>
<?php 
if (!empty($person->roles))
	if (is_Array($person->roles) or is_object($person->roles))
		foreach ($person->roles as $role)
			echo '<span class="role label label-info">'.$this->record->creativeRolesSynonyms($role).'</span> ';
?>

<div class="person-block" >
	<span id="button<?=$uid?>"><i class="<?= $ico ?>" ></i></span>
	<div class="cloud-info <?= $boxClass ?>">
		<div class="pi-body" id="personBox<?= $uid ?>" >
			<div class="pi-Desc">
				<?php if (empty($person->wikiQ) || ($person->wikiQ == 'not found')): ?>
					<p><?= $this->transEsc('We do not have a clear identifier or note about this person. You can try to get more information using external search engines') ?>:</p>
					<p class="searchersLinks">
					<?php 
						foreach ($this->configJson->settings->searchEngines as $searcherName=>$searcher)
							echo '<a href="'.$searcher->link.urlencode($person->name).'" target=_blank title="'.$this->transEsc('Search with').' '.$searcherName.'"><img src="'.$this->HOST.$searcher->logo.'"></a>';
					?></p>
				<?php else: ?>
					<?= $this->helper->loader2() ?> 
				<?php endif ?>
			</div>	
			
		</div>
	</div>
</div>
	
