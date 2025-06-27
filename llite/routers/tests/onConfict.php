<?php 

$res = $this->psql->queryObject("INSERT INTO elb_errors (msg) 
VALUES ('Repeated 245') 
ON CONFLICT (msg) 
DO UPDATE SET msg = EXCLUDED.msg 
RETURNING id;
");
var_dump ($res);

echo '<br/>';

$res = $this->psql->queryObject("WITH ins AS (
    INSERT INTO elb_errors (msg) 
    VALUES ('Repeated 245') 
    ON CONFLICT (msg) DO NOTHING 
    RETURNING id
)
SELECT id FROM ins
UNION 
SELECT id FROM elb_errors WHERE msg = 'Repeated 245';", 'id');
var_dump ($res);


/*
Które rozwiązanie jest szybsze?

    Jeśli większość operacji to nowe wstawienia, ON CONFLICT DO UPDATE będzie szybsze, ponieważ nie wymaga dodatkowego zapytania.
    Jeśli większość operacji dotyczy już istniejących wartości, metoda WITH ins AS (...) UNION SELECT będzie wydajniejsza, bo unika zbędnych operacji UPDATE.
	
*/