<?php 
$active = '';


if (!empty($activeFacets['user_list']))
	foreach ($activeFacets['user_list'] as $v) {
		$tmp = explode('|', $v['value']);
		$return = $this->bookcart->getListDetails(intval($tmp[1]));
		if (!empty($return['list_description']))
			echo $this->helper->alert('white',  '<h4>'.$return['list_name'].'</h4>'.$return['list_description']); //'ph '.$return['list_ico'],
		}

if (!empty($activeFacets['source_db_str'])) {
	$sourceToCode = [
		'Polska Bibliografia Literacka' => 'pl',
		'Česká Literární Bibliografie' => 'cz',
		'Biblioteca Nacional de España' => 'es',
		'Kansalliskirjasto' => 'fi',
		];
		
	$links = [
		'cz' => 'https://pl.wikipedia.org/wiki/Czechy#/media/Plik:Czech_Republic_in_European_Union.svg',
		'pl' => 'https://pl.wikipedia.org/wiki/Polska#/media/Plik:Poland_in_European_Union.svg',
		'fi' => 'https://pl.wikipedia.org/wiki/Finlandia#/media/Plik:Finland_in_European_Union.svg',
		'es' => 'https://pl.wikipedia.org/wiki/Hiszpania#/media/Plik:EU-Spain.svg',
		];	
	
	$pictures = [
		'cz' => 'https://upload.wikimedia.org/wikipedia/commons/6/6f/Czech_Republic_in_European_Union.svg',
		'pl' => 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Poland_in_European_Union.svg',
		'fi' => 'https://upload.wikimedia.org/wikipedia/commons/c/c5/Finland_in_European_Union.svg',
		'es' => 'https://upload.wikimedia.org/wikipedia/commons/2/21/EU-Spain.svg',
		];	
		
		
	foreach ($activeFacets['source_db_str'] as $v) {
		$name = str_replace('"', '', $v['value']);
		$code = $sourceToCode[$name] ?? '';
		$templatePath = '/cms/sources-header-box/'.$this->userLang.'-'.$code.'.php';
		$templateDef = '/cms/sources-header-box/'.$this->defaultLanguage.'-'.$code.'.php';
		$picture = '';
		if (!empty($pictures[$code]))
			$picture = '
				<div class="picture"><img src="'.$pictures[$code].'" style=""><br/>
				<a class="small" href="'.$links[$code].'">commons.wikimedia.org</a>
				</div>';
		
		if ($this->templatesExists($templatePath))
			echo $this->helper->alert('default header-box', $picture.$this->render($templatePath));	
			else if ($this->templatesExists($templateDef))
			echo $this->helper->alert('default header-box', $picture.$this->render($templateDef));	
		}
	}

?>	