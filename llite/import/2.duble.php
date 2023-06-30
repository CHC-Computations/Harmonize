<?php

$file = file("errors/psql.txt");
foreach ($file as $line) 
	if (stristr($line, "Błąd wykonania zapytania - (INSERT INTO biblio (id, title, title_sort) VALUES ('")) {
		$x = str_replace("Błąd wykonania zapytania - (INSERT INTO biblio (id, title, title_sort) VALUES ('", '', $line);
		$y = explode("'", $x);
		$id = $y[0];
		echo "$id\n";
		file_put_contents("errors/dubleId.txt", "$id\n", FILE_APPEND);
		
		}