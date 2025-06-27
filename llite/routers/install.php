<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');

$this->addClass('buffer', 	new buffer()); 
$this->addClass('solr', 	new solr($this)); 
$this->addClass('helper', 	new helper()); 


$status = new stdClass;
$status->php['version'] = phpversion();


$t = $this->psql->querySelect("SELECT version();");
if (is_Array($t)) {
	$versionStr = $t[0]['version'] ?? '';
	$tmp = explode('(', $versionStr);
	$status->SQL['version'] = $tmp[0];
	}




// solr: http://172.16.0.123:8983/solr/admin/cores?action=STATUS   - cores
// http://172.16.0.123:8983/solr/admin/info/system?wt=json  - version / memory /etc


?>
<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<div class="panel-header"><div class="container"><h1><?= $this->transEsc('Install') ?></h1></div></div>
<div class="container">
	<div class="main">
		<?= $this->render('panel/install.php', ['status'=>$status] ) ?>
	</div>
</div>

<?= $this->render('core/footer.php') ?>


