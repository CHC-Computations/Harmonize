<?php 
if (empty($this)) die;
if (file_exists('import/outputfiles/counter.txt')) {
	$count = intval(file_get_contents('import/outputfiles/counter.txt'));
	echo number_format($count, 0, '', '.');
	$this->addJS("
		const myTimeout = setTimeout(reLoad, 5000);
		function reLoad() {
			page.ajax('workInProgress', 'service/reindexing/status');
			}
		");
	} else {
	echo '<span style="font-size:0.5em">No background work</span>';	
	}