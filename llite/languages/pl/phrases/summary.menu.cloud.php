<?php 
$core1 = $core2 = '';
switch ($core) {
	case 'persons' : 
		$core1 = 'osobach'; 
		$core2 = 'osób';
		break;
	case 'corporates' : 
		$core1 = 'organizacjach, instytucji, stowarzyszeń'; 
		$core2 = 'organizacji';
		break;
	case 'magazines' : 
		$core1 = 'czasopismach'; 
		$core2 = 'czasopism';
		break;
	case 'places' : 
		$core1 = 'miejscach'; 
		$core2 = 'miejsc';
		break;
	case 'events' : 
		$core1 = 'wydarzeniach'; 
		$core2 = 'wydarzeń';
		break;
		
	}

$return = '<b>'.$corePercent.'%</b> ('.$this->helper->numberFormat($biblioRecWithLinks).') rekordów bibliograficznych zawiera informacje o '.$core1.' (w dowolnej roli).<br/>
	W sumie zebrano '.$this->helper->numberFormat($sumOfLinks).' zapisów na temat '.$core2.'.</br>
	<b>'.$wikiPercent.'%</b> ('.$this->helper->numberFormat($totalWikiBiblioRec).') tych zapisów jest reprezentowane w kolekcji '.$core2.'. <br/>';
?>						