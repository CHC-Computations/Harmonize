<?php
include('config.php');

$pattern = $destination_path."/solr/*/*.json";
echo "reading file list; $pattern ...\n";
$list = glob ($flist);

$lp = 0;
foreach ($list as $file) {
	$lp++;
	echo "$lp. $file\n";
	unlink ($file);
	}



$pattern = "errors/*.json"
echo "reading file list; $pattern ...\n";
$list = glob ($flist);

$lp = 0;
foreach ($list as $file) {
	$lp++;
	echo "$lp. $file\n";
	unlink ($file);
	}