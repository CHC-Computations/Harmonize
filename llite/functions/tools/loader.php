<?php 

require_once('../klasa.buffer.php');
require_once('../klasa.importer.php');


$imp = new importer($pgs=null);
$buffer = new marcBuffer();

$imp->mdb([
		'host'=>'localhost', 
		'dbname'=>'vufind', 
		'user'=>'liteU', 
		'password'=>'9hsprsnBKVapzZW'
		]);
		
$buffer->setSql($imp->sql);
		
		
?>