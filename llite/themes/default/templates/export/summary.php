<?php 

 $pages = explode(',', $this->getIniParam('search', 'pagination', 'rpp'));
 $sorts = $this->getIniParam('search', 'sortoptions');

 
?>
<div class="search-header hidden-print">
    <div class="search-stats">
        <span><?= $this->transEsc('Total results')?>: <b><?= number_format($this->solr->totalResults(),0,'','.'); ?></b>, </span>
		<span><?= $this->transEsc('showing')?>: <?= $this->solr->firstResultNo()?> - <?= $this->solr->lastResultNo()?> </span>
	</div>
	<div class="search-controls">
		<?= 
		$this->helper->dropDown(
			$pages,
			$this->getUserParam('limit'),
			$this->transEsc('Results per page')
			)
		?>
	</div>
	<div class="search-controls">
		<?= 
		$this->helper->dropDown(
			$sorts,
			$this->getUserParam('sortoption'),
			$this->transEsc('Sort by')
			)
		?>
	</div>

    <div class="search-controls">
		<div class="view-buttons hidden-xs">
            <span title="List&#x20;view&#x20;already&#x20;selected" data-toggle="tooltip">
				<i class="fa fa-list" alt="List"></i>
				<span class="sr-only">List</span>
            </span>
            &nbsp;
            <a href="?type=AllFields&amp;view=th-list" title="Switch&#x20;view&#x20;to&#x20;Compressed&#x20;list" >
				<i class="fa fa-th-list" alt="Compressed&#x20;list"></i>
				<span class="sr-only">Compressed list</span>
			</a>
            &nbsp;
            <a href="?type=AllFields&amp;view=grid" title="Switch&#x20;view&#x20;to&#x20;Grid" >
				<i class="fa fa-grid" alt="Grid"></i>
				<span class="sr-only">Grid</span>
			</a>
            &nbsp;
      </div>
	</div>
</div>

<?php 

 #echo "<pre>".print_r($this->solr,1)."</pre>";

?>