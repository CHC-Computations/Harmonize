<?php 
if (empty($this)) die;
require_once('functions/klasa.helper.php');
$this->addClass('helper', new helper()); 

$klucz = $this->routeParam[0];

$dir = $this->routeParam;
unset($dir[0]);
$ffolder = implode('/',$dir);
#echo "klucz: <b>$klucz</b>, folder: <b>$ffolder</b><br>";
#echo "<prE>".print_R($this->routeParam,1)."</pre>";



function folderSize ($dir) {
    $size = 0;
	foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : folderSize($each);
		}
    return $size;
	}

function folderSizeSimple ($dir) {
    $size = 0;
	foreach (glob("$dir/*", GLOB_NOSORT) as $each) {
        $size += filesize($each);
		}
    return $size;
	}





	###################################################################################################

			
	if (empty($_SESSION[$klucz])) {

		$_SESSION[$klucz]['list'] = $lista = glob ("$ffolder/*");
		$_SESSION[$klucz]['count'] = count($lista);
		$_SESSION[$klucz]['size'] = 0;
		$_SESSION[$klucz]['filesCount'] = 0;
		
		echo "counting folder <b>$ffolder</b>";
		echo "<script>page.ajax('ajaxBox_{$klucz}', 'service/checkfolder/$klucz/$ffolder');</script>";
		} else {
		
		$lista = $_SESSION[$klucz]['list'];
		$maks = $_SESSION[$klucz]['count'];
		
		if (is_array($lista)) {
			$poz = current($lista);
			$key = key($lista);
			unset($_SESSION[$klucz]['list'][$key]);
			#echo "<prE>".print_r($_SESSION[$klucz]['list'],1).'</pre>';
			$_SESSION[$klucz]['size'] += folderSizeSimple($poz);
			$_SESSION[$klucz]['filesCount'] += count(glob($poz."/*.*"));
			
			$fullSize = $_SESSION[$klucz]['size'];	
			$fullCount = $_SESSION[$klucz]['filesCount'];	
			
			$count = $maks - count($lista);
			
			echo "<div class='row'>";
			echo "<div class=col-sm-3><b>$ffolder</b></div>";
			echo "<div class='col-sm-2 text-right'>files: ".number_format($fullCount, 0, '', '.')."</div>";
			echo "<div class='col-sm-3 text-right'>size: <b>".$this->helper->fileSize($fullSize)."</b></div>";
			
			if ($count < $maks) {
				echo "<div class=col-sm-4>(checking: $poz)</div>";
				echo "</div>";
				echo $this->helper->progressThin($count,$maks);	
				echo "<script>page.ajax('ajaxBox_{$klucz}', 'service/checkfolder/$klucz/$ffolder');</script>";
				} else {
				
				unset($_SESSION[$klucz]);
				echo "<div class='col-sm-4 text-right'>";
				echo " <div class='btn-group'>";
				echo " <button class='btn btn-info' title='Empty this folder' OnClick=\"page.ajax('ajaxBox_{$klucz}', 'service/checkfolder/$klucz/$ffolder');\"><i class='glyphicon glyphicon-refresh'></i></button>";
				echo " <button class='btn btn-danger' title='Empty this folder' OnClick=\"page.ajax('ajaxBox_{$klucz}', 'service/clearFolder/$klucz/$ffolder');\"><i class='glyphicon glyphicon-trash'></i></button>";
				echo "</div>";
				echo "</div>";
				echo "</div>";
			
				}
		
			
			}
		}	
		

