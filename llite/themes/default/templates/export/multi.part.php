<?php 

$fullFileName = "./files/exports/$filename.mrc";

if (($p==1)and(file_exists($fullFileName))) 
	echo $this->render('export/multi.finish.php', ['facets' => $facets, 'filename'=>$this->routeParam[2] ] );	
	else {
?>
	<?= $this->transEsc('Preparing file')?>.
	<?= $this->transEsc('Please wait')?>.
	<?= $this->transEsc("Don't close this window")?>.
	<br/>
	<br/>
	<?= $this->helper->percent($step,$this->solr->totalResults()) ?>
	
	<b><?=number_format($step, 0, '', '.')?></b> <?= $this->transEsc('of')?> <?= number_format($this->solr->totalResults(),0,'','.') ?>. 
	
	<?php 
	
	foreach ($this->solr->resultsList() as $result) {
		#echo $result->fullrecord."<br/>";
		file_put_contents("./files/exports/$filename.mrc", $result->fullrecord, FILE_APPEND);
		file_put_contents("./files/exports/$filename.txt", "<id>{$result->id}</id>", FILE_APPEND);
		}
	file_put_contents("./files/exports/$filename.txt", "\n", FILE_APPEND);	
	?>
	
	

<script>
$(document).ready(function(){
	results.ExportPart('<?= $p+1 ?>', '<?=$this->facetsCode?>', '<?=$filename?>');
	});
</script>

<?php 
	}
?>