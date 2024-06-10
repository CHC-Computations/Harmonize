<?php 
if (!empty($publisher->name))
	echo '<a href="'.$this->buildUrl('results/biblio/', ['lookfor' =>$publisher->name, 'type'=> 'publisher' ]).'">'.$publisher->name.'</a> ';
if (!empty($publicationYear))
	echo '<a href="'.$this->buildUrl('results/biblio/', ['lookfor' =>$publicationYear, 'type'=> 'publicationYear' ]).'">'.$publicationYear.'</a>';

?>