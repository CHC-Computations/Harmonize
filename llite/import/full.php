<?php 

if (count($argv) == 1) {
	include ('import.marc.1.php');
	include ('import.marc.2.php');
	include ('import.marc.3.php');
	} else {
	if (stristr($argv[1], '1'))	
		include ('import.marc.1.php');
	if (stristr($argv[1], '2'))	
		include ('import.marc.2.php');
	if (stristr($argv[1], '3'))	
		include ('import.marc.3.php');
	}

?>