<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');

$export = $this->getConfig('export');
$facets = $this->getConfig('search');
$facets = $this->getConfig('facets');

$this->addClass('buffer', 	new buffer()); 
$this->addClass('solr', 	new solr($this)); 
$this->addClass('helper', 	new helper()); 


	
$this->setTitle("Libri ".$this->transEsc('users'));

if (!empty($this->routeParam[0])) {
	$a = explode('.', $this->routeParam[0]);
	$mod =  end($a);
	}
if (!empty($this->routeParam[1])) {
	$a = explode('.', $this->routeParam[1]);
	$mod .= '/'.end($a);
	}


?> 

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<div class="container">
	<div class="main">
		<?= $this->render('panel/'.$mod.'.php', ['facets' => $facets] ) ?>
	</div>
</div>

<?= $this->render('core/footer.php') ?>


