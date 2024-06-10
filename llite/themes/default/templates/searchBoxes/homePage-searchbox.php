<?php

foreach ($this->getIniParam('search', 'basicSearches') as $k=>$v) {
	$opt[$k] = $this->transEsc( $v );
	}
$cleanLink = '';
if (!empty($this->GET['lookfor']))
	$cleanLink = '<div class="searchRemoveBtn"><a href="'.$this->selfUrl($_SERVER['QUERY_STRING'], '').'" title="'.$this->transEsc('Clean up').'"><i class="glyphicon glyphicon-remove"></i></a></div>';


?>


<nav class="nav searchbox hidden-print" id="search-collapse">
	<form id="searchForm" class="searchForm" method="get" action="<?= $this->buildUrl('results', ['core'=>'biblio']) ?>" name="searchForm" autocomplete="off">
		<div class="searchInput" id="searchInput">
			<div class="searchInputMain"><input id="searchForm_lookfor" class="search-query autocomplete ac-auto-submit" required type="text" name="lookfor" value="<?= $this->getParam('GET','lookfor')?>"  aria-label="Hasła" placeholder="<?= $this->transEsc('Search for') ?>..."/></div>
			<?= $cleanLink ?>
			<div>
			<?= $this->forms->select(
						'type', 
						$opt , 
						['id'=>'searchForm'])  
				?>
			
			</div>
			<div class="serachSubmitBtn">
				<button type="submit" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i><span class="hidden-xs hidden-sd"> <?= $this->transEsc('Search') ?></span></button>
			</div>
		</div>

		
		<nav class="search-menu">
			<a href="<?= $this->basicUri('/search/advanced')?>" rel="nofollow"><?= $this->transEsc('Advanced search') ?></a>
		</nav>
	</form>
</nav>
<br/><br/>