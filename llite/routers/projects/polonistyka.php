<?php
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.converter.php');


$this->addClass('buffer', 	new buffer()); 
$this->addClass('helper', 	new helper()); 


$path = './files/polonistyka/';
$glob = glob($path.'*.*');
$i = 1;
foreach ($glob as $fileName) {
	$onlyName = str_replace($path, '', $fileName);
	$htmlPath = $this->HOST.'files/polonistyka/'.$onlyName;
	$fileSize = filesize($fileName);
	$toShow[] = '<a href="'.$htmlPath.'" class="list-group-item" target="_blank">'.$i.'. '.$onlyName.' <small class="badge">'.$this->helper->numberFormat($fileSize).' B</small></a>';
	$i++;
	}

$toShowStr = '<div class="list-group">';
$toShowStr .= implode('', $toShow);
$toShowStr .= '</div>';


$title = 'Polonistyka wobec wyzwań współczesnego świata';
$this->setTitle($title);
echo $this->render('head.php');
echo $this->render('core/header.php');
echo '<div class="main">';
echo '<div class="container">';
echo '<h1>'.$title.'</h1>';
echo '<p>Pliki do pobrania:</p>';
echo $toShowStr;

echo '<p><a href="https://biuletynpolonistyczny.pl/pl/projects/polonistyka-wobec-wyzwan-wspolczesnego-swiata,1851/details">Więcej o projekcie na stronie &quot;Biuletynu Polonistycznego&quot;.</a></p>';
echo '<p><strong>Projekt dofinansowany ze środków budżetu państwa w ramach programu Ministra Edukacji i Nauki pod nazwą „Nauka dla Społeczeństwa II”<br/>(numer projektu: NdS-II/SP/0264/2024/01).</strong></p>';
echo '</div>';
echo '</div>';
echo $this->render('core/footer.php');