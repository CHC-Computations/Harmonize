<?php 
if (empty($this)) die;
require_once('functions/klasa.forms.php');
$this->addClass('forms', 	new forms()); 
$this->forms->values($this->GET);
$facets = $this->getConfig('search');

require_once('functions/klasa.helper.php');
$this->addClass('helper', new helper()); 

echo "<div style=''>";
echo $this->render('searchBoxes/search-searchbox.php');

echo "</div>";



?>
