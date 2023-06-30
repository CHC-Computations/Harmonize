<?php 
$menu 		= $this->getMenu(1);
$currPage 	= $this->getCurrentPost();
?> 
<?php if (is_array($menu)): ?>
	<div style="">
		<ul class="nav nav-pills" >
		<?php foreach ($menu as $row): ?>
			<li <?php if ($row['url']==$currPage['url']):?>class="active" <?php endif;?>>
				<a href="<?=$this->baseUrl('home/'.$row['url'])?>"><?= $row['title'] ?></a>
			</li>
		<?php endforeach; ?>
		</ul>	
	</div>
<?php endif; ?>
	