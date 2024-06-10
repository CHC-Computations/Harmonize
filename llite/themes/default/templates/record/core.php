<?php 



$mapChecksCount = 6;
$mapDraw = '<br/>';
$mapDraw.= $this->maps->drawWorldMap([]); // $placesList.
$mapDraw.= '<div id="mapRelationsAjaxArea">'.$this->helper->loader2();
$mapDraw.= '<form id="mapDrowCheckboxes">';
for ($i = 1; $i<=$mapChecksCount; $i++) 
	$mapDraw .= '<input type="checkbox" checked id="map_checkbox_'.$i.'" >';
$mapDraw .= '</form>';
$mapDraw .= '</div>';
$this->addJS('results.maps.addBiblioRecRelatations("'.$this->record->getId().'")');			
$this->addJS('results.btnPrevNext("'.$this->record->getId().'");');
$this->addJS('results.collapseLongValues();');


$extraTabs = [
			'details' 	=> [
					'label' => $this->transEsc('Marc view'),	
					'content' => '<br/>'.$this->record->drawMarc()
					],
			# 'jsonview' 	=> ['label' => $this->transEsc('Json view'), 	'content' => "<br/><pre style='background-color:transparent; border:0px;'>".print_r($this->record->marcJson, 1)."</pre>" ],
			'jsonrel' 	=> [
					'label' => $this->transEsc('ELB fields'), 	
					'content' => '<br/><pre id="json-renderer" style="background-color:transparent; border:0px;">'.print_r($this->record->elbRecord, 1).'</pre>' 
					],
			'map' 		=> [
					'label' => $this->transEsc('Map'), 			
					'content' => $mapDraw
					],
			];

