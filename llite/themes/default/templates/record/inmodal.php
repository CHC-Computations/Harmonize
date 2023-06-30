<?php 



$cite = str_replace('.html','', $this->linkParts[3]);

$coreFields = $this->record->getCoreFields();

$exportMenu[] = array (
		'title' => 'MARC',
		'link' 	=> $this->selfUrl('.html', '.mrc')
		);
$exportMenu[] = array (
		'title' => 'MARCXML',
		'link' 	=> $this->selfUrl('.html', '.marcxml')
		);
$exportMenu[] = array (
		'title' => 'Json',
		'link' 	=> $this->selfUrl('.html', '.json')
		);
$exportMenu[] = array (
		'title' => 'RDF',
		'link' 	=> $this->selfUrl('.html', '.rdf')
		);
		
$exportMenu[] = array (
		'title' => 'BibTeX',
		'link' 	=> $this->selfUrl('.html', '.btx')
		);
$exportMenu[] = array (
		'title' => 'RIS',
		'link' 	=> $this->selfUrl('.html', '.ris')
		);
		
		
$sideMenu[] = array (
		'ico' 	=> 'fa fa-asterisk',
		'title' => $this->transEsc('Cite this'),
		'onclick'=> "results.citeThis('".$this->transEsc('Cite this')."', '{$cite}');"
		);

$sideMenu[] = array (
		'ico' 	=> 'fa fa-download',
		'title' => $this->transEsc('Export record to').'...',
		'submenu'=> $exportMenu
		);

$sideMenu[] = array (
		'ico' 	=> 'fa fa-star',
		'title' => $this->transEsc('Save to List'),
		'link' 	=> $this->selfUrl()
		);

$sideMenu[] = array (
		'ico' 	=> 'fa fa-plus',
		'title' => $this->transEsc('Add to Book Bag'),
		'link' 	=> $this->selfUrl()
		);



$barMenu = $this->helper->drawSideMenu($sideMenu);

$extraTabs = [
			'details' 	=> ['label'=>'Marc view', 'content'=>$this->record->drawMarc()],
			'jsonview' 	=> ['label'=>'Json view', 'content'=>"<pre style='background-color:transparent; border:0px;'>".print_r($this->record->fullRecord, 1)."</pre>" ],
			'map' 		=> ['label'=>'Map', 'content'=>'<img src="'.$this->HOST.'themes/default/images/extras/word2.svg" class="img img-responsive">' ],
			];
$bottomMenu = $this->record->getELaA_full();
if (is_array($bottomMenu))
	foreach ($bottomMenu as $k=>$ln) {
		#echo "<pre>".print_r($ln,1).'</pre>';
		$content = '<ol class="extra-records-list">';
		foreach ($ln as $rec) 
			$content .= $this->render('record/by-link.php', ['rec' => $rec] );
		$content .= '</ol>';
		$extraTabs[$k] = [
				'label' => $this->transEsc(ucfirst($k)),
				'content' => $content
				];
		}


?> 
	<div class="record">
	
		
		<div class="record-left-panel">
			<div class="thumbnail">
				<?= $this->render('record/cover.php', ['rec' => $this->record]) ?>
			
			</div>
		</div>
		<div class="record-main-panel">
			
			<?= $this->record->getRETpic() ?>
			<?= $this->record->getDescription() ?>
			
			<ul class="detailsview">
				<?php foreach ($coreFields as $func=>$current): ?>
					<dl class="detailsview-item">
					  <dt class="dv-label"><?=$this->transEsc($current['label'])?>:</dt>
					  <dd class="dv-value"><?=$current['content']?></dd>
					</dl>
				<?php endforeach; ?>
			</ul>
			
			
		</div>
		<div class="record-right-panel">
			<?= $barMenu ?>
		</div>
		
	</div>
	
	
	<div class="tabs-panel">
		<?= 
		$this->helper->tabsCarousel( $extraTabs , 'details');
		?>
	  
    </div>
	  

<script>
$("#inModalTitle").html('<?= $this->record->getTitle()?>');
</script>