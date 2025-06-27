<?php 

	// try: https://vufind-test.ucl.cas.cz/Cover/Show?size=large&recordid=002071394&source=Solr
	// read: https://docs.google.com/document/d/1b2iw31p5Izs0cHyDmErETQSp4KppBVm05BBCkiw67kI/edit?pli=1
	// Vojta needed to register :-)
	$coversFolder = './files/covers/medium/';
	$imageFieldId = 'coverImage'.$this->record->getIdStr();
	$ajaxFieldId = 'lookForCover'.$this->record->getIdStr();



	$img_file = $this->HOST."themes/default/images/no_cover.png";
	$altText = $this->transEsc('no cover found');
	$hasLocalCover = false;
	
	if (!empty($this->record)) {
		$fieldsToCheck = ['issn', 'isbn'];
		foreach ($fieldsToCheck as $field)
			if (!empty($this->record->get($field))) {
				$numbers = $this->record->get($field);
				foreach ($this->record->get($field) as $value) {
					#$value = end($numbers);
					$file = str_replace('-', '', $value);
					$fn = 'files/covers/'.$field.'/medium/'.$file.'.jpg';
					
					if (file_exists($fn)) {
						$img_file = $this->HOST.$fn;
						$altText = $this->transEsc('Cover image');
						$hasLocalCover = true;
						}
					}
				}	
		
		$field = 'isbn';		
		if (!$hasLocalCover && !empty($this->record->get($field))) {
			echo '<div id="'.$ajaxFieldId.'" class="hidden"></div>';
			$jsonISBN = json_encode($this->record->get($field));
			$jsonISBNbase64 = base64_encode($jsonISBN);
			$this->addJS("page.ajax('$ajaxFieldId', 'results/look.for.cover/{$this->record->getId()}/$jsonISBNbase64' )");
			}		
		}

	if (empty($this->record->getRETpic()))
		echo '<img src="'.$img_file.'" alt="'.$altText.'" class="img img-responsive" id="'.$imageFieldId.'">';
		else 
		echo $this->record->getRETpic(false);
?>



