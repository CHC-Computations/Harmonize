<?php 
if (empty($this)) die;
require_once('functions/klasa.helper.php');
require_once('functions/klasa.forms.php');
$this->addClass('solr', new solr($this->config));
$this->addClass('buffer', new marcBuffer()); 
$this->addClass('helper', 	new helper()); 

$this->setTitle($this->transEsc('Advanced search'));


if (!empty($this->GET['sj'])) {
	$_SESSION['advSearch']['form'] = json_decode($this->GET['sj'],true);
	
	}

echo $this->render('head.php');
echo $this->render('core/header.php');
echo $this->render('search/advanced.php', ['helpMenu' => $this->getMenu(100)] );
echo $this->render('core/footer.php');

?>