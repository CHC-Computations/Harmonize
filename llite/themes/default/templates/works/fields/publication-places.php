<?php
if (!empty($value)) {
	
	if (!empty($workRecord->publicationPlacesCount)) {
		arsort($workRecord->publicationPlacesCount);
	
		echo '<dl class="detailsview-item">
				<dt class="dv-label">'.$label.':</dt>
				<dd class="dv-value">
				';
		$list = false;
		if (count($workRecord->publicationPlacesCount)>1) {
			echo '<ol>';
			$list = true;
			}
		foreach ($workRecord->publicationPlacesCount as $key => $count) {
			$author = $value[$key];
			if ($list) echo '<li>';
			echo $this->render('works/fields/persons-link.php', ['persons' => $author, 'personId'=>$key, 'hideRole'=>$hideRole ?? '']);
			echo '<span class="badge" style="float:right">'.$count.'/'.$publicationsTotal.'</span>';
			if ($list) echo '</li>';
			}
		if ($list) echo '</ol>';	
		
		
		echo '</dd>
			</dl>';
		}
	}
// @$workRecord->publicationPlacesCount[$key]++;
?>