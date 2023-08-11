<?php
#echo "<pre style='background-color:#fff; border:0px;'>".print_r($author,1)."</pre>";
$uid = uniqid();


if (!empty($author['wikiq'])) {
	$OMO = "OnMouseOver=\"page.ajax('personBox".$uid."', '/wiki/person/box/{$author['wikiq']}'); \"";
	$boxClass = 'personBox'.$author['wikiq'];
	} else {
	$OMO = ''; 
	$boxClass = 'personBoxEmpty';
	}

?>
<?php if (!empty($author['name'])): ?>
	<?php if (!empty($author['relation'])): ?> 
		<label><?= $author['relation'] ?></label> 
	<?php endif; ?>
	
	<a href="<?= $this->buildUrl('search/results/', ['lookfor' =>$author['name'], 'type'=> 'Author' ]) ?>" title="<?= $this->transEsc('look for')?>: <?= $author['name'] ?>"><?= $author['name'] ?></a> 
	<?php if (!empty($author['date'])): ?>
		<span class="date"><?= $author['date'] ?></span>
	<?php endif; ?>
	<?php if (!empty($author['role'])): ?>
		<?php 
		if (is_Array($author['role']))
			foreach ($author['role'] as $role)
				echo '<span class="role label label-info">'.$this->transEsc($role).'</span> ';
			else 
			echo '<span class="role label label-info">'.$this->transEsc($author['role']).'</span> ';	
		?>
	<?php endif; ?>

	<div class="person-block" >
		<span id="button<?=$uid?>"><i class="glyphicon glyphicon-info-sign" ></i></span>
		<div class="cloud-info <?= $boxClass ?>">
			<div class="pi-body" id="personBox<?= $uid ?>" >
				<div class="pi-Desc">
					<?php if (empty($author['wikiq'])): ?>
						<p><?= $this->transEsc('We do not have a clear identifier or note about this person. You can try to get more information using external search engines') ?>:</p>
						<p class="searchersLinks">
						<?php 
							foreach ($this->settings->searchEngines as $searcherName=>$searcher)
								echo '<a href="'.$searcher->link.urlencode($author['name']).'" target=_blank title="'.$this->transEsc('Search with').' '.$searcherName.'"><img src="'.$this->HOST.$searcher->logo.'"></a>';
						?></p>
					<?php else: ?>
						<?= $this->helper->loader2() ?> 
					<?php endif ?>
				</div>	
				
			</div>
		</div>
	</div>
	<?php 
		if (!empty($author['t'])) 
			echo $author['t'];
	?>	
<?php endif; ?>