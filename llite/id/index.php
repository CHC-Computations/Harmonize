<?php 
#ini_set('display_errors', 'On');
#error_reporting(E_ALL);
$settings = json_decode(file_get_contents('../config/settings.json'));

$tmp = explode('/',$_SERVER['REQUEST_URI']);
$id = end($tmp);

$userLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2);
$langPath = '../languages/';
$langs = glob($langPath.'*');
foreach ($langs as $lang) {
	$tmp = str_replace($langPath, '', $lang);
	$Tlangs[$tmp] = $tmp;
}


if (in_array($userLang, $Tlangs))
	header( "Location: ".$settings->www->host.$userLang.'/search/record/'.$id.'.html' ) ;
	else 
	header( "Location: ".$settings->www->host.'en/search/record/'.$id.'.html' ) ;

?>