$this->addJS('
	var data = '.json_encode($this->record->elbRecord).'
	$("#json-renderer").jsonViewer(data,  {collapsed: true, rootCollapsable : false, withLinks: false, bigNumbers: true});
	');

if (!empty(($relRec->linkedResources))) {
	foreach ($relRec->linkedResources as $linkedResource)
		if (!empty($linkedResource->fullText) && $linkedResource->fullText) {
			$extraTabs['fullText'] = [
				'label' => $this->transEsc('Full text'), 	
				'content' => '<iframe src="'.$linkedResource->link.'" title="'.$linkedResource->desc.'" style="width:100%; height:30vh; border: 0"></iframe><br/><br/> Content from: <a href="'.$linkedResource->link.'">'.$linkedResource->link.'</a>' 
				];
			}
	}

$bottomMenu = $this->record->getELaA_full();
if (is_array($bottomMenu))
	foreach ($bottomMenu as $k=>$ln) {
		#echo "<pre>".print_r($ln,1).'</pre>';
		$content = '<div class=" results-list">';
		$LP = 0;
		foreach ($ln as $srec) {
			$LP++;
			$content .= $this->render('record/by-link.php', ['rec' => $srec, 'lp'=>$LP] );
			}
		$content .= '</div>';
		$extraTabs[$k] = [
				'label' => $this->transEsc(ucfirst($k)),
				'content' => $content
				];
		}

if ($this->record->hasSimilar()) {
	$this->addJS('page.post("recHasSimilar", "results/record.has.similars", '.json_encode($this->record->get('similars')).')');
	
	$simStr = '<div class="results" id="recHasSimilar">';
	$simStr .= $this->helper->loader2();
	$simStr.='</div>';
	
	$extraTabs['similar'] = [
			'label' => $this->transEsc('Other versions'),
			'content' => $simStr
			];
	}


?> 


<div class="graybox">
  <div class="infopage">
    <div class="infopage-header">
	   <h1 property="name"><?= $this->record->getTitle() ?></h1>
	</div>
	<div class="record">
	
		
		<div class="record-left-panel">
			<div class="thumbnail">
				<?= $this->render('record/cover.php', ['rec' => $this->record]) ?>
			
			</div>
		</div>
		<div class="record-main-panel">
			
			<?= $this->record->getRETpic() ?>
			
			<ul class="detailsview">
				<?= $this->render('record/fields/only-text.php', [
							'label'=>$this->transEsc('Statement of Responsibility'),  	
							'value'=>$this->record->get('StatmentOfResp')
							]) ?>
				<?= $this->render('record/fields/authors-link.php', [
							'label'=>$this->transEsc('Main author'),  					
							'value'=>$this->record->get('persons', 'mainAuthor')
							]) ?>
				<?= $this->render('record/fields/authors-link.php', [
							'label'=>$this->transEsc('Corporate as main author'),  	
							'value'=>$this->record->get('corporates', 'mainAuthor')
							]) ?>
				<?= $this->render('record/fields/authors-link.php', [
							'label'=>$this->transEsc('Other authors'),  				
							'value'=>$this->record->get('persons', 'coAuthor')
							]) ?>
				<?= $this->render('record/fields/authors-link.php', [
							'label'=>$this->transEsc('Corporate Author'),  			
							'value'=>$this->record->get('corporates', 'coAuthor')
							]) ?>
				<?= $this->render('record/fields/biblio-link.php', 	[
							'label'=>$this->transEsc('Format'), 				
							'facetField'=>'format_major', 		
							'translated'=>true,
							'value'=>$this->record->get('majorFormat')
							]) ?>
				<?= $this->render('record/fields/biblio-link.php', 	[
							'label'=>$this->transEsc('Publication language'),  
							'facetField'=>'language', 
							'translated'=>true,
							'value'=>$this->record->get('language','publication')
							]) ?>
				<?= $this->render('record/fields/biblio-link.php', 	[
							'label'=>$this->transEsc('Original language'),  	
							'facetField'=>'language_o_str_mv', 	
							'translated'=>true,
							'value'=>$this->record->get('language','original')
							]) ?>
				
				<?= $this->render('record/fields/places-link.php', 	[
							'label'=>$this->transEsc('Publication place'),  	
							'facetField'=>'geographicpublication_str_mv', 	
							'translated'=>true,
							'value'=>$this->record->get('places','publication')
							]) ?>
				
				<?= $this->render('record/fields/biblio-link.php', 	[
							'label'=>$this->transEsc('In'),  			
							'facetField'=>'magazines_str_mv', 	
							'value'=>$this->record->get('publishedIn')
							]) ?>
				<?= $this->render('record/fields/biblio-link.php', 	[
							'label'=>$this->transEsc('Edition'),  		
							'facetField'=>'edition', 	
							'linkValue'=>$this->record->get('edition')->no ?? $this->record->get('edition'),
							'value'=>$this->record->get('edition')->str ?? $this->record->get('edition')
							]) ?>
				<?= $this->render('record/fields/refered-work.php', 	[
							'label'=>$this->transEsc('Referred work'), 
							'value'=>$this->record->get('referedWork')
							]) ?>
				<?= $this->render('record/fields/biblio-link.php', 	[
							'label'=>$this->transEsc('Seria'),  		
							'facetField'=>'series', 
							'value'=>$this->record->get('seria')
							]) ?>
				
				<?= $this->render('record/fields/formGenre-link.php', 	[
							'label'=>$this->transEsc('Form / Genre'),  
							'value'=>$this->record->get('subject','formGenre')
							]) ?>
				<?= $this->render('record/fields/UDC-link.php', 		[
							'label'=>$this->transEsc('Universal Decimal Classification'),  
							'facetField'=>'udccode_str_mv', 
							'value'=>$this->record->get('subject','UDC')]) 
							?>
				<?= $this->render('record/fields/authors-link.php', 	[
							'label'=>$this->transEsc('Subject persons'), 
							'value'=>$this->record->get('persons', 'subjectPerson')
							]) ?>
				<?= $this->render('record/fields/subjects-link.php', 	[
							'label'=>$this->transEsc('Subjects'), 	
							'facetField'=>'subjects_str_mv', 
							'value'=>$this->record->get('subject','strings')
							]) ?>
				<?= $this->render('record/fields/only-text.php', [
							'label'=>$this->transEsc('Annotation'),  	
							'value'=>$this->record->get('description')
							]) ?>

			</ul>
			
			
		</div>
		<div class="record-right-panel">
			<?= $this->render('record/side-menu.php'); ?>
		</div>
	</div>

	
	<div class="tabs-panel">
		<?= 
		$this->helper->tabsCarousel( $extraTabs , 'map');
		?>
    </div>
	  
	<span class="Z3988" title="<?=$this->record->getCoinsOpenURL() ?>"></span> 
  </div>
</div>
<div id="recordAjaxAddsOn"></div>



