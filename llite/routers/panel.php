<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');

$this->addClass('buffer', 	new buffer()); 
$this->addClass('solr', 	new solr($this)); 
$this->addClass('helper', 	new helper()); 


$path = 'routers/panel/'.implode('/',$this->routeParam);
if (file_exists($path.'/content.ini'))
	$iniContent = parse_ini_file($path.'/content.ini', true);
	else 
	$iniContent = [];

if (!empty($iniContent['name']))	
	$this->setTitle($iniContent['name'].' - '.$this->transEsc('User panel'));
	else 
	$iniContent['name'] = 'mysterious unknown site';
		
$mod = '';
foreach ($this->routeParam as $stepValue) {
	$a = explode('.', $stepValue);
	$modPath[] = end($a);
	}
$mod = implode('/',$modPath);	



?> 

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<div class="panel-header"><div class="container"><h1><?= $iniContent['name'] ?></h1></div></div>
<div class="container">
	<div class="main">
		<?= $this->render('panel/'.$mod.'.php' ) ?>
	</div>
</div>

<?= $this->render('core/footer.php') ?>


