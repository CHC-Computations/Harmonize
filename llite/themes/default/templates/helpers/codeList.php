<?php 
$i = 0;
foreach ($values as $value) {
	if (($firstDefault) && ($i == 0)) 
		$value = '<abbr title="'.$this->transEsc('default value').'">'.$value.'</abbr>';
	
	$tres[] = '<code>'.$value.'</code>';
	$i++;
	}
?>
<?= implode(', ', $tres) ?>