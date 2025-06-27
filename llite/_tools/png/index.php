<?php
#ini_set('display_errors', 'On');
#error_reporting(E_ALL);
$logFile = 'log/log.txt';

function hexColorAllocate($image,$hex){
    $hex = ltrim($hex,'#');
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));
    return imagecolorallocate($image, $r, $g, $b); 
	}


$image = imagecreatefrompng('empty.png');
$centerX = 64;
$centerY = 64;

$gray = imagecolorallocate($image, 155, 155, 155);
$white = imagecolorallocate($image, 255, 255, 255);

if (file_exists($logFile)) unlink($logFile);

$params = $_SERVER['REQUEST_URI'] ?? '';
if (!empty($params)) {
	$tmp = explode('/png/', $params);
	$fileName = $tmp[1];
	$pieParams = explode('-', $tmp[1]);
	$graphMode = end($pieParams);
	unset($pieParams[count($pieParams)-1]);
		
	foreach ($pieParams as $colorNvalue) {
		$tmp = explode('_',$colorNvalue);
		$pie[$tmp[0]] = $tmp[1];
		}
	if (!empty($pie)) {
		$total = array_sum($pie);	
		$offs = 0;
		$i = 0;
		foreach ($pie as $color=>$value) {
			$i++;
			$step = round((360/$total)*$value, 0);
			if ($offs == 360) $offs = 359;
			if (($i == count($pie)) && ($offs+$step!==360))	$step = 360-$offs;
			
			imagefilledarc($image, $centerX, $centerY, 128, 128, $offs, $offs+$step , hexColorAllocate($image, $color), IMG_ARC_PIE);
			file_put_contents($logFile, "$offs, $step, $total;\n", FILE_APPEND);
			$offs += $step;
			}
		}
	}

$gray 	= imagecolorallocate($image, 155, 155, 155);
$white 	= imagecolorallocate($image, 255, 255, 255);

imagefilledarc($image, $centerX, $centerY, 48, 48, 0, 360 , $gray, IMG_ARC_PIE);
/*
$px     = round((imagesx($image) - 7.5 * strlen($total)) / 2);
$py     = round((imagesy($image) - 7.5 * strlen($total)) / 2);
imagestring($image, 5, $px, 59, $total, $white);
*/

header("Content-type: image/png");
imagepng($image);
imagedestroy($image)



/*

 imagefilledarc(
    GdImage $image,
    int $center_x,
    int $center_y,
    int $width,
    int $height,
    int $start_angle,
    int $end_angle,
    int $color,
    int $style
): bool
*/

#https://stackoverflow.com/questions/13872045/how-can-i-display-a-png-image-pie-chart-generated-by-gd-using-php-in-my-html-p




?>