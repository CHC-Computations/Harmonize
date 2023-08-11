<?php 

$citeThis = $this->render('search/record/inmodal/cite.php');
$citeThisB = base64_encode($citeThis);

$coreFields = $this->record->getCoreFields();


$formats = $this->getConfig('export');
foreach ($formats['ExportFormats'] as $k=>$v)
	$exportMenu[] = array (
		'title' => $v,
		'link' 	=> $this->selfUrl('.html', '.'.$k)
		);

		
		
$sideMenu[] = array (
		'ico' 	=> 'fa fa-asterisk',
		'title' => $this->transEsc('Cite this'),
		'onclick' => "results.citeThis('{$this->transEsc('Cite this')}', '{$rec->id}');",
		);

$sideMenu[] = array (
		'ico' 	=> 'fa fa-download',
		'title' => $this->transEsc('Export record to').'...',
		'submenu'=> $exportMenu
		);
/*
$sideMenu[] = array (
		'ico' 	=> 'fa fa-star',
		'title' => $this->transEsc('Save to List'),
		'link' 	=> $this->selfUrl()
		);
*/


if (is_array($this->buffer->isOnMyLists($rec->id)))
	$sideMenu[] = array (
		'ico' 	=> 'fa fa-minus',
		'title' => $this->transEsc('Remove from Book Bag'),
		'id'	=> 'ch_'.$rec->id,
		'onclick' 	=> "results.myList('{$rec->id}', 'myListSingleRec')",
		'class'	=> 'active'
		);
	else 
	$sideMenu[] = array (
		'ico' 	=> 'fa fa-plus',
		'title' => $this->transEsc('Add to Book Bag'),
		'id'	=> 'ch_'.$rec->id,
		'onclick' 	=> "results.myList('{$rec->id}', 'myListSingleRec')"
		);


$barMenu = $this->helper->drawSideMenu($sideMenu);


$placesList = '';
if (is_array($regions))
	foreach ($regions as $place) {
		$placesList .= $this->render('record/place-link-simple.php', ['place'=>$place]);
		}
$mapDraw = $this->maps->drawWorldMap($Tmap); // $placesList.
$mapDraw.= '<div id="mapRelationsAjaxArea">'.$this->helper->loader2().'
				<input type="checkbox" checked id="map_checkbox_1" >
				<input type="checkbox" checked id="map_checkbox_2" >
				<input type="checkbox" checked id="map_checkbox_3" >
				<input type="checkbox" checked id="map_checkbox_4" >
				<input type="checkbox" checked id="map_checkbox_5" >
				<input type="checkbox" checked id="map_checkbox_6" >
			</div>';
$this->addJS("results.maps.addBiblioRecRelatations('".$rec->id."')");			

$extraTabs = [
			'details' 	=> ['label' => $this->transEsc('Marc view'),	'content' => $this->record->drawMarc()],
			'jsonview' 	=> ['label' => $this->transEsc('Json view'), 	'content' => "<pre style='background-color:transparent; border:0px;'>".print_r($this->record->fullRecord, 1)."</pre>" ],
			'map' 		=> ['label' => $this->transEsc('Map'), 			'content' => $mapDraw],
			];
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

if (!empty($similar) && (count($similar)>0)) {
	$simStr = '<div class=" results-list">';
	foreach ($similar as $simRec) {
		#$simStr .= '<a href="'.$this->buildUrl('search/record/'.$simRec->id.'.html').'"><b>'.$simRec->title.'</b></a><br>';
		$simStr .= $this->render('record/by-link.php', ['rec' => (array)$simRec, 'lp'=>$simRec->lp] );
		}
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
				<?php foreach ($coreFields as $func=>$current): ?>
					<?php if (!empty($current['content'])): ?>
						<dl class="detailsview-item">
						  <dt class="dv-label"><?=$this->transEsc($current['label'])?>:</dt>
						  <dd class="dv-value"><?= $this->render('helpers/CollapseBox.php', ['desc'=>$current['content'], 'minSize'=>130]) ?></dd>
						</dl>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php if (!empty($this->record->getDescription())): // na prośbe B.Wachek 2022-12-12 ?>
					<dl class="detailsview-item">
					  <dt class="dv-label"><?=$this->transEsc('Annotation')?>:</dt>
					  <dd class="dv-value"><?= $this->render('helpers/CollapseBox.php', ['desc'=>$this->record->getDescription(), 'minSize'=>130]) ?></dd>
					</dl>
				<?php endif; ?>
					
			</ul>
			
			
		</div>
		<div class="record-right-panel">
			<?= $barMenu ?>
		</div>
	</div>


	
	<span class="Z3988" title="<?=$this->record->getCoinsOpenURL() ?>"></span>
	
	<?php 
	
	if (!empty($this->record->bottomLists->persons)) {
		echo '<div class="hidden">';
		$personsList = $this->record->bottomLists->persons;

		foreach ($personsList as $key=>$personLine) {
			$AP = (object)$personLine;
			if (!empty($AP->wikiq)) {
				$wikiIDint = $AP->wikiq;
				$wikiId = 'Q'.$AP->wikiq;
				$wiki = new wikidata($wikiId);
				$activePerson = $wiki->getActivePersonValues();
				$activePerson->wiki = $wiki;
				$AP->field = "personBoxQ".$AP->wikiq;
				
				$activePerson->wiki = new wikidata($wikiId); 
				$activePerson->wiki->setUserLang($this->user->lang['userLang']);

				$photo = $this->buffer->loadWikiMediaUrl($activePerson->wiki->getStrVal('P18'));

				echo '<div class="person-info" id="'.$AP->field.'">';
				echo $this->render('persons/results/list-wiki.php',['activePerson'=>$activePerson, 'photo'=>$photo]);
				echo '</div>';
				
				$this->addJS("$('.personBox{$AP->wikiq}').html($('#{$AP->field}').html());"); 
				
				} 
			}
		echo '</div>';
		}
	?>
	
	<div class="tabs-panel">
		<?= 
		$this->helper->tabsCarousel( $extraTabs , 'map');
		?>
	  
    </div>
	  
	 
  </div>
</div>
<div id="recordAjaxAddsOn"></div>

<?php 
	$tabId = $this->helper->lastId; 
	$this->addJS("results.btnPrevNext('{$rec->id}');");
	#$this->addJS("$('.ind_{$tabId}').removeClass('active'); $('#ind_{$tabId}_0').addClass('active');$('#myCarousel{$tabId}').carousel(0);");

	
?>

