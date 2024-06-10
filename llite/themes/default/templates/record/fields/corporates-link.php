<?php
#echo $this->helper->pre($corporates);
$uid = uniqid();


if (!empty($corporates->wikiQ)) {
	$OMO = "OnMouseOver=\"page.ajax('personBox".$uid."', '/wiki/person/box/{$corporates->wikiQ}'); \"";
	$boxClass = 'personBox'.$corporates->wikiQ;
	} else {
	$OMO = ''; 
	$boxClass = 'personBoxEmpty';
	}


?>
<?php 
if (!empty($corporates->name)) {
	$displayName = $this->helper->formatMultiLangStr($corporates->name);
	echo '<a href="'. $this->buildUrl('results/biblio/', ['lookfor' =>$displayName, 'type'=> 'Author' ]) .'" title="'. $this->transEsc('look for').': '. $displayName .'">'. $displayName .'</a> ';
	}
?>
<?php 
if (!empty($corporates->roles))
	if (is_Array($corporates->roles) or is_object($corporates->roles))
		foreach ($corporates->roles as $role)
			echo '<span class="role label label-info">'.$this->record->creativeRolesSynonyms($role).'</span> ';
?>

<div class="person-block" >
	<span id="button<?=$uid?>"><i class="glyphicon glyphicon-info-sign" ></i></span>
	<div class="cloud-info <?= $boxClass ?>">
		<div class="pi-body" id="personBox<?= $uid ?>" >
			<div class="pi-Desc">
				<?php if (empty($corporates->wikiQ)): ?>
					<p><?= $this->transEsc('We do not have a clear identifier or note about this corporate. You can try to get more information using external search engines') ?>:</p>
					<p class="searchersLinks">
					<?php 
						foreach ($this->configJson->settings->searchEngines as $searcherName=>$searcher)
							echo '<a href="'.$searcher->link.urlencode($corporates->name).'" target=_blank title="'.$this->transEsc('Search with').' '.$searcherName.'"><img src="'.$this->HOST.$searcher->logo.'"></a>';
					?></p>
				<?php else: ?>
					<?= $this->helper->loader2() ?> 
				<?php endif ?>
			</div>	
			
		</div>
	</div>
</div>
	
