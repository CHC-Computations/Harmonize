<?php

echo "TEST";
require 'marc21/File/MARC.php';

// Retrieve a set of MARC records from a file
$journals = new File_MARC('marc21/marcfiles/pbl_marc_articles2021-8-4.mrc');



	$record = $journals->next();
	
	
    // Iterate through the fields
    foreach ($record->getFields() as $tag => $subfields) {
        echo "<b>$tag</b>:";
		
		if (method_exists($subfields,'getSubfields'))
			foreach ($subfields->getSubfields() as $code => $value) {
                echo "$code: $value<br/>";
				}
			
		echo "pre:<pre>".print_r($subfields,1)."</pre>";
		echo "<br/>";
	
    }

	
    echo "<hr/>";
    echo "<pre>".print_r( $record,1 ). "</pre>";
	


?>