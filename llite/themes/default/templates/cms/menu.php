<?php if (is_array($menu)): ?>
	<p>&nbsp;</p>
	<div style="">
		<ul class="nav nav-pills nav-right" style="float:right; ">
		<?php foreach ($menu as $row): ?>
			<li <?php if ($row['url']==$currPage):?>class="active" <?php endif;?>>
				<a href="<?=$this->baseUrl('home/'.$row['url'])?>"><?= $row['title'] ?></a>
			</li>
		<?php endforeach; ?>
		</ul>	
	</div>
<?php endif; ?>
	
	
	
	