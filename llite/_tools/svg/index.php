<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

#echo '<pre>'.print_r($_SERVER,1).'</pre>';
$params = $_SERVER['REQUEST_URI'] ?? '';
if (stristr($params, '/ready/'))
	die();

if (!empty($params)) {
	$tmp = explode('svg/', $params);
	$fileName = $tmp[1];
	$readyFileName = 'ready/'.$fileName; 
	$reLocation = 'https://'.$_SERVER['HTTP_HOST'].str_replace('svg/', 'svg/ready/', $params);
	#echo 'fileName: '.$fileName.'</br>';
	#echo 'readyFileName: '.$readyFileName.'</br>';
	#echo $reLocation.'</br>';
	if (file_exists($readyFileName)) {
		#echo 'reloction:'.$reLocation.'</br>';
		header( "Location: ".$reLocation ) ;
	
		} else {
		$pieParams = explode('-', $tmp[1]);
		
		
		$graphMode = end($pieParams);
		unset($pieParams[count($pieParams)-1]);
		
		foreach ($pieParams as $colorNvalue) {
			$tmp = explode('_',$colorNvalue);
			$pie[$tmp[0]] = $tmp[1];
			}
		if (!empty($pie)) {
			$total = array_sum($pie);	
			#echo '<pre>'.print_r($pie,1).'</pre>';
			$offs = 0;
			$cir[] = '<circle id="pie_body" style="stroke:#888888;stroke-opacity:1;fill:#888888" cx="50%" cy="50%" r="40%" stroke-width="2%" />';
			foreach ($pie as $color=>$value) {
				$proc = round( (($value/$total)*100), 1);
				$cir[] = '
					<circle id="pie_'.$color.$value.'" style="stroke:#'.$color.';stroke-opacity:1;fill:none" cx="50%" cy="50%" r="40%" stroke-width="20%" stroke-dasharray="'.number_format($proc,1,'.','').' 100" stroke-dashoffset ="-'.number_format($offs,1,'.','').'" />
					';
				$offs += $proc;
				}
			$cir[] = '<text x="50%" y="50%" style="text-anchor:middle;text-align:center" fill="#fff">'.$total.'</text>';
			
			$draw = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
				<svg width="64" height="64" viewBox="0 0 64 64">
				   '.implode('',$cir).'
				   Sorry, your browser does not support inline SVG.
				</svg> 
				';
			
			file_put_contents($readyFileName, $draw);
			#echo 'mkFile & reloction:'.$reLocation.'</br>';
			header( "Location: ".$reLocation ) ;
			/*
			$fn = uniqid().'.svg';
			$len=strlen($draw);
			header("Content-type: image/svg+xml");
			header("Content-Length: $len");
			header("Content-Disposition: inline; filename=$fn");
			print $draw;
			*/
			}
		}
	}
?>

