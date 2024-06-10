<?php 
if ($matchLevel>1) 
	$matchLevel = 1;
$percent = round($matchLevel*100);

$color = '#ff0000';

if ($percent == 100) 
	$color = '#008000';

if (($percent < 100) & ($percent>35))
	$color = '#00f000';

$printStr = '
		<div class="percent-box" title="'.$this->transEsc('Data matching').': '.$percent.'%" data-toggle="tooltip">
			<span class="overlaygrow" style="width:'.$percent.'%; background-color: '.$color.';"></span>
			<span class="label"><span class="value">'.$this->transEsc('Data matching').': '.$percent.'% </span></span>
		</div>
		';

?>
<?= $printStr ?>