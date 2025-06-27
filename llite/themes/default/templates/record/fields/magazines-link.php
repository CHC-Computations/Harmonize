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
	foreach ($value as $uid => $item) {
		if ($list) echo '<li>';
		echo $this->render('record/fields/magazine-link.php', ['item' => $item, 'facetField'=>$facetField ?? '']);
		if ($list) echo '</li>';
		}
	if ($list) echo '</ol>';	
	echo '</dd>
		</dl>';
	}

?>