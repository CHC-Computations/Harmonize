<?php 
 # echo "<pre>".print_r($exportParams,1)."</pre>";
 
 $OC = "results.ExportStart(\"{$exportParams->fileFormat}\", \"{$this->facetsCode}\", ".json_encode($exportParams).");";

?>
	<div class="row">
		<div class="col-sm-3 text-center" style="font-size:3em">
			<i class="ph-cloud-arrow-down-bold"></i>
		</div>
		<div class="col-sm-7" id="export_box">
			
			<span><?= $this->transEsc('Export format')?>: <b><?= $exportParams->formatName ?></b> (<?= $exportParams->fileFormat ?>).</span><br/><br/>
			<?= $this->transEsc('Export file will contain records')?>:<br/>
			<ul style="overflow:hidden;">
			<?php 
			foreach ($exportParams->exportTable as $key=>$group)
				echo '<li>'.$this->transEsc($group->displayName).': <b>'.$this->helper->numberFormat($group->totalResults).'</b>.<div id="exportField_'.$key.'"></div></li>';
			
			?>
			</ul>
		</div>
		<div class="col-sm-2 text-center" id="exportBtn">
			
			<?php if ($exportParams->exportTable->biblio->totalResults > $this->configJson->biblio->exports->maxRecords)
				echo "You have exceeded the one-time export limit.</p><p>(max {$this->helper->badgeFormat($this->configJson->biblio->exports->maxRecords)} records)</p>";
				else 
				echo "<button class=\"btn btn-primary\" OnClick='".$OC."'>Start</button>";
			?>
			 
		</div>
		
	</div>
	
	<div id="exportControlField">
	</div>