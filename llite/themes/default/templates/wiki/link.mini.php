<?php 
	
	if (!empty($value)) {
		$this->wikiRec->loadRecord($value); 
		$link = '<a href="'.$this->buildURL('wiki/record/'.$value).'">'.$this->wikiRec->get('labels').'</a>';
	
		echo $link;
		}
?>