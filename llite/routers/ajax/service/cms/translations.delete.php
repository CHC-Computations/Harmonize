<?php

#echo $this->helper->pre($this->POST);
$langList = ['en', 'pl', 'cs'];
$languagesFilesPath = './languages/';
	


$originalString = base64_decode($this->POST['pdata']);
$rowId = md5($originalString);
			
$t = $this->psql->querySelect("DELETE FROM dic_translations WHERE original = {$this->psql->string($originalString)};");
$t = $this->psql->querySelect("UPDATE translate SET deleted = true WHERE string = {$this->psql->string($originalString)};");
		


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

echo '<td colspan = 6 class="danger text-center">The line has been deleted.</td>';
echo '
	<script>
		setTimeout(function(){ $("#'.$rowId.'").html(" "); }, 500);
		$("#myModal").modal("hide");
	</script>
	';


?>
