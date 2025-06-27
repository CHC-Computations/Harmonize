<?php 
 # echo "<pre>".print_r($exportParams,1)."</pre>";
 
$OC = "results.ExportStart(\"{$exportParams->fileFormat}\", \"{$this->facetsCode}\", ".json_encode($exportParams).");";


$filesToPrepare[] = (object) [
		'displayName' => $this->transEsc('Selected data from bibliografic records'),
		'totalResults' => $this->solr->totalResults()
		];
		
foreach ($this->solr->facetsList()['record_format'] as $dataFormat=>$sourceCount) {
	$filesToPrepare[] = (object) [
		'displayName' => $this->transEsc('Orginal bibliographic data provided to the ELB in format').' '.$dataFormat,
		'totalResults' => $sourceCount
		];
	}		

foreach ($this->solr->facetsList()['source_db_str'] as $sourceDB=>$sourceCount) {
	$usedLicences[$licenceTable[$sourceDB]->code]['link'] = $licenceTable[$sourceDB]->link;
	$usedLicences[$licenceTable[$sourceDB]->code]['content'][$sourceDB]['count'] = $sourceCount;
	$usedLicences[$licenceTable[$sourceDB]->code]['content'][$sourceDB]['desc'] = $licenceTable[$sourceDB]->description;
	}


$sourceDB = 'Wikidata';
if (!empty($this->configJson->biblio->exports->formats->{$exportParams->fileFormat}->getCount)) {
	$wikiSum = 0;
	$wikiInclude = [];
	foreach ($this->configJson->biblio->exports->formats->{$exportParams->fileFormat}->getCount as $indexName=>$displayName) {
		$responseIndexName = $indexName.'_x';
		if ($this->solr->fullResponse->facets->$responseIndexName>0) {
			$wikiSum += $this->solr->fullResponse->facets->$responseIndexName;
			$wikiInclude[] = $displayName.' <span class="badge">'.$this->helper->badgeFormat($this->solr->fullResponse->facets->$responseIndexName).' rec.</span>';
			$filesToPrepare[] = (object) [
				'displayName' => $displayName.' '.$this->transEsc('records'),
				'totalResults' =>$this->solr->fullResponse->facets->$responseIndexName
				];
			}
		}
	$usedLicences[$licenceTable[$sourceDB]->code]['link'] = $licenceTable[$sourceDB]->link;
	$usedLicences[$licenceTable[$sourceDB]->code]['content'][$sourceDB]['count'] = $wikiSum;
	$usedLicences[$licenceTable[$sourceDB]->code]['content'][$sourceDB]['desc'] = $this->transEsc('Wikidata content including').':<br/>'.implode(',<br/>',$wikiInclude);
	
	}
		


if (!empty($usedLicences)) {
	$usedLicencesStr = '<p>'.$this->transEsc('The export will contain data subject to the following licences. The export will be accompanied by a file containing the license designation and a list of record IDs subject to the corresponding license.').'</p>'; 
	foreach ($usedLicences as $licenceCode => $values) {
		$usedLicencesStr .= '<strong>'.$licenceCode.'</strong> <a href="'.$values['link'].'">'.$this->transEsc('See what this means').'</a><ol>';
		foreach ($values['content'] as $sourceDB => $sourceVal) {
			if (!empty($sourceVal['desc'])) $sourceVal['desc'] = '<br/>'.$sourceVal['desc'];
			$usedLicencesStr .= '<li>'.$sourceDB.' <span class="badge">'.$this->helper->badgeFormat($sourceVal['count']).'  rec.</span> '.$sourceVal['desc'].'</li>';
			}
		$usedLicencesStr .= '</ol>';
		}
	# echo $this->helper->pre($usedLicences);
	
	echo $this->helper->alert('info', $usedLicencesStr);
	}
	
	
	

	
?>
	<div class="row">
		<div class="col-sm-2 text-center" style="font-size:3em">
			<i class="ph-cloud-arrow-down-bold"></i>
		</div>
		<div class="col-sm-8" id="export_box">
			<span><?= $this->transEsc('Export format')?>: <b><?= $exportParams->title ?></b> (<?= $exportParams->fileFormat ?>).</span> <?= $this->transEsc('ZIP compressed') ?>.<br/><br/>
			<p><?= $this->transEsc($exportParams->description) ?></p>
			<?= $this->transEsc('Export will contain files')?>:<br/>
			<?php 
			foreach ($filesToPrepare as $key=>$fileInfo)
				echo '<label><input type="checkbox" checked="checked"> '.$this->transEsc($fileInfo->displayName).': <b>'.$this->helper->numberFormat($fileInfo->totalResults).'</b>.</label>
					<br/><div id="exportField_'.$key.'"></div>';
			
			?>
			
			
			
		</div>
		<div class="col-sm-2 text-center" id="exportBtn" style="vertical-align:bottom">
			
			 
		</div>
		
	</div>
	
	<div id="exportControlField">
		<div class="text-center">
		<?php if ($this->solr->totalResults() > $this->configJson->biblio->exports->maxRecords)
				echo "You have exceeded the one-time export limit.</p><p>(max {$this->helper->badgeFormat($this->configJson->biblio->exports->maxRecords)} records)</p>";
				else 
				echo "<button class=\"btn btn-primary\" OnClick='".$OC."'>Start</button>";
			?>
		</div>	
	</div>

