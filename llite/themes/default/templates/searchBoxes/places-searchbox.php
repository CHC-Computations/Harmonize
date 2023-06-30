<?php
$facets = $this->getConfig('search');
foreach ($this->getIniParam('search', 'basicSearches') as $k=>$v) {
	$opt[$k] = $this->transEsc( $v );
	}
$cleanLink = '';
if (!empty($this->GET['lookfor']))
	$cleanLink = '<div class="searchRemoveBtn"><a href="'.$this->selfUrl($_SERVER['QUERY_STRING'], '').'" title="'.$this->transEsc('Clean up').'"><i class="glyphicon glyphicon-remove"></i></a></div>';

?>


<?php if (!empty($this->linkParts[3]) && ($this->linkParts[3] == 'advanced')): ?>
	<h1><?= $this->transEsc('Advanced search')?></h1>
<?php else: ?>	
	
	<nav class="searchbox hidden-print" >
		<form id="searchForm" class="searchForm" method="get" action="<?= $this->buildUrl('places/results')?>s" name="searchForm" autocomplete="off">
			<input type="hidden" name="type" value="placeName">
			<div class="searchInput" id="searchInput">
				<div class="searchInputMain"><input id="searchForm_lookfor" class="search-query " required type="text" name="lookfor" value="<?= $this->getParam('GET','lookfor')?>" placeholder="<?= $this->transEsc('Search for') ?>..."/></div>
				<?= $cleanLink ?>
				<div class="serachSubmitBtn">
					<button type="submit" class="btn btn-primary"><i class="ph-magnifying-glass-bold" aria-hidden="true"></i><span class="hidden-xs hidden-sd"> <?= $this->transEsc('Search') ?></span></button>
				</div>
				
			</div>
		</form>
	</nav>
<?php endif; ?>

<script>

const AC = new Autocomplete({ limit: 10, minInputLength: 2 });

AC(document.getElementById("searchForm_lookfor"), function achandler(query, callback) {
    const url = "<?= $this->buildUrl('autocomplete/places.json',['lookfor'=>null, 'type'=>null])?>q="+query;
    fetch(url).then(function(response) {
        return response.json();
    }).then(function(json) {
        callback( json.searchResults.map( place => ({
            text: place.name //,sub: place.names
        })  ) );
    });
});

</script>