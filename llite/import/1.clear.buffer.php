<?php

$folders = [
	'marc', 'mrc', 'mrk', 'solr', 'json'
	];

foreach ($folders as $folder) {
	echo "\nreading file list from $folder...\n";
	$list = glob ("./files/$folder/*/*");
	$lp = 0;
	foreach ($list as $file) {
		$lp++;
		echo "$lp. $file\n";
		unlink ($file);
		}
	}