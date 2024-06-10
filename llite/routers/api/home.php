<?php
$this->addClass('buffer', 	new buffer()); 
$this->addClass('helper', 	new helper()); 





echo $this->render('head.php');
echo $this->render('core/header.php');

if ($this->templatesExists('cms/api/'.$this->userLang.'-instruction.php'))
	echo $this->render ('cms/api/'.$this->userLang.'-instruction.php');
	else if ($this->templatesExists('cms/api/'.$this->defaultLanguage.'-instruction.php'))
	echo $this->render ('cms/api/'.$this->defaultLanguage.'-instruction.php');

echo $this->render('core/footer.php');


?>