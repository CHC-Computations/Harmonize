<?php 
echo '<h1>'.$this->transEsc('Browse in category').': <b>'.$this->transEsc($facetName).'</b></h1>';
echo '<p>'.$this->transEsc('Number of all items on this list').': '.$this->helper->numberFormat($total).'</p>';
echo '<ol class="list-group">';
if (is_Array($results))
	foreach ($results as $result=>$resultCount) {
		$key = $this->buffer->createFacetsCode([$currentFacet.':"'.$result.'"']);
		echo '<li class="list-group-item"><a href="'.$this->buildUri('results', ['core'=>'biblio', 'facetsCode'=>$key] ).'">'.$result.' <span class="badge">'.$this->helper->numberFormat($resultCount).'</span></a></li> ';
		}

if ($total<9999)
	echo '</ol>';

?>