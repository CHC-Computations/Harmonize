<?php
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.buffer.php');

$this->addClass('buffer', 	new buffer()); 
$this->addClass('helper', 	new helper()); 

$Id = $this->routeParam[0];

$xmlf = $this->buffer->loadFromGeonames($Id); // 
$xml = json_decode($xmlf);

$rec = $xml;

echo $this->helper->panelCollapse(
		uniqid(), 
		"record from geonames", 
		"<pre style='background-color:#fff; border:0px;' id='json-viewer'>".print_r($rec,1).'</pre>', 
		'',
		false
		);

echo "<script>
	var input = ".json_encode($rec).";
	$('#json-viewer').jsonViewer(input, {collapsed: true, rootCollapsable: false});
	</script>";
			


			
$mapping = array(
	'LDR' 	=> [
				'field' 	=> 'LDR',
				'value' => '-----nz--a22-----n--4500', 				
				'label' => 'LEADER',
				'repeated' => false
				],
	'001' 	=> [
				'field' 	=> '001',
				'source' => 'geonameId',
				'label' => 'geonameId',
				'repeated' => false
				],
	'008' 	=> [
				'field' 	=> '008',
				'value' => '211102----------------------------------', 				
				'label' => '008',
				'repeated' => false
				],
	'034' 	=> [
				'field' 	=> '034',
				'subfields' => [
						'd' => [
							'source' => 'bbox->east',
							'label' => 'W'
							],
						'e' => [
							'source' => 'bbox->west',
							'label' => 'E'
							],
						'f' => [
							'source' => 'bbox->north',
							'label' => 'N'
							],
						'g' => [
							'source' => 'bbox->south',
							'label' => 'S'
							],
						],
				'label' => 'bbox',
				'repeated' => false
				],
	'151' 	=> [ 
				'field' 	=> '151',
				'subfields' => [
						'i' => [
							'source' => 'fcodeName', 
							'label' => 'Type of place'
							],
						'a' => [
							'source' => 'asciiName',
							'label' => 'Name of place'
							],
						'1' => [
							'source' => 'geonameId',
							'label' => 'geonames_id'
							],
						],
				'label' => 'official name of the place',
				'ind1' => '1',
				'ind2' => '\\',
				'repeated' => false
				],
	);			

$count = count($rec->alternateNames);
for ($i = 0; $i<$count; $i++) {
	// 451  1\$aAlternative name of place$2Code of the language<br/>
	$mapping["($i) 455"] = [
				'field' 	=> '455',
				'subfields' => [
						'a' => [
							'source' => 'alternateNames->'.$i.'->name',
							'label' => 'Alternative name of place'
							],
						'2' => [
							'source' => 'alternateNames->'.$i.'->lang',
							'label' => 'Code of the language'
							],
						],
				'label' => 'Alternative name of place',
				'ind1' => '1',
				'ind2' => '\\',
				'repeated' => false
		];
	}

$mapping['500'] = [ 
				'field' 	=> '500',
				'value' => '', // 1\$iRelation$aName of related person,$dDates,$1viaf_id',
				'label' => 'Name of related person',
				'ind1' => '1',
				'ind2' => '\\',
				'repeated' => false
			];
$mapping['(0) 551'] = [  //1\$wh$aName of narrower related place$1geonames_id<br/>
				'field' 	=> '551',
				'subfields' => [
						'a' => [
							'value' => '',
							'label' => 'Name of narrower related place'
							],
						],
				'label' => 'Name of narrower related place',
				'ind1' => '1',
				'ind2' => '\\',
				'repeated' => false
			];
$mapping['(1) 551'] = [ //1\$wg$aName of broader related place$1geonames_id<br/>
				'field' 	=> '551',
				'subfields' => [
						'a' => [
							'value' => '',
							'label' => 'Name of broader related place'
							],
						],
				'label' => 'Name of broader related place',
				'ind1' => '1',
				'ind2' => '\\',
				'repeated' => false
			];
$mapping['670'] = [ 
				'field' 	=> '670',
				'subfields' => [
						'a' => [
							'value' => 'http://geonames.org/',
							'label' => 'Source of information'
							],
						],
				'label' => 'Source of information',
				'ind1' => '1',
				'ind2' => '\\',
				'repeated' => false
			];

			
echo '<table class="table table-hover">';			
foreach ($mapping as $k=>$v) {
	
	if (!empty ($v['source']))
		$value = $this->buffer->getObjectValue($v['source'], $rec);
		elseif (!empty($v['value']))
			$value = $v['value'];
			elseif (!empty($v['subfields'])) {
				$value = 'retrieving subfields';
				$res = [];
				foreach ($v['subfields'] as $sk=>$arr) {
					if (!empty($arr['source']))
						$res[$sk] = " <b>|$sk</b> <span title='$arr[label]'>".$this->buffer->getObjectValue($arr['source'], $rec).'</span>';
					if (!empty($arr['value']))
						$res[$sk] = " <b>|$sk</b> <span title='$arr[label]'>".$arr['value'].'</span>';
					if ($v['repeated']) {
						$res[$sk].=' <small class=badge>shoud be repeated!</small>';
						}
					}
				$value = implode(' ', $res);	
				} else 
				$value = 'error!';
	$ind1=$ind2='<td></td>';
	if (!empty($v['ind1']))
		$ind1= "<td>$v[ind1]</td>";
	if (!empty($v['ind2']))
		$ind2= "<td>$v[ind2]</td>";
	
	
	echo '
		<tr>
			<td class="text-right"><b>'.$v['field'].'</b></td>
			'.$ind1.$ind2.'
			<td>'.$value.'</td>
			<td class="text-right small">'.$v['label'].'</td>
		</tr>
		';
	}			
echo '</table>';



		
?>


678  0\$aDescription<br/>
856  42$uMap URL$yMap<br/>
856  42$uFlag URL$yFlag<br/>
856  42$uCoat of armsURL$yCoat of arms<br/>
