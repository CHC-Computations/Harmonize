<?php 
if (empty($this)) die;
require_once('functions/klasa.helper.php');
$this->addClass('helper', new helper()); 

$klucz = $this->routeParam[0];

$dir = $this->routeParam;
unset($dir[0]);
$ffolder = implode('/',$dir);
echo "klucz: <b>$klucz</b>, folder: <b>$ffolder</b><br>";
# echo "<prE>".print_R($this->routeParam,1)."</pre>";


if ($klucz=='covers') {
	echo 'You have no power to clean this folder<br/>';
	} else {

	$list = glob ("$ffolder/*");
	echo "folders to delete ".$c1 = count($list);
	$folder = current($list);
	if (($c1>0)&& is_dir($folder)) {
		$flist = glob ("$folder/*.*");
		echo ", files in $folder: ".count($flist);
		foreach ($flist as $file)
			unlink($file);
		rmdir($folder);
		
		$this->addJS("page.ajax('ajaxBox_{$klucz}', 'service/clearFolder/$klucz/$ffolder');");
		} else 
		$this->addJS("page.ajax('ajaxBox_{$klucz}', 'service/checkfolder/$klucz/$ffolder');");
	#echo " <button class='btn btn-danger' title='Empty this folder' OnClick=\"page.ajax('ajaxBox_{$klucz}', 'service/clearFolder/$klucz/$ffolder');\"><i class='glyphicon glyphicon-trash'></i></button>";
	echo '  ('.date("H:i:s", time()).')';		
	}			
		

