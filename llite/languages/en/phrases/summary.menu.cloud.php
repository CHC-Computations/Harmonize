<?php 
$return = '<b>'.$corePercent.'%</b> ('.$this->helper->numberFormat($biblioRecWithLinks).') of bibliografic records contains informations about '.$core.'.<br/>
	This creates '.$this->helper->numberFormat($sumOfLinks).' links between bibliographic records and '.$core.'.</br>
	<b>'.$wikiPercent.'%</b> ('.$this->helper->numberFormat($totalWikiBiblioRec).') of these links are represented in the '.$core.' collection. <br/>';
?>						