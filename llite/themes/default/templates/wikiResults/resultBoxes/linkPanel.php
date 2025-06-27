<?php 
$this->addJS('$(\'[data-toggle="tooltip"]\').tooltip();');
$libriLink = '';

if (!empty($AP->as)) {
	arsort($AP->as);
	foreach ($AP->as as $key => $count) {
		$libriLink .= '
			<li>
				<a href="'. $this->buildUri('results', ['core'=>'biblio', 'facetsCode'=> $this->buffer->createFacetsCode(["with_roles_wiki:\"Q{$AP->wikiq}|$key\""])], false ) .'" 
					title="'.$this->helper->numberFormat($count).' '.$this->transEsc($this->helper->formatMajorRole($key)->title).'" data-toggle="tooltip" data-placement="bottom">
				<i class="'.$this->helper->formatMajorRole($key)->ico.'"></i> <span>'.$this->helper->badgeFormat($count).'</span>
				</a>
			</li>
			';
		}
	}
	

if (!empty($AP->wikiq))
	$wikiLink = '
			<li><a href="'.$this->buildUri('wiki/record/Q'.$AP->wikiq).'" title="'.$this->transEsc('ELB record card').'" data-toggle="tooltip" data-placement="bottom"><i class="ph-address-book-bold"></i></a></li>
			<li><a href="https://www.wikidata.org/wiki/Q'.$AP->wikiq.'" target=_blank title="WikiData" data-toggle="tooltip" data-placement="bottom"><i class="glyphicon glyphicon-barcode"></i></a></li>
			';
	else 
	$wikiLink = '<li><a style="opacity:0.2; filter: grayscale(100%);" title="WikiData"><i class="glyphicon glyphicon-barcode"></i></a></li>';	


if (!empty($AP->viaf_id))
	$viafLink = '<li><a href="https://viaf.org/viaf/'.$AP->viaf_id.'" target=_blank title="VIAF" data-toggle="tooltip" data-placement="bottom"><i class="ph-identification-card-bold"></i></a></li>';
	else 
	$viafLink = '<li><a style="opacity:0.2; filter: grayscale(100%);" title="VIAF" data-toggle="tooltip" data-placement="bottom"><i class="ph-identification-card-bold"></i></a></li>';	
#echo "<textarea>".print_r($AP,1).'</textarea>';
?>
	<div class="bulkActionButtons">
		<ul class="action-toolbar">
			<?= $libriLink ?>
			<?= $wikiLink ?>
			<li><a href="https://www.google.com/search?q=<?=urlencode(strip_tags($AP->name))?>" target="_blank" title="Google" data-toggle="tooltip" data-placement="bottom"><i class="ph-google-logo-bold"></i></a></li>
		</ul>
	</div>