<?php 

if (empty($this)) die();
require_once ('./functions/class.lists.php');
$this->addClass('lists', new lists());

	
$blockName = 'post';
$CONDITIONS = [];
$ORDER = '';

if (!empty($this->GET['lang']))
	$CONDITIONS[] = 'lang='.$this->psql->string($this->GET['lang']);
if (!empty($this->GET['url']))
	$CONDITIONS[] = 'url='.$this->psql->string($this->GET['url']);
if (!empty($this->GET['author']))
	$CONDITIONS[] = 'author='.$this->psql->string($this->GET['author']);

if (!empty($this->GET['operator']))
	$CONDITIONS[] = "operator='".$this->psql->string($this->GET['operator'])."'";



$this->lists->saveConditions($blockName, $CONDITIONS);


echo '<div id="filterField">'.$this->helper->loader().'</div>';
echo '<div id="resultsField">'.$this->helper->loader().'</div>';

$this->addJS('
	page.phpResults = "/service/cms/post.results";
	page.phpFilters = "/service/cms/post.filters";
	page.phpAction = "/service/cms/post.edit";
	page.results("1", "parent_id,p_order");
	page.filters();
	');



?>
