<?php 
$this->addClass('buffer', 	new buffer()); 
$this->addClass('helper', 	new helper()); 


$this->setTitle('500a test');


$testLines = ["Tytuł oryginału: The lord's highland temptation. Na stronie tytułowej także inne siedziby wydawcy.",
	"Tytuł oryginału: Undeniable. Tytuł oryginału cyklu: Cloverleigh farms.",
	"Podstawa edycji: Komedyja z francuskiego języka na polski przetłumaczona w dzień dorocznej uroczystości Klemensa Świętego patrona Jaśnie Wielmożnego Jegomości Pana Klemensa Zamoyskiego, ordynata, starosty płoskirowskiego reprezentowana roku 1752 miesiąca listopada 23. dnia. Tytuł oryginału: Le médecin malgré lui.",
	"Tytuł oryginału cyklu: The great schools of Dune.",
	"Tytuł oryginału: The blood. Tytuł oryginału cyklu: Monstress."
	];
	
$keyLines = ["Tytuł oryginału cyklu:", 'Tytuł oryginału:'];

$file = './files/mrk/500 a.csv';
$fp = @fopen($file, "r");
		

echo $this->render('head.php');
echo $this->render('core/header.php');

$i=0;
$keyStr = 'Tytuł oryginału:';
if ($fp) {
	while (($buffer = fgets($fp, 8192)) !== false) {
		$line = str_getcsv($buffer);
		if (!empty($line[1])) {
			$testLine = $line[1];
			if (stristr($testLine, $keyStr)) {
				$tmp = explode($keyStr, $testLine);
				if (!empty($tmp[1])) {
					$tmp = explode('.', $tmp[1]);
					$title = $tmp[0];
					echo str_replace($title, '<b>'.$title.'</b>', $testLine).'<br/>'; 
					}
				$i++;
				if ($i == 5000)
					break;
				}
			}
		}
	}	


		
echo $this->render('helpers/report.error.php'); 
echo $this->render('core/footer.php');		
?>