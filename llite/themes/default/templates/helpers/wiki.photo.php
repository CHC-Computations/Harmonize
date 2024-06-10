<?php 
	$title = $this->wiki->get('labels');
	if (empty($title))
		$title = $this->transEsc('some picture');
	if (empty($photo = $this->buffer->loadWikiMediaUrl($this->wiki->getStrVal('P18')))) {
		$img = $this->HOST.'themes/default/images/no_photo.svg';
		$pic = '<img src="'.$img.'" title="'.$title.'" style="opacity:0.2;  filter: grayscale(100%);">';
		} else {
		$picB = base64_encode('<div class="text-center"><img src="'.$photo.'" class="img img-responsive"></div>');
		$OC = "OnClick=\"results.InModal('$title','$picB');\"";
		$pic = '<img src="'.$photo.'" title="'.$title.'" style="cursor:pointer;" '.$OC.'>';
		
		}
	?>
<?= $pic ?>		