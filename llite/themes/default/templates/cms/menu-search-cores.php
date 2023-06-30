<?php if (is_array($menu)): ?>
<?php foreach ($menu as $url=>$rowName): ?>
	<li class="core-menu-item <?php if ($url == $currPage):?>active<?php endif;?>">
		<a href="<?=$this->baseUrl($url.'/results/')?>"><?= $this->transEsc($rowName) ?></a>
	</li>
<?php endforeach; ?>
<?php endif; ?>
	
	
	 
	