<?php if (is_array($menu)): ?>
<?php foreach ($menu as $row): ?>
	<li class="core-menu-item <?php if ($row['url']==$currPage):?>active<?php endif;?>">
		<a href="<?=$this->baseUrl('home/'.$row['url'])?>"><?= $row['title'] ?></a>
	</li>
<?php endforeach; ?>
<?php endif; ?>
	
	
	
	