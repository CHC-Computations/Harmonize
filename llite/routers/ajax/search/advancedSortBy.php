<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.forms.php');
$this->addClass('helper', new helper()); 
$this->addClass('forms', new forms()); 

$currentCore = 'biblio';

$this->addJS('$("#sortbyBox").css("opacity","1"); ');

$search = $this->getConfig('search');
$facets = $this->getConfig('facets');

#$sortOptions = $this->getIniParam('search', 'sortnames');
#$sortOptions = $this->configJson->$currentCore->summaryBarMenu->sorting;
$sortOptions = [];
if (!empty($this->configJson->$currentCore->summaryBarMenu->sorting->optionsAvailable))
	foreach ($this->configJson->$currentCore->summaryBarMenu->sorting->optionsAvailable as $k=>$v)
		$sortOptions[$k] = $this->transEsc($v->name);

$content = '';
if (!empty($_SESSION['advSortBy'])) {
	$sorts = $_SESSION['advSortBy'];
	$sorts[] = '';
	} else 
	$sorts = ['1' => ''];
$this->forms->values($sorts);

foreach ($sorts as $sortKey=>$sortValue) {
	#echo "$sortKey<pre>".print_R($sortOptions,1)."</prE>";
	$content .='<div class="row">';
	if ($sortKey>1) 
		$content.='<div class="col-sm-3 text-right">'.$this->transEsc('then').'</div>';
		else 
		$content.='<div class="col-sm-3 text-right"></div>';
	$content .='<div class="col-sm-5">';
	$content .= $this->forms->select(
				$sortKey, 
				$sortOptions,
				[ 
				'onChange' => "advancedSearch.AddRemove('sortby', this.value, 'add', '$sortKey');",
				'class' => 'form-control'
				]
				);
	$content .='</div>';
	unset($sortOptions[$sortValue]);
	if (($sortKey>1)&($sortKey<max(array_keys($sorts)))) {
		$content .='<div class="col-sm-4">';
		$OC ="advancedSearch.AddRemove('sortby', '$sortValue', 'remove', '$sortKey');";
		$content .='<button class="btn btn-danger" type="button" OnClick="'.$OC.'" title="'.$this->transEsc('remove row').'"><i class="ph-minus-bold"></i></button>';
		$content .='</div>';
		}
	$content .='</div>';
	}





echo $this->helper->panelCollapse(
					'sortby',
					'<b>'.$this->transEsc('Sort by').'</b>',
					$content
					);


# echo "this<pre>".print_r($this,1).'</pre>';
# echo "_POST<pre>".print_r($_POST,1).'</pre>';
# echo "SESSION<pre>".print_r($_SESSION,1).'</pre>';
# echo "results<pre>".print_r($results,1).'</pre>';
# echo "facets.ini<pre>".print_r($facets,1).'</pre>';

?>