

<div class="subfacetCascade">

	<div class="subfacetCascade-header">
	<input type="text" placeholder="<?=$this->transEsc('Search in')?> <?=$this->transEsc($facet->name)?>" id="subfacetInput<?= $facet->solr_index ?>" OnKeyUp="facets.cascadeSearch('<?= $this->facetsCode ?>', '<?= $facet->solr_index ?>', '<?= $stepSetting->formatter ?>', '<?= $stepSetting->translated ?>');">
	<button type="button" title="<?=$this->transEsc('More options')?>" OnClick="facets.InModal('<?= $this->transEsc($facet->name) ?>', '<?= $facet->solr_index ?>');">
        <i class="ph-chart-pie-slice-bold"></i> 
		<?php if (!empty($total)): ?>
			<span id="subfacetCascadeCounter<?=$facet->solr_index?>"><?= $this->helper->badgeFormat($total) ?></span>
		<?php endif ?>
      </button>
	</div>
	<div class="subfacetCascade-searchArea" id="subfacetCascadeResults_<?=$facet->solr_index?>">
	<?=implode('', $lines)?>
	</div>
</div>
