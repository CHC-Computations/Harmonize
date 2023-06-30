<?php 
	
	if (!empty($value)) {
		$rec = new wikidata($value); 
		$rec->setUserLang($this->user->lang['userLang']);
		$link = '<a href="'.$this->buildURL('wiki/record/'.$value).'">'.$rec->get('labels').'</a>';
	
		echo $link;
		}
?>