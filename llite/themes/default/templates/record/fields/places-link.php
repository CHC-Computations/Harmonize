<?php
if (!empty($value)) {
	echo '<dl class="detailsview-item">
			<dt class="dv-label">'.$label.':</dt>
			<dd class="dv-value">
			';
	$list = false;
	if (count((array)$value)>1) {
		echo '<ol>';
		$list = true;
		}
	foreach ($value as $uid => $name) {
		# unset($name->roles);
		# echo $this->helper->pre($name);
		if ($list) echo '<li>';
		echo '<a href="">'.$this->helper->formatMultiLangStr($name->nameML).'</a>';
		/*
		<div class="person-block" >
			<span id="button<?=$uid?>"><i class="glyphicon glyphicon-info-sign" ></i></span>
			<div class="cloud-info <?= $boxClass ?>">
				<div class="pi-body" id="personBox<?= $uid ?>" >
					<div class="pi-Desc">
						<?php if (empty($person->wikiQ)): ?>
							<p><?= $this->transEsc('We do not have a clear identifier or note about this place. You can try to get more information using external search engines') ?>:</p>
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
		*/
		if ($list) echo '</li>';
		}
	if ($list) echo '</ol>';	
	echo '</dd>
		</dl>';
	}

?>