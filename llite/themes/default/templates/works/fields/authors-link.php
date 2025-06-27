<?php
if (!empty($value)) {
	echo '<dl class="detailsview-item">
			<dt class="dv-label">'.$label.':</dt>
			<dd class="dv-value">
			';
	$list = false;
	if (count((array)$value)>1) {
		echo '<ol>';
		$list = true;
		}
	foreach ($value as $personId => $author) {
		if ($list) echo '<li>';
		echo $this->render('works/fields/persons-link.php', ['persons' => $author, 'personId'=>$personId, 'hideRole'=>$hideRole ?? '']);
		if ($list) echo '</li>';
		}
	if ($list) echo '</ol>';	
	echo '</dd>
		</dl>';
	}

?>