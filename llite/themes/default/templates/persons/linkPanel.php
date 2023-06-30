<?php 

$libriLink = '';
if (!empty($AP->as_author))
if ($AP->as_author > 0)
	$libriLink .= '
		<li>
			<a href="'. $this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$this->buffer->createFacetsCode($this->sql, ["author_facet:\"{$AP->solr_str}\""]), ['lookfor'=>'','type'=>''] ) .'" title="'.$this->transEsc('As an author').'">
			<i class="ph-pen-nib-bold"></i> <span>'.$AP->as_author.'</span>
			</a>
		</li>
		';
	else 
	$libriLink .= '
		<li>
			<a style="opacity:0.2; filter: grayscale(100%);" title="'.$this->transEsc('As an author').'">
			<i class="ph-pen-nib-bold"></i>
			</a>
		</li>
		';
		
if (!empty($AP->as_topic))		
if ($AP->as_topic > 0)	
	$libriLink .= '	
		<li>
			<a href="'. $link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$this->buffer->createFacetsCode($this->sql, ["subject_person_str_mv:\"{$AP->solr_str}\""]), ['lookfor'=>'','type'=>''] ).'"  title="'.$this->transEsc('As topic person').'">
			<i class="ph-user-focus-bold"></i> <span>'.$AP->as_topic.'</span>
			</a>
		</li>
		';
	else 
	$libriLink .= '
		<li>
			<a style="opacity:0.2; filter: grayscale(100%);" title="'.$this->transEsc('As topic person').'">
			<i class="ph-user-focus-bold"></i>
			</a>
		</li>
		';
	

if (!empty($AP->wikiq))
	$wikiLink = '
			<li><a href="'.$this->basicUri().'wiki/record/Q'.$AP->wikiq.'" title="WikiData on Libri"><i class="ph-address-book-bold"></i></a></li>
			<li><a href="https://www.wikidata.org/wiki/Q'.$AP->wikiq.'" target=_blank title="WikiData"><i class="glyphicon glyphicon-barcode"></i></a></li>
			';
	else 
	$wikiLink = '<li><a style="opacity:0.2; filter: grayscale(100%);" title="WikiData"><i class="glyphicon glyphicon-barcode"></i></a></li>';	


if (!empty($AP->viaf_id))
	$viafLink = '<li><a href="https://viaf.org/viaf/'.$AP->viaf_id.'" target=_blank title="VIAF"><i class="ph-identification-card-bold"></i></a></li>';
	else 
	$viafLink = '<li><a style="opacity:0.2; filter: grayscale(100%);" title="VIAF"><i class="ph-identification-card-bold"></i></a></li>';	

?>
	<div class="bulkActionButtons">
		<ul class="action-toolbar">
			<?= $libriLink ?>
			<?= $wikiLink ?>
			<li><a href="https://www.google.com/search?q=<?=urlencode($AP->name)?>" target="_blank" title="Google"><i class="ph-google-logo-bold"></i></a></li>
			<?= $viafLink ?>
		</ul>
	</div>