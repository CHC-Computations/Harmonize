<?php

$helperFile = json_decode(file_get_contents('./config/helper/meaning_of_tags_in_configuration_files.json'));



drawHelp($this,$helperFile,$this->routeParam); 


$this->addJS("
	$('.list-menu-item').removeClass('active');
	$('#{$this->GET['id']}').addClass('active');
	");
/*
echo $this->helper->pre($this->routeParam);
echo $this->helper->pre($this->GET);
echo $this->helper->pre($helperFile);
*/


function drawHelp($handler, $helperFile, $path) {
	foreach ($path as $k=>$step) {
		$class = 'default';
		if ($step == end($path))
			$class = 'success';
		if (!empty($helperFile->$step) && is_string($helperFile->$step))
			echo $handler->helper->alert($class, '<h4>'.$step.'</h4>'.$helperFile->$step);
			else if (!empty($helperFile->$step->_desc) && is_string($helperFile->$step->_desc))
				echo $handler->helper->alert($class, '<h4>'.$step.'</h4>'.$helperFile->$step->_desc);
		if (!empty($helperFile->$step) && is_object($helperFile->$step)) {	
			unset($path[$k]);
			drawHelp($handler, $helperFile->$step, $path);
			}
	
	}
	
}

?>