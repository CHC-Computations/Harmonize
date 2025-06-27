<?php
#ini_set('display_errors', 'On');
#error_reporting(E_ALL);
error_reporting(0);

session_start();
require_once('config/db.php');
require_once('functions/class.user.php');
require_once('functions/class.cms.php');
require_once('functions/class.buffer.php');
require_once('functions/class.solr.php');
require_once('functions/class.helper.php');
require_once('functions/class.pgsql.php');
require_once('functions/class.marc21.php');


$cms = new CMS();


$cms->addClass('psql', new postgresql($psqldb));
$cms->addClass('user', new user($cms) );
$cms->addClass('helper', new helper($cms) );

$cms->user->isLoggedIn();
 
if (!empty($cms->redirectTo)) {
	header( "Location: ".$cms->redirectTo ) ;
	#echo ( "Location: ".$cms->redirectTo ) ;
	}

$cms->head();
echo $cms->content();
echo $cms->footer();


 
/*
if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
	$ip = $_SERVER['HTTP_X_REAL_IP'];
	file_put_contents('./files/logi/trafic/'.date("Y-m-d").'.log', date("Y-m-d H:i:s").';'.$ip.';'.$_SERVER['REQUEST_URI'].';"'.$_SERVER['HTTP_USER_AGENT'].'";'."\n", FILE_APPEND); 
	} 
*/	

?>