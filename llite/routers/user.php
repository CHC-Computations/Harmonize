<?php 
if (empty($this)) die;

require_once('functions/class.helper.php');

$this->addClass('buffer', 	new buffer()); 
$this->addClass('solr', 	new solr($this)); 


	
$this->setTitle("Libri ".$this->transEsc('users'));

$modul = $this->routeParam[0];



if ($modul == 'logout')
	$this->user->logOut();

?> 

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<?= $this->render('user/'.$modul.'.php') ?>

<?= $this->render('core/footer.php') ?>


