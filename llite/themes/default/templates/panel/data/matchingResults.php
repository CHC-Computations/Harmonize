<?php 
if (empty($this)) die();
require_once ('./functions/class.lists.php');
$this->addClass('lists', new lists());


$blockName = 'matching.results';
$CONDITIONS = [];
$ORDER = '';

if (!empty($this->GET['sstring']))
	$CONDITIONS[] = "(s.string ILIKE '%{$this->GET['sstring']}%' 
				OR s.clearstring ILIKE '%{$this->GET['sstring']}%' 
				OR match_result={$this->psql->string($this->GET['sstring'])}) ";
if (!empty($this->GET['match_source']))
	$CONDITIONS[] = 'match_source='.$this->psql->string($this->GET['match_source']);
if (!empty($this->GET['rec_type_name']))
	$CONDITIONS[] = 'rec_type_name='.$this->psql->string($this->GET['rec_type_name']);
if (!empty($this->GET['match_type']))
	$CONDITIONS[] = 'match_type='.$this->psql->string($this->GET['match_type']);

if (!empty($this->GET['match_level']))
	$CONDITIONS[] = "match_level='".intval($this->GET['match_level'])."'";



$this->lists->saveConditions($blockName, $CONDITIONS);



echo '<div id="filterField">'.$this->helper->loader().'</div>';
echo '<div id="resultsField">'.$this->helper->loader().'</div>';

$this->addJS('
page.phpResults = "/service/data/matching.results";
page.phpFilters = "/service/data/matching.results.filters";
page.phpAction = "/service/data/matching.results.edit";
page.results("0", "clearstring");
page.filters();
');

### GO TO: https://testlibri.ucl.cas.cz/pl/panel/30.data/12.matchingResults?&rec_type_name=person&sstring=mickiewicz

?>
