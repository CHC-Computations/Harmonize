<?php 

#echo "<pre>".print_R($author,1)."</pre>";
#echo "<pre>".print_R($person,1)."</pre>";

$key = $author['field'];
$ukey = $uid = uniqid();


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

	
	<div class="person-block <?=$key?>" id="point_<?=$ukey?>" >
		<!--i class="glyphicon glyphicon-info-sign" OnMouseOver="results.personBox('<?=$key?>','<?=$ukey?>')"></i>  </i-->
	</div>

	<?php 
		if (!empty($author['t'])) 
			echo $author['t'];
	?>	
<?php endif; ?>

