<?php 
if (empty($this)) die;
	require_once('functions/class.buffer.php');
	require_once('functions/class.places.php');
	$this->addClass('solr', new solr($this));
	$this->addClass('buffer', new buffer());
	$this->buffer->setSQL($this->sql);
	$this->addClass('places', new places($this->sql));

	if (empty($_SESSION['checklist'])){
		echo "ładuję listę";
		$_SESSION['checklist'] = $this->solr->getFullList('geographic_facet');
		$this->addJS("page.ajax('apiCheckBox','places/import');");
		} else {
		echo "sprawdzam: ";	
		foreach ($_SESSION['checklist']->results as $placeName=>$placeCount) {
			echo "$placeName<br/>";
			$res = $this->buffer->getPlaceParams($placeName);
			unset($_SESSION['checklist']->results[$placeName]);
			break;
			}
		# echo "<pre>".print_R($_SESSION['checklist'],1)."</pre>";	
		if (count($_SESSION['checklist']->results)>0) {
			$this->addJS("page.ajax('apiCheckBox','places/import');");
			} else {
			unset($_SESSION['checklist']);	
			}
		}

	
	$placesList = $this->places->getFullList();		
	echo "<br>  places: <b>".count($placesList).'</b>';
	echo "<br>  places to check: <b>".count($_SESSION['checklist']->results).'</b>';
	
 
?>