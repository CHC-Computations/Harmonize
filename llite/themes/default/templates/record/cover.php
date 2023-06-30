<?php 
	$img_file= $this->HOST."themes/default/images/no_cover.png";
	
	if (!empty($rec->record->ISSN)) {
		#echo $rec->record->ISSN;
		$file = str_replace('-', '', $rec->record->ISSN);
		$fn = 'files/covers/medium/'.$file.'.jpg';
		
		if (file_exists($fn)) {
			$img_file = $this->HOST.$fn;
			}
		
		}
?>

<img src="<?= $img_file ?>" alt="no cover found" class="img img-responsive">

