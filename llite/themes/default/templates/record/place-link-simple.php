<?php 
#echo '<pre>'.print_R($place,1).'</pre>'
$ukey = uniqid(); //$this->helper->shortHash($place);
?>
	<a href="<?= $this->buildUrl('search/results/', ['lookfor' =>$place, 'type'=> 'AllFields' ]) ?>" title="<?= $this->transEsc('look for')?>: <?= $place ?>"><?= $place ?></a> 
	<div class="person-block" id="point_<?=$ukey?>" >
		<i class="glyphicon glyphicon-info-sign" ></i>
		<div class="cloud-info"> 
			<div class="bottom-links" >
				<a href="http://testlibri.ucl.cas.cz/lite/pl/places/record/<?=$this->urlName($place)?>?place=<?=urlencode($place)?>" title="<?=$this->transEsc('More with LiBRI places')?>"><i class="ph-map-pin-line-bold"></i> <?=$this->transEsc('More with LiBRI places')?></a>
				<a href="https://www.google.com/search?q=<?=$place?>" target="_blank" title="<?=$this->transEsc('search with google')?>"><i class="ph-google-logo-bold"></i>  <?=$this->transEsc('search with google')?></a>
			</div>	

		</div>
	</div>
	
	

