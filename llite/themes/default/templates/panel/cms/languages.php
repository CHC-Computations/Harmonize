<?php

require_once ('./functions/class.lists.php');

$this->addClass('lists', new lists());
$this->addJS("$('#'+page.resultsField).css('opacity','1');");


if ( 
	($this->user->isLoggedIn() && $this->user->hasPower('admin')) 
	or (!empty($this->GET['pass']) && ($this->GET['pass']=='3998eb1f34e8a2019029ed2c905cdd9d')) // Vojtek
	or (!empty($this->GET['pass']) && ($this->GET['pass']=='zQDn3uFAWpgKiZA5jLbbP7dk06jS1MpNA3')) // Paulina
	or (!empty($this->GET['pass']) && ($this->GET['pass']=='cZ23xRfEGHKGKZYrXQ4I9VKqbJJezo7i4t')) // Ania 
	) {

	$t = $this->psql->querySelect("SELECT * FROM translate WHERE deleted = false;");
	if (is_Array($t))
		foreach ($t as $row) {
			@$collectedTranslations[$row['string']]++;
			}

	$lp = 1;
	$languagesFilesPath = './languages/';
	$defLang = $this->defaultLanguage;
	$langAvaible = $this->lang['available'];
	#echo $this->helper->pre($langAvaible);
	
	
	foreach ($langAvaible as $languageCode=>$languageName) {
		if (file_exists($translationFile = $languagesFilesPath.$languageCode.'/'.$languageCode.'.ini')) {
			$translationTable[$languageCode] = parse_ini_file($translationFile);
			} else {
			echo $this->helper->alert('danger', 'Error reading '.$languageCode.'.ini file ('.$languageName.').');
			}
		}

	if (is_array($translationTable))
		foreach ($translationTable as $langCode=>$translations) 
			foreach ($translations as $orgin=>$translation )  
				$allTranslations[$orgin][$langCode] = $translation;
				
	

	

	$tableHeaders = '
			<thead>
			<tr>
				<td>No.</td>
				<td>Original</td>
				<td>EN</td>
				<td>CS</td>
				<td>PL</td>
				<td></td>
			</tr>
			</thead>';
		

	
	$tableContent = '<tbody>';
	
	$allTranslations = $this->psql->querySelect("SELECT * FROM dic_translations ORDER BY original;");
	if (is_array($allTranslations));
		foreach ($allTranslations as $row) {
			$base64 = base64_encode($row['original']);
			$rowId = md5($row['original']);
			$rowOnClick = "page.postInModal('{$this->transEsc('Edit row')}', 'service/cms/translations.edit/', '".$base64."');";
			if (!empty($collectedTranslations[$row['original']])) {
				$action = '<i class="ph ph-check" title="translation is used" onClick = "'.$rowOnClick.'"></i>';
				unset($collectedTranslations[$row['original']]);
				} else 
				$action = '<i class="ph ph-question" title="may be deleted" onClick = "'.$rowOnClick.'"></i>';
			$tableContent .= '
					<tr id="'.$rowId.'">
						<td class="text-right">'.$lp++.'.</td>
						<td onClick = "'.$rowOnClick.'">'.$row['original'].'</td>
						<td onClick = "'.$rowOnClick.'" id="'.$rowId.'_en">'.$row['en'].'</td>
						<td onClick = "'.$rowOnClick.'" id="'.$rowId.'_cs">'.$row['cs'].'</td>
						<td onClick = "'.$rowOnClick.'" id="'.$rowId.'_pl">'.$row['pl'].'</td>
						<td>'.$action.'</td>
					</tr>
					';
			}
			
	if (!empty($collectedTranslations))
		foreach ($collectedTranslations as $originalString=>$count) {
			$base64 = base64_encode($originalString);
			$rowId = md5($originalString);
			$rowOnClick = "page.postInModal('{$this->transEsc('Edit row')}', 'service/cms/translations.edit/', '".$base64."');";
			$delOnClick = "page.post('$rowId', 'service/cms/translations.delete/', '".$base64."');";
			
			$tableContent .= '
					<tr id="'.$rowId.'" class="warning">
						<td class="text-right">'.$lp++.'.</td>
						<td onClick = "'.$rowOnClick.'" >'.$originalString.'</td>
						<td onClick = "'.$rowOnClick.'" id="'.$rowId.'_en"> </td>
						<td onClick = "'.$rowOnClick.'" id="'.$rowId.'_cs"> </td>
						<td onClick = "'.$rowOnClick.'" id="'.$rowId.'_pl"> </td>
						<td>
							<i class="ph ph-check" onClick = "'.$rowOnClick.'" title="translation is used - automatically collected"></i>
							<i class="ph ph-trash" onClick = "'.$delOnClick.'" title="delete row"></i>
						</td>
					</tr>
					';
			}
			
	$tableContent .= '</tbody>';		
			
	echo '	
		<div class="table-containter">
			<table class="table table-hover table-bordered table-lists table-inContainter">
			'.$tableHeaders.'
			'.$tableContent.'
			</table>
		</div>	
		';
	}



 
?>
<h3>Legenda:</h3>
<p>
<i class="ph ph-question" title="may be deleted"></i> - prawdopodobnie ta fraza pochodzi ze starego modułu i nie jest już używana. Nie ma potrzeby uzupełniać tłumaczenia. <br/>
<i class="ph ph-check" title="translation is used"></i> - istnieje automatyczny zapis potwierdzający, że to tłumaczenie jest w użyciu. Należy uzupełnić tłumaczenie.<br/>
</p>
<div class="text-warning">
<i class="ph ph-check" title="translation is used"></i> wiersze w tabeli z żółtym tłem. - Istnieje automatyczny zapis potwierdzający, że to tłumaczenie jest w użyciu ale nie ma pewności czy powinno. <br/>
W kilku miejscach programu translator próbuje tłumaczyć fragmenty danych. Jeśli nieprzetłumaczony fragment zawiera: <i>nazwisko, nazwę miejscowości, nazwę języka</i> nie ma sensu uzupełniać tłumaczeń. 
</div>
<p>
Wszystkie zastosowane tłumaczenia są natychmiast widoczne na stronie www. Wystarczy odświeżyć oglądaną stronę. np. na klawiaturze <code>ctrl+R</code></p>
<br/><br/>
