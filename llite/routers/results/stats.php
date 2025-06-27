<?php 
if (empty($this)) die;

require_once('functions/class.helper.php');
require_once('functions/class.forms.php');
require_once('functions/class.converter.php');
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.bookcart.php');

$this->addClass('buffer', 	new buffer($this)); 
$this->addClass('solr', 	new solr($this)); 
$this->addClass('helper', 	new helper($this)); 
$this->addClass('forms', 	new forms($this)); 
$this->addClass('convert', 	new converter($this));
$this->addClass('bookcart',	new bookcart());


$dataArray = $this->psql->querySelect("SELECT * FROM awstats ORDER BY year, month;");

?>
<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<?= $this->render('cms/awstats/home.php', ['data'=>$dataArray]) ?> 
<?= $this->render('helpers/report.error.php') ?> 
<?= $this->render('core/footer.php') ?>
