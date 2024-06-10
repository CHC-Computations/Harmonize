<?php 
$key = $result->solrRecord->wikiq;	

if (!empty($result->solrRecord->picture))
	$photoBox = '<div class="box-Image"><img src="'.$this->buffer->convertPicturePath(current($result->solrRecord->picture),'small').'"></div>';
	else 
	$photoBox = '
		<div class="box-Image empty">
			<img src="'. $this->HOST .'themes/default/images/no_photo.svg" alt="no cover found" class="img img-responsive no-photo">
		</div>';

echo '
	<div class="box-Body" id="place_'.$key.'">
		'.$photoBox.'
		<div class="box-Desc">
			<div class="box-linkPanel" id="linkPanel_'.$key.'">'.$this->render('wikiResults/resultBoxes/linkPanel.php', ['AP' => $result->linkPanel()] ) .'</div>
			<div class="box-head">
				<h4>
				  <a href="'. $this->buildUri('wiki/record/'.$result->solrRecord->wikiq) .'" title="'. $this->transEsc('card of').'...">
					'. $result->getStr('labels') .' 
				  </a>
				</h4>
			</div>
			<p>'. $this->helper->setLength($result->getStr('descriptions'),155) .'</p>';
if (empty($result->solrRecord->bottomLink))
	echo '<a class="box-bottom-link" href="'. $this->buildUri('wiki/record/'.$result->solrRecord->wikiq) .'" title="'. $this->transEsc('card of').'...">'. $this->transEsc('More about') .'...</a>';
	else 
	echo '<a class="box-bottom-link" href="'. $result->solrRecord->bottomLink .'" title="'. $result->solrRecord->bottomStr .'">'. $result->solrRecord->bottomStr .'</a>';
			
echo '</div>
	</div>
	';


$this->addJS("
	tekst = $('#place_{$key}').html();
	$('#placeBox_{$key}').html(tekst);
	tekst = $('#content_{$key}').html();
	$('#placeBoxAppend_{$key}').html(tekst);
	");
?>

