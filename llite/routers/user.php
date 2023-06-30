<?php 
if (empty($this)) die;

require_once('functions/klasa.helper.php');

$export = $this->getConfig('export');
$facets = $this->getConfig('search');
$facets = $this->getConfig('facets');

$this->addClass('buffer', 	new marcBuffer()); 
$this->addClass('solr', 	new solr($this->config)); 


	
$this->setTitle("Libri ".$this->transEsc('users'));

$modul = $this->routeParam[0];

if ($modul == 'logout')
	$this->user->logOut();

?> 

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<?= $this->render('user/'.$modul.'.php', ['facets' => $facets] ) ?>

<?= $this->render('core/footer.php') ?>


