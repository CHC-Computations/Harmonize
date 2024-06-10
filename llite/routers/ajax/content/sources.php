<?php

$filePath = './import/data/';
$orderTable = [];
$sourceTable = [];
$this->defaultLanguage = 'en';

$list = glob ($filePath.'*.mrk');

foreach ($list as $file) {
	$fileName = str_replace($filePath, '', $file);
	$tmp = explode('_', $fileName);
	$sourceCode = current($tmp);
	$sourceTable[$sourceCode][] = ['fileName' => $fileName, 'fileWithPath' => substr($file, 1)];
	
	@$orderTable[$sourceCode] += fileSize($file);
	#echo $sourceCode.' '.$fileName.'</br>';
	
	
	}	
arsort($orderTable);
foreach ($orderTable as $code=>$value) {
	$templatePath = '/cms/sources/'.$this->userLang.'-'.$code.'.php';
	$templateDef = '/cms/sources/'.$this->defaultLanguage.'-'.$code.'.php';
	
	echo '<div class="row"><div class="col-sm-8">';
	if ($this->templatesExists($templatePath))
		echo $this->render($templatePath);	
		else if ($this->templatesExists($templateDef))
		echo $this->render($templateDef);	
		else echo '<h3><small>'.$this->transEsc('No info page about').' </small>'.$code.'<small> source.</small></h3>';
	echo '</div><div class="col-sm-4" style="padding-top:40px;">';	
	foreach ($sourceTable[$code] as $source)
		echo '<a href="'.$this->buildUrl('download'.$source['fileWithPath']).'">'.$source['fileName'].'</a><br/>';
	echo '</div></div>';
	}
	
echo '<p class="text-right" style="margin-top:5em; margin-bottom:2em;">'.$this->transEsc('The order in which the sources are presented depends on the volume of resources made available').'.<br/> '.$this->transEsc('All source files presented here are available under licence the').' <a href="https://creativecommons.org/publicdomain/zero/1.0/">'.$this->transEsc('Creative Commons CC0 License').'</a>.</p>'; 
#echo $this->helper->pre($sourceTable);
#echo $this->defaultLanguage. ' '. $this->userLang;
?>