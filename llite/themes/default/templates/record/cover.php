<?php 

	// try: https://vufind-test.ucl.cas.cz/Cover/Show?size=large&recordid=002071394&source=Solr
	// read: https://docs.google.com/document/d/1b2iw31p5Izs0cHyDmErETQSp4KppBVm05BBCkiw67kI/edit?pli=1
	// Vojta needed to register :-)


	$img_file= $this->HOST."themes/default/images/no_cover.png";
	
	
	
	if (!empty($this->record)) {
		$fieldsToCheck = ['issn', 'isbn'];
		foreach ($fieldsToCheck as $field)
			if (!empty($this->record->get($field))) {
				$value = current($this->record->get($field));
				$file = str_replace('-', '', $value);
				$fn = 'files/covers/medium/'.$file.'.jpg';
				
				if (file_exists($fn)) {
					$img_file = $this->HOST.$fn;
					}
				}	
		}
	
?>

<img src="<?= $img_file ?>" alt="no cover found" class="img img-responsive">

