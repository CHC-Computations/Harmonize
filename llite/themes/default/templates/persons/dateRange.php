<?php

$d = $this->helper->onlyYear($d);
$b = $this->helper->onlyYear($b);

if (!empty($b) && !empty($d))
	$res = '('.$b.'-'.$d.')';
	elseif (empty($b) && !empty($d))
	$res = '(???-'.$d.')';
	elseif (!empty($b) && empty($d))
	$res = '('.$b.'-&nbsp;)';
	else 
	$res = '';	
?>
<?= $res ?>