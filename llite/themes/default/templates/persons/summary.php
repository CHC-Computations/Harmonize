<div class="search-header hidden-print">
	<div class="sidebar-buttons">
		<button type="button" id="slideinbtn" class="ico-btn" OnClick="facets.SlideIn();" title="<?= $this->transEsc('Show side panel')?>"><i class="ph-sidebar-simple-bold"></i></button>
	</div>
    <div class="main-title"><b><?= $this->transEsc('Persons') ?></b></div>
	<div class="search-stats">
        <span><?= $this->transEsc('Total results')?>: <b><?= number_format($resultsCount,0,'','.'); ?></b>, </span>
		<span><?= $this->transEsc('showing')?>: <?= $this->getCurrentPage()*$this->getUserParam('limit')-$this->getUserParam('limit')+1?> - <?= $this->getCurrentPage()*$this->getUserParam('limit')?> </span>
	</div>
</div>