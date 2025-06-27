<?php


$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this)); 
$this->addClass('buffer', 	new buffer()); 


 
$results = $this->solr->getFacets('biblio', ['source_db_str'], [])['source_db_str'];
$biblioJson = [];

$licences = $this->helper->getLicence();


$sourceToCode = [
	'pl' => 'Polska Bibliografia Literacka',
	'cz' => 'Česká Literární Bibliografie',
	'es' => 'Biblioteca Nacional de España',
	'fi' => 'Kansalliskirjasto',
	];

$filePath = './import/data/';
$orderTable = [];
$sourceTable = [];
$this->defaultLanguage = 'en';

$list = glob ($filePath.'*.mrk');

foreach ($list as $file) {
	$fileName = str_replace($filePath, '', $file);
	$tmp = explode('_', $fileName);
	$sourceCode = current($tmp);
	$sourceTable[$sourceCode][] = ['fileName' => $fileName, 'fileWithPath' => substr($file, 1)];
	
	@$orderTable[$sourceCode] += fileSize($file);
	#echo $sourceCode.' '.$fileName.'</br>';
	}	
arsort($orderTable);
foreach ($orderTable as $code=>$value) {
	$templatePath = '/cms/sources/'.$this->userLang.'-'.$code.'.php';
	$templateDef = '/cms/sources/'.$this->defaultLanguage.'-'.$code.'.php';
	
	echo '<div class="row" id="s_'.$code.'"><div class="col-sm-8">';
	echo '<div style="line-height:150%">'; 
	if ($this->templatesExists($templatePath))
		echo $this->render($templatePath);	
		else if ($this->templatesExists($templateDef))
		echo $this->render($templateDef);	
		else echo '<h3><small>'.$this->transEsc('No info page about').' </small>'.$code.'<small> source.</small></h3>';
		
	if (!empty($sourceToCode[$code])) {
		if (!empty($results[$sourceToCode[$code]])) {
			echo '<p>'.$this->transEsc('View records at ELB').' <a href="'.$this->buildUrl('results', ['core'=>'biblio', 'facetsCode'=>$this->buffer->createFacetsCode(['source_db_str:"'.$sourceToCode[$code].'"'])]).'">'.$this->transEsc($sourceToCode[$code]).' <span class="badge">'.$this->helper->numberFormat( $results[$sourceToCode[$code]] ).'</span></a></p>';
			$biblioJson[$code] = (object)[
				'name' => $sourceToCode[$code],
				'link' => $this->buildUrl('results', ['core'=>'biblio', 'facetsCode'=>$this->buffer->createFacetsCode(['source_db_str:"'.$sourceToCode[$code].'"'])]),
				'count' => $results[$sourceToCode[$code]]
				];
			}

		$licence = $licences[$sourceToCode[$code]] ?? new stdClass;
		if (!empty($licence))
			echo $this->helper->panelSimple($this->transEsc('Licence').': <strong><a href="'.$licence->link.'">'.$licence->code.'</a></strong> '.$licence->description, 'header');
		}
	
		
	echo '</div>';
	echo '</div><div class="col-sm-4" style="padding-top:40px;">';	
	foreach ($sourceTable[$code] as $source)
		echo '<a href="'.$this->HOST.substr($source['fileWithPath'], 1).'">'.$source['fileName'].'</a><br/>';
	echo '</div></div>';
	}
	
#echo $this->helper->pre($results);

echo '
	<hr/>
	
	<div class="row">
	<div class="col-sm-8">
		<div id="map" style="width:100%; height:600px;"></div>
	</div>
	<div class="col-sm-4">
		'.$this->transEsc('Legend').':<br/>
	</div>
	</div>
		';

file_put_contents('./files/maps/biblio.json', json_encode($biblioJson));

#echo '<p class="text-right" style="margin-top:5em; margin-bottom:2em;">'.$this->transEsc('The order in which the sources are presented depends on the volume of resources made available').'.<br/> '.$this->transEsc('All source files presented here are available under licence the').' <a href="https://creativecommons.org/publicdomain/zero/1.0/">'.$this->transEsc('Creative Commons CC0 License').'</a>.</p>'; 
#echo $this->helper->pre($sourceTable);
#echo $this->helper->pre($licences);
#echo $this->defaultLanguage. ' '. $this->userLang;
?>

<script>

// Funkcja do wczytywania danych JSON
async function fetchJSON(url) {
    const response = await fetch(url);
    if (!response.ok) {
        throw new Error(`Failed to fetch ${url}: ${response.statusText}`);
    }
    return response.json();
}

// Funkcja do określania intensywności koloru
function getColor(count) {
    return count > 2000000 ? '#32174d' : // Najciemniejszy odcień
           count > 1000000 ? '#4b2c70' : // Ciemny
           count > 500000  ? '#653d91' : // Bazowy kolor
           count > 100000  ? '#7e5ba7' : // Jaśniejszy
           count > 50000   ? '#9873bc' : // Jeszcze jaśniejszy
           count > 20000   ? '#b18fd2' : // Delikatny
           count > 10000   ? '#cab5e2' : // Bardzo jasny
                             '#e4d6f2';  // Najjaśniejszy
}

// Funkcja stylizująca kraje
function style(feature, counts, countryCode) {
    const count = counts[countryCode]?.count || 0; // Domyślnie 0, jeśli brak danych
    return {
        fillColor: getColor(count),
        weight: 1,
        opacity: 1,
        color: '#653d91',
        fillOpacity: 0.7
    };
}

// Główna funkcja inicjalizująca mapę
async function initMap() {
    // Wczytaj dane biblio.json
    const biblioData = await fetchJSON('https://testlibri.ucl.cas.cz/files/maps/biblio.json');

    // Utwórz mapę
    var startPosition = [53.3, 9.5];
	var map = L.map('map').setView(startPosition, 3);

    // Dodaj warstwę bazową
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Wczytaj dane GeoJSON dla każdego kraju
    for (const countryCode in biblioData) {
        const geoJsonUrl = `https://testlibri.ucl.cas.cz/files/maps/${countryCode}.json`;
        try {
            const geoJsonData = await fetchJSON(geoJsonUrl);
            L.geoJson(geoJsonData, {
                style: (feature) => style(feature, biblioData, countryCode),
                onEachFeature: (feature, layer) => {
                    const countryInfo = biblioData[countryCode];
                    if (countryInfo) {
                        layer.bindPopup(
                            `<div class="mapPlaceBox"><div class="box-head"><h4>${countryInfo.name}</h4></div>` +
                            `<p>Total rec.: ${countryInfo.count.toLocaleString()}<br>` +
                            `<a href="${countryInfo.link}" target="_blank">Go to records</a></p></div>`
                        );
                    }
                }
            }).addTo(map);
        } catch (error) {
            console.error(`Nie udało się wczytać danych dla kraju ${countryCode}: ${error.message}`);
        }
    }

    
}

// Inicjalizuj mapę
initMap().catch((error) => console.error('Błąd inicjalizacji mapy:', error));

</script>
