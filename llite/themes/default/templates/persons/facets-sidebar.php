<?php 
/*
$labelsList = [];
if (is_array($labels))
	foreach ($labels as $labelName=>$labelCount) {
		$labelsList[] = '<div class="facetTop"><a href=""><span class="text">'.$this->transEsc($labelName).'</span> <span class="badge">'.$this->helper->numberFormat($labelCount).'</span></a></div>';
		}
	*/
	$labelsList = [];
?>

<div class="facets-header">
<span type="button" class="ico-btn" id="slideoutbtn" onclick="facets.SlideOut(); " title="<?= $this->transEsc('Hide facet panel') ?>"><i class="fa fa-angle-left"></i></span>

<span type="button" class="ico-btn" id="collapse_all_button" onclick="$('.collapse'+'.sidefl').collapse('hide'); " title="<?= $this->transEsc('Collapse all') ?>"><i class="fa fa-angle-double-up"></i></span>
<span type="button" class="ico-btn" id="uncollapse_all_button" onclick="$('.collapse'+'.sidefl').collapse('show'); " title="<?= $this->transEsc('Show all') ?>"><i class="fa fa-angle-double-down"></i></span>

<h4><?= $this->transEsc('Narrow Search') ?></h4>
</div>


 
<?= 
$this->helper->PanelCollapse(
			uniqid(),
			$this->transEsc("Persons lists"),
			implode('',$labelsList)
			);
	?>

	
