<?php 


$id_link = $gps['geocode'];
$gps['geolink'] = "https://www.geonames.org/".$id_link;

if (!empty($gps['lat'])) {
	$gps['googlelink'] = "https://www.google.pl/maps/place/{$gps['lat']},{$gps['lon']}/@{$gps['lat']},{$gps['lon']},10z/";
	}
	

?>

<a href='<?= $this->basicUri('place') ?>/<?= $this->urlName($gps['name']) ?>/<?= $id_link ?>'><?= $gps['name'] ?></a> 
	
	
	
	<div class="person-block">
		<i class="glyphicon glyphicon-info-sign"></i>
		<div class="cloud-info">
			<h4><?= $gps['name'] ?></h4>
			<div class="more" id="author_box_<?=$uid?>"></div>
			
			<div class="bottom-links">
				<p>GPS: <?= $gps['lat']?>,<?= $gps['lon'] ?></p>
				<?php if (!empty($gps['geolink'])): ?>
					<a href="<?=$gps['geolink']?>"><?= $this->transEsc('See on') ?> Geonames</a>
				<?php endif; ?>
				<?php if (!empty($gps['googlelink'])): ?>
					<a href="<?=$gps['googlelink']?>" target=_blank><?= $this->transEsc('See on')?> Google Maps</a>
				<?php endif; ?>
			</div>
			
		</div>
	</div>
	