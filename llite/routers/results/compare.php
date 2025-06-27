<?php 
if (empty($this)) die;
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.helper.php');
require_once('functions/class.converter.php');
require_once('functions/class.maps.php');
require_once('functions/class.wikidata.php');
require_once('./functions/class.bookcart.php');


$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->addClass('convert', 	new converter()); 
$this->addClass('wikiData', new wikiData($this)); 
$this->addClass('bookcart', new bookcart);

$recIdTable = $this->routeParam;

echo $this->render('head.php');
echo $this->render('core/header.php');

// container-fluid
echo '
	<div class="container-fluid compare">
		<br/><br/>
		<div class="text-center">
			<button class="btn btn-danger" type="button"><i class="ph ph-trash"></i> Delete left record</button>
			<button class="btn btn-success" type="button"><i class="ph ph-thumbs-up"></i><i class="ph ph-thumbs-up"></i> Both should stay here</button>
			<button class="btn btn-primary" type="button"><i class="ph ph-intersect"></i> Merge records</button>
			<button class="btn btn-danger" type="button"><i class="ph ph-trash"></i> Delete right record</button>
			
		</div>
	
	<div class="row">
	<div class="col-sm-6">
	';

$this->setTitle($this->transEsc('compare').': '.implode(' vs ', $recIdTable));

foreach ($recIdTable as $rec_id) {	
	$solrRecord = $this->solr->getRecord('biblio', $rec_id);
	
	if (!empty($solrRecord->id)) {
		$marcJson = $this->convert->mrk2json($solrRecord->fullrecord);
		$this->addClass('record', new bibliographicRecord($solrRecord, $marcJson));
		echo '<div class="main">';
		echo $this->render('record/compare/core.php', ['record' => $this->record ]);
			# echo $this->helper->pre($this->record->elbRecord);
		echo '</div>';
		$boxId = $this->record->getIdStr();
		$Tmark[] = $this->record->drawMarc();
		$Trec[] = $this->record->marcJson;
		$Tjson[] = '<br/><pre id="jsonrenderer'.$boxId.'" style="background-color:transparent; border:0px;">'.print_r($this->record->elbRecord, 1).'</pre>' ;
		
		$this->addJS('
			var data = '.json_encode($this->record->elbRecord).'
			$("#jsonrenderer'.$boxId.'").jsonViewer(data,  {collapsed: true, rootCollapsable : false, withLinks: false, bigNumbers: true});
			');
		
		} else {
		echo '<div class="main">';
		echo $this->render('record/no-core.php', ['rec'=>$rec_id, 'Tmap'=>$Tmap]);
		echo '</div>';
		}
	echo '
		</div>
		<div class="col-sm-6">
		';
	}
echo '
	</div>
	</div>
	';


/*
echo '<hr/><h3>'.$this->transEsc('Marc view').'</h3>';
echo '<div class="row"><div class="col-sm-6">'.implode('</div><div class="col-sm-6">', $Tmark).'</div></div>';	
*/

echo '<hr/><h3>'.$this->transEsc('Marc view').'</h3>';
echo compareMarc($Trec);	

echo '<hr/><h3>'.$this->transEsc('ELB fields').'</h3>';
echo '<div class="row"><div class="col-sm-6">'.implode('</div><div class="col-sm-6">', $Tjson).'</div></div>';	
	
echo '	
	</div>
	';	
echo $this->render('core/footer.php');
	
	
	
	
function compareMarc($recTable) {
	foreach ($recTable as $lp=>$record) {
		foreach ($record as $field=>$subarr) {
			@$Tfields[$field]++;
			}
		}
	unset($Tfields['LEADER']);	
	ksort($Tfields);
	if (!empty($recTable)) {
		$result = '<table class="table table-striped table-hover">
				<thead>
					<tr>
					<td style="text-align:right"><b>LEADER</b></td>
					<td colspan=3>'.$recTable[0]->LEADER.'</td>
					<td colspan=3>'.$recTable[1]->LEADER.'</td>
					</tr>
				</thead>
				<tbody>
				';
		foreach ($Tfields as $field=>$fcount) {
			
			$count[0] = $count[1] = 0;
			if (!empty($recTable[0]->$field) && is_Array($recTable[0]->$field)) $count[0] = count($recTable[0]->$field);
			if (!empty($recTable[1]->$field) && is_Array($recTable[1]->$field)) $count[1] = count($recTable[1]->$field);
			
			$repeat = max($count)-1;
			
			for ($z = 0; $z <= $repeat; $z++) {
				$ind = $ind1 = '<td></td><td></td>';
				$value = $value1 = '<td></td>'; 
				
				if (!empty($recTable[0]->$field[$z])) {
					$row = $recTable[0]->$field[$z];
					$codes = array();
					$value = $ind = ''; 
					$row = (array)$row;
					if (!empty($row['ind1'])) {
						$ind = "<td>$row[ind1]</td>";
						if (!empty($row['ind2']))
							$ind .= "<td>$row[ind2]</td>";
							else 
							$ind .= "<td></td>";
						}
					if (!empty($row['code'])) {
						foreach ($row['code'] as $code=>$val) 
							if (is_array($val))
								$codes[]="<b>|$code</b> ".implode(" <b>|$code</b> ", $val);
								else
								$codes[]="<b>|$code</b> $val ";
						$value = "<td>".implode(' ', $codes)."</td>";
						if ($ind=='')
							$ind = "<td></td><td></td>";
						} 
					if (count($row)==1)
						$value = "<td colspan=3>$row[0]</td>";
					if ($value == '')
						$value = '<td></td>';
					}
					
				
				if (!empty($recTable[1]->$field[$z])) {
					$row = $recTable[1]->$field[$z];
					$codes = array();
					$value1 = $ind1 = ''; 
					$row = (array)$row;
					if (!empty($row['ind1'])) {
						$ind1 = "<td>$row[ind1]</td>";
						if (!empty($row['ind2']))
							$ind1 .= "<td>$row[ind2]</td>";
							else 
							$ind1 .= "<td></td>";
						}
					if (!empty($row['code'])) {
						foreach ($row['code'] as $code=>$val) 
							if (is_array($val))
								$codes[]="<b>|$code</b> ".implode(" <b>|$code</b> ", $val);
								else
								$codes[]="<b>|$code</b> $val ";
						$value1 = "<td>".implode(' ', $codes)."</td>";
						if ($ind1 == '')
							$ind1 = "<td></td><td></td>";
						} 
					if (count($row)==1)
						$value1 = "<td colspan=3>$row[0]</td>";
					if ($value1 == '')
						$value1 = '<td></td>';
					}
				
				$is_div = '<i class="ph ph-eye" title="The fields are different" data-toggle="tooltip"></i>';
				$rowClass = 'danger';
				if (($value == $value1) & ($ind == $ind1)) {
					$is_div = '<i class="ph ph-check" title="The fields are identical" data-toggle="tooltip"></i>';
					$rowClass = 'success';
					}
				
				$result .= '
					<tr class="'.$rowClass.'">	
						<td style="text-align:right"><b>'.$field.'</b></td>
						'.$ind.'
						'.$value.'
						'.$ind1.'
						'.$value1.'
						<td>'.$is_div.'</td>
					</tr>';	
				}
			
					
			}
		$result.="</tbody></table>";
		
		
		return $result;	
		} else {
		return "no record loaded";	
		}
	}
	