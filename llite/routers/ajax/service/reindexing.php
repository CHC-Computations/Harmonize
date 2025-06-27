<?php 
if (empty($this)) die;


# apcu_store('import_progress', '50%');
/*
$key = ftok(__FILE__, 'a');
if (!empty($key)) {
	echo $key;
	$shm_id = shmop_open($key, "a", 0644, 1024);
	$progress = shmop_read($shm_id, 0, shmop_size($shm_id));
	shmop_close($shm_id);
	echo $progress;

	}
*/

$fileName = './import/outputfiles/counter.txt';

if (file_exists($fileName)) {
	$fileTime = filemtime($fileName);
	$file = file($fileName);
	foreach ($file as $line) {
		$tmp = explode(':', $line);
		$label = $tmp[0];
		unset($tmp[0]);
		$value = trim(implode(':',$tmp));
		$content[$label] = $value;
		}
	$progressWarningString = $progressWarningIco = '';	
	if (time()-$fileTime > 20) {
		$progressWarningIco = ' <i class="ph ph-warning text-warning"></i>';
		$progressWarningString = '<li style="background-color:red"><a style="color:yellow"><b><i class="ph ph-warning"></i></b> '.$this->transEsc('Warning. Reindexation has probably stopped').'!</a></li>';
		}
		
	if (!empty($content) && (count($content)>3)) {
		echo '<a class="dropdown-toggle" data-toggle="dropdown">'
			.$this->transEsc('reindexation in progress').': '.$content['persent done'].'% file '.$content['current file number'].' of '.$content['total files']
			.$progressWarningIco
			.'</a>';
		echo '<ul class="dropdown-menu">';
		if (!empty($content['step'])) {
			switch ($content['step']) {
				case '1' : $content['current process'] = 'pre-reading'; break;
				case '2' : $content['current process'] = 'wikidata items indexing'; break;
				case '3' : $content['current process'] = 'bibliografic data indexing'; break;
				}
			}
		
		if (!empty($content['current process']))
			echo '<h6 class="dropdown-header">'.$this->transEsc('Current process').': <b style="color:black">'.$content['current process'].'</b></h6>';
		echo '<h6 class="dropdown-header">'.$this->transEsc('Current file').': <b style="color:black">'.$content['current file name'].'</b></h6>';
		echo '<h6 class="dropdown-header">'.$this->transEsc('Files progress').': <b style="color:black">'.$content['current file number'].'</b> of '.$content['total files'].'</h6>';
		echo '<h6 class="dropdown-header">'.$this->transEsc('Progress in file done').': <b style="color:black">'.$content['persent done'].'</b>%</h6>';
		echo '<h6 class="dropdown-header">'.$this->transEsc('Records in file done').': <b style="color:black">'.$content['count'].'</b></h6>';
		echo '<h6 class="dropdown-header">'.$this->transEsc('Duration').': <b style="color:black">'.$content['work time'] ?? ''.'</b></h6>';
		echo $progressWarningString;
		echo '</ul>';
		}
	
	$this->addJS("
		const myTimeout = setTimeout(reLoad, 5000);
		function reLoad() {
			page.ajax('workInProgress', 'service/reindexing/status');
			}
		");
	} else {
	echo '<a class="dropdown-toggle" data-toggle="dropdown">No tasks in console mode</a>';	
	$this->addJS("
		const myTimeout = setTimeout(reLoad, 50000);
		function reLoad() {
			page.ajax('workInProgress', 'service/reindexing/status');
			}
		");
	}

?>
