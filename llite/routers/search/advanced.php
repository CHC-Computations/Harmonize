<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.forms.php');
$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer()); 
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