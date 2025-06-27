<?php

	if (true || $tabsToShow['bibliographicalStatistics']) {
		echo '<div id="bibliographicalStatistics">'.$this->helper->loader2().'</div>';		
		$this->addJS("page.post('bibliographicalStatistics', 'wiki/bibliographicalStatistics/{$this->wiki->getID()}/{$recType}');");
		}

		

	echo '<hr/><br/><br/>';
	echo $this->transEsc('note_3');
?>

<div id="drawPoints"></div> 