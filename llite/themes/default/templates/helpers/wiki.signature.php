<?php 
	$title = $this->wiki->get('labels');
	$pic = '';
	if (empty($title))
		$title = $this->transEsc('some picture');
	if (!empty($photo = $this->buffer->loadWikiMediaUrl($this->wiki->getStrVal('P109')))) {
		$picB = base64_encode('<div class="text-center"><img src="'.$photo.'" class="img img-responsive"></div>');
		$OC = "OnClick=\"results.InModal('$title','$picB');\"";
		$pic = '<img src="'.$photo.'" title="'.$title.'" style="cursor:pointer;" '.$OC.'>';
		
		}
	?>
<?= $pic ?>		