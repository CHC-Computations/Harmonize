
<div class="facets-header">
<span type="button" class="ico-btn" id="slideoutbtn" onclick="facets.SlideOut(); " title="<?= $this->transEsc('Hide facet panel') ?>"><i class="fa fa-angle-left"></i></span>

<span type="button" class="ico-btn" id="collapse_all_button" onclick="$('.collapse'+'.sidefl').collapse('hide'); " title="<?= $this->transEsc('Collapse all') ?>"><i class="fa fa-angle-double-up"></i></span>
<span type="button" class="ico-btn" id="uncollapse_all_button" onclick="$('.collapse'+'.sidefl').collapse('show'); " title="<?= $this->transEsc('Show all') ?>"><i class="fa fa-angle-double-down"></i></span>

<h4><?= $this->transEsc('Narrow Search') ?></h4>
</div>
<div id="facetsArea" class="facets-body">

<?php 
foreach ($this->settings->facets->facetsMenu as $gr=>$facet) {
	echo $this->transEsc($facet->name).'<br/><small>'.$facet->solr_index.'</small><br/>';
	}

echo $this->helper->pre($this->settings->facets);
?>
		
</div>
  

 