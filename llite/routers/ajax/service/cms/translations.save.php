<?php

#echo $this->helper->pre($this->POST);
$langList = ['en', 'pl', 'cs'];
$languagesFilesPath = './languages/';
	


$originalString = base64_decode($this->POST['pdata']['string']);
$rowId = md5($originalString);
			
$en = $this->POST['pdata']['en'];
$pl = $this->POST['pdata']['pl'];
$cs = $this->POST['pdata']['cs'];

$t = $this->psql->querySelect("INSERT INTO dic_translations (original, en, pl, cs) 
		VALUES ({$this->psql->string($originalString)}, {$this->psql->string($en)}, {$this->psql->string($pl)}, {$this->psql->string($cs)})
		ON CONFLICT (original)
		DO UPDATE SET en = {$this->psql->string($en)}, pl = {$this->psql->string($pl)}, cs = {$this->psql->string($cs)}
		RETURNING original");
		


$t = $this->psql->querySelect("SELECT * FROM dic_translations ORDER BY original;");
if (is_array($t)) {
	foreach ($t as $row) {
		foreach ($langList as $langCode)
			if (!empty($row[$langCode])) {
				$row['original'] = str_replace('"', '&quot;', $row['original']);
				$row[$langCode] = str_replace('"', '&quot;', $row[$langCode]);
				$toFile[$langCode][] = '"'.$row['original'].'" => "'.$row[$langCode].'"';
			}
		}
	foreach ($langList as $langCode) {
		$fileName = $languagesFilesPath.$langCode.'.php';
		file_put_contents($fileName, '<?php $this->translations = ['.implode(",\n", $toFile[$langCode]).']; ?>');
		}	
	}

echo $this->helper->alert('success', "changes have been saved");
echo '
	<script>
		$("#'.$rowId.'_pl").html("'.$pl.'");
		$("#'.$rowId.'_en").html("'.$en.'");
		$("#'.$rowId.'_cs").html("'.$cs.'");
		$("#myModal").modal("hide")
	</script>
	';


?>
