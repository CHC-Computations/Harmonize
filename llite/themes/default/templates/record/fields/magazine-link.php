<?php
#echo $this->helper->pre($persons);
$uid = uniqid();

if (!empty($item->wikiQ) && ($item->wikiQ !== 'not found')) {
	$OMO = "OnMouseOver=\"page.ajax('personBox".$uid."', '/wiki/person/box/{$item->wikiQ}'); \"";
	$boxClass = 'personBox'.$item->wikiQ;
	$ico = 'ph ph-info';
	} else {
	$OMO = ''; 
	$boxClass = 'personBoxEmpty';
	$ico = 'ph ph-question';
	}


?>
<?php 
if (!empty($item->title)) {
	$displayName = $item->title;
	if (!empty($item->bestLabel))
		$onlyName = explode('|', $item->bestLabel)[0];
		else 
		$onlyName = $item->title;	
	
	if (!empty($item->issn)) {
		$displayName.= ' <small>(ISSN: '.$item->issn.')</small>';
		$facetsCode = $this->buffer->createFacetsCode(['issn:"'.$item->issn.'"']);
		echo '<a href="'. $this->buildUrl('results', ['core'=>'biblio', 'facetsCode' =>$facetsCode ]) .'" data-toggle="tooltip" title="'. $this->transEsc('Show results using filter on ISSN').'">'. $displayName .'</a> ';
		} else {
		echo '<a href="'. $this->buildUrl('results/biblio/', ['lookfor' =>$onlyName, 'type'=> 'allfields' ]) .'" data-toggle="tooltip" title="'. $this->transEsc('Look for').': '. $onlyName .'">'. $displayName .'</a> ';	
		}

	}
if (!empty($item->relatedParts))
	echo ' <small>'.$item->relatedParts.'</small>';


?>
<?php 
if (!empty($item->roles))
	if (is_Array($item->roles) or is_object($item->roles))
		foreach ($item->roles as $role)
			echo '<span class="role label label-info">'.$this->record->creativeRolesSynonyms($role).'</span> ';
?>

<div class="person-block" >
	<span id="button<?=$uid?>"><i class="<?= $ico ?>" ></i></span>
	<div class="cloud-info <?= $boxClass ?>">
		<div class="pi-body" id="personBox<?= $uid ?>" >
			<div class="pi-Desc">
				<?php if (empty($item->wikiQ) || ($item->wikiQ == 'not found')): ?>
					<p><?= $this->transEsc('We do not have a clear identifier or note about this item. You can try to get more information using external search engines') ?>:</p>
					<p class="searchersLinks">
					<?php 
						foreach ($this->configJson->settings->searchEngines as $searcherName=>$searcher)
							echo '<a href="'.$searcher->link.urlencode($item->title).'" target=_blank title="'.$this->transEsc('Search with').' '.$searcherName.'"><img src="'.$this->HOST.$searcher->logo.'"></a>';
					?></p>
				<?php else: ?>
					<?= $this->helper->loader2() ?> 
				<?php endif ?>
			</div>	
			
		</div>
	</div>
</div>
	
