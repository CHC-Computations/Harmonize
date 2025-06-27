<?php

$thisPlace = 'panel/'.implode('/',$this->routeParam);

$total = 0;
	
foreach ($this->configJson as $key=>$values) {
	if (!empty($this->GET['file']) && ($key == $this->GET['file']))
		$active = ' list-group-item-success';
		else 
		$active = '';
	$count = $this->helper->countEntries($this->configJson->$key);
	$total += $count;
	$printList[] = '<a class="list-group-item'.$active.'" href="'.$this->buildUrl($thisPlace, ['file'=>$key]).'">'.$key.'<span class="badge">'.$count.'</span></a>';
	}

echo '<div class="row">';
echo '<div class="col-sm-2">';
echo '<div class="list-group">';

echo implode('', $printList);
echo '</div>';
echo '<p>'.$this->transEsc('Sum of all editable parameters').': <b>'.$total.'</b></p>';
echo '</div>';
echo '<div class="col-sm-5" id="detailsPanel">';

if (empty($this->GET['file']) or empty($this->configJson->{$this->GET['file']}))
	echo $this->helper->alertIco('white', 'ph ph-arrow-left',  $this->transEsc('select a group of settings'));
	else {
	$file = $this->GET['file'];
	echo $this->helper->panelCollapse('panel'.$file, $file, drawJsonMenu($this->configJson->$file, $file), '', true, 'success');	

	}
echo '</div>';
echo '<div class="col-sm-5" id="helperPanel">';

echo '</div>';
echo '</div>';




function drawJsonMenu ($json, $prefix='') {
	$content = '<ul class="list-menu">';
	if (!empty($json) && (is_object($json) or is_array($json))) {
		foreach ($json as $key=>$values) {
			if (is_object($values) or is_array($values))
				$subcontent = drawJsonMenu($values, $prefix.'/'.$key);
				else {
				if (is_bool($values))
					$values = $values ? 'true':'false';
				$subcontent = ' = '.(string)$values;
				}
			$uid = uniqid();	
			$content .= '<li class="list-menu-item" id="'.$uid.'"><a onClick="page.ajax(\'helperPanel\',\'service/helperForConfigFiles/'.$prefix.'/'.$key.'?id='.$uid.'\')">'.$key.'</a>'.$subcontent.'</li>';
			} 
		}
	$content .= '</ul>';
	
	
	return $content;
	}

?>

