<?php 

function checkBookCover($isbn) {
	$coversFolder = './files/covers/isbn/medium/';
	
	$baseUrl = 'https://covers.openlibrary.org/b/isbn/';
	$url = $baseUrl . $isbn . '-M.jpg';

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HEADER, false); // Pobierz całą odpowiedź bez nagłówków
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);

	$imageData = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	// Sprawdzanie kodu odpowiedzi HTTP
	if ($httpCode === 200) {
		// Analiza rozmiaru pobranego obrazu
		if (strlen($imageData) > 100) {
			file_put_contents($coversFolder.$isbn.'.jpg', $imageData);
			return $coversFolder.$isbn.'.jpg';
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

function checkObalkyKnih($isbn) {
	$coversFolder = './files/covers/isbn/medium/';
	
	$fileToCheck = 'https://www.obalkyknih.cz/view?isbn='.$isbn;
	$testFile = file_get_contents($fileToCheck);
	$t1 = explode('previewimage" href="', $testFile);
	if (is_array($t1) && (count($t1)>1)) {
		$t2 = explode('"', $t1[1]);
		$cover = $t2[0];
		$coverImage = file_get_contents($cover);
		$localFile = $coversFolder.$isbn.'.jpg';
		file_put_contents($localFile, $coverImage);
		return $localFile;
		}
	}	



$recId = $this->routeParam[0];
$imageFieldId = 'coverImage'.str_replace('.','_', $recId);

if (!empty( $this->POST['pdata'] )) {
	$isbnToCheck = $this->POST['pdata'];

	echo $this->helper->pre($isbnToCheck);

	foreach ($isbnToCheck as $value) {
		$doLookFor = true;
		$isbn = str_replace('-', '', $value);
		$result = false;
		
		$t = $this->psql->querySelect("SELECT * FROM no_cover WHERE type='isbn' AND numer='$isbn'");
		if (is_array($t)) {
			$rec = current($t);
			$update = 'UPDATE no_cover SET lastcheck = now();';
			$lastcheck = strtotime($rec['lastcheck']);
			if (time() - $lastcheck > 1209600) // 14 days
				$doLookFor = true;
				else 
				$doLookFor = false;
			} else {
			$update = "INSERT INTO no_cover (type, numer, lastcheck) VALUES ('isbn', '$isbn', now());";
			$doLookFor = true;
			}
		
		if ($doLookFor) {
			$result = checkBookCover($isbn);
			if ($result == false)
				$result = checkObalkyKnih($isbn);
			if (is_string($result)) {
				$htmlCoverPath = $this->HOST.str_replace('./files/covers', 'files/covers', $result);
				$this->addJS("$('#{$imageFieldId}').attr('src', '$htmlCoverPath');");
				$this->psql->query("DELETE FROM no_cover WHERE type='isbn' AND numer='$isbn'");
				} else {
				if (!empty($update))
					$this->psql->query($update);	
				}
			}
		
		}
	}
?>