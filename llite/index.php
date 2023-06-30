<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

session_start();
require_once('config/db.php');
require_once('functions/klasa.user.php');
require_once('functions/klasa.cms.php');
require_once('functions/klasa.buffer.php');
require_once('functions/klasa.solr.php');
require_once('functions/klasa.helper.php');
require_once('functions/klasa.pgsql.php');
require_once('functions/klasa.marc21.php');


$cms = new CMS();

$cms->mdb($mdb);
$cms->addClass('psql', new postgresql($psqldb));
$cms->addClass('user', new user() );
$cms->user->setSql($cms->sql);
$cms->user->isLoggedIn();
$cms->addClass('helper', new helper() );
 
if (!empty($cms->redirectTo)) {
	header( "Location: ".$cms->redirectTo ) ;
	}

$cms->head();
echo $cms->content();
echo $cms->footer();

# echo "<pre>".print_R($_SERVER,1)."</pre>";
 
?>