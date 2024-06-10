<?php
#echo "<pre style='background-color:#fff; border:0px;'>".print_r($author,1)."</pre>";
$uid = uniqid();
#echo $this->helper->pre($author);

if (!empty($author->person)) {
	
	
	}

if (!empty($author->wikiQ)) {
	$OMO = "OnMouseOver=\"page.ajax('personBox".$uid."', '/wiki/person/box/{$author->wikiQ}'); \"";
	$boxClass = 'personBox'.$author->wikiQ;
	} else {
	$OMO = ''; 
	$boxClass = 'personBoxEmpty';
	}


?>
<?php 
if (!empty($author->name)) {
	$displayName = $this->helper->formatMultiLangStr($author->name);
	echo '<a href="'. $this->buildUrl('results/biblio/', ['lookfor' =>$displayName, 'type'=> 'Author' ]) .'" title="'. $this->transEsc('look for').': '. $displayName .'">'. $displayName .'</a> ';
	}
?>
<?php if (!empty($author->dates)): ?>
	<span class="date"><?= $author->dates ?></span>
<?php 
if (!empty($author->roles))
	if (is_Array($author->roles))
		foreach ($author->roles as $role)
			echo '<span class="role label label-info">'.$this->transEsc($role).'</span> ';
?>

<div class="person-block" >
	<span id="button<?=$uid?>"><i class="glyphicon glyphicon-info-sign" ></i></span>
	<div class="cloud-info <?= $boxClass ?>">
		<div class="pi-body" id="personBox<?= $uid ?>" >
			<div class="pi-Desc">
				<?php if (empty($author->wikiQ)): ?>
					<p><?= $this->transEsc('We do not have a clear identifier or note about this person. You can try to get more information using external search engines') ?>:</p>
					<p class="searchersLinks">
					<?php 
						foreach ($this->configJson->settings->searchEngines as $searcherName=>$searcher)
							echo '<a href="'.$searcher->link.urlencode($author->name).'" target=_blank title="'.$this->transEsc('Search with').' '.$searcherName.'"><img src="'.$this->HOST.$searcher->logo.'"></a>';
					?></p>
				<?php else: ?>
					<?= $this->helper->loader2() ?> 
				<?php endif ?>
			</div>	
			
		</div>
	</div>
</div>
	
<?php endif; ?>