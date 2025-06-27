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


$items = [];
$indicators = [];
$recId = $this->routeParam[0];
$imageFieldId = 'coverImage'.str_replace('.','_', $recId);

$isbnToCheck = $this->POST['pdata'];

$i = 0;
foreach ($isbnToCheck as $value=>$count) {
	$isbn = str_Replace('-','', $value);
	$fn = 'files/covers/isbn/medium/'.$isbn.'.jpg';
					
	if (file_exists($fn)) {
		$img_file = $this->HOST.$fn;
		$altText = $this->transEsc('Cover image');
		if ($i == 0) 
			$active = 'active';
			else 
			$active = '';
		$indicators[] = '<li data-target="#coversCarousel" data-slide-to="'.$i.'" class="'.$active.'"></li>';
		$items[] = '<div class="item '.$active.'">
			  <img src="'.$img_file.'" alt="'.$altText.' '.$isbn.'" title="'.$isbn.'" style="width:100%">
			</div>';
		$i++;
		#echo '<img src="'.$img_file.'" alt="'.$altText.'" style="width:100%" class="img img-responsive" id="'.$value.'">'.$isbn;
		$hasLocalCover = true;
		} else {
	
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

if (!empty($items)) {
	echo '<div id="coversCarousel" class="carousel slide" data-ride="carousel">';
	echo '<div class="carousel-inner">'.implode('', $items).'</div>';
	echo '
	<!-- Left and right controls -->
	  <a class="left carousel-control" href="#coversCarousel" data-slide="prev">
		<span class="glyphicon glyphicon-chevron-left"></span>
		<span class="sr-only">Previous</span>
	  </a>
	  <a class="right carousel-control" href="#coversCarousel" data-slide="next">
		<span class="glyphicon glyphicon-chevron-right"></span>
		<span class="sr-only">Next</span>
	  </a>
	  </div>';	
	echo '<ol class="carousel-indicators">'.implode('', $indicators).' </ol>';	
	}

?>

