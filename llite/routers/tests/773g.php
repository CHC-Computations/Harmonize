<?php 
function parseEntries($entry) {
    $parsedData = [];
    
	$pattern = '/(?:Roč\.|Jg\.)\s*(\d+),\s*(\d{4}(?:\/\d{4})?),\s*č\.\s*(\d+).*?s\.\s*([\d\-]+)/u';
	$pattern = '/(?:Roč\.\s*(\d+)?(?:,\s*)?)?(\d{4}(?:\/\d{4})?)(?:\.\s*)?,?\s*č\.\s*(\d+).*?s\.\s*([\d\-]+)/ux';
	if (preg_match($pattern, $entry, $matches)) {
		$parsedData = [
			'rocznik' => (int)$matches[1],
			'rok_wydania' => $matches[2],
			'wydanie' => (int)$matches[3],
			'strona' => $matches[4]
			];
		}
    $parsedData['rocznik'] = $parsedData['rocznik'] ?? '';
	$parsedData['rok_wydania'] = $parsedData['rok_wydania'] ?? '';
	$parsedData['wydanie'] = $parsedData['wydanie'] ?? '';
	$parsedData['strona'] = $parsedData['strona'] ?? '';

    return (object)$parsedData;
}




$this->addClass('buffer', 	new buffer()); 
$this->addClass('helper', 	new helper()); 


$this->setTitle('773g test');

	
$file = './files/mrk/773 g.csv';
$fp = @fopen($file, "r");
		

echo $this->render('head.php');
echo $this->render('core/header.php');
echo '<table class="table table-hover">';
$i=0;
$err = 0;
if ($fp) {
	while (($buffer = fgets($fp, 8192)) !== false) {
		$line = str_getcsv($buffer);
		if (!empty($line[1]) && strlen($line[1])>7) {
			$testLine = $line[1];
			$res = parseEntries($testLine);
			if (empty($res->strona)) {
				file_put_contents('./files/mrk/773g_exeptions.csv', $line[0].',"'.$line[1]."\"\n", FILE_APPEND);
				$err++;
				}
			echo '
				<tr>
					<td>'.$line[0].'</td>
					<td>'.$testLine.'</td>
					<td>'.$res->rocznik.'</td>
					<td>'.$res->rok_wydania.'</td>
					<td>'.$res->wydanie.'</td>
					<td>'.$res->strona.'</td>
				</tr>
				';
			$i++;
			if ($i == 5000)	break;
			}
		}
	}	
echo '</table>';
echo $err.'/'.$i.'<br>';
		
echo $this->render('helpers/report.error.php'); 
echo $this->render('core/footer.php');		
?>