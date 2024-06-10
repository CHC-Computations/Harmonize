<?php if (is_object($menu)): ?>
	<?php foreach ($menu as $url=>$row): ?>
		<li class="core-menu-item <?php if ($row->url == $currPage):?>active<?php endif;?>">
			<a href="<?=$this->baseUrl('results/'.$row->url.'/')?>"><?= $this->transEsc($row->name) ?></a>
		</li>
	<?php endforeach; ?>
<?php endif; ?>
	
	
	 
	