<?php 

$citeThis = $this->render('search/record/inmodal/cite.php');
$citeThisB = base64_encode($citeThis);


$sideMenu[] = array (
		'ico' 	=> 'fa fa-asterisk',
		'title' => $this->transEsc('Cite this'),
		'onclick' => "results.citeThis('{$this->transEsc('Cite this')}', '{$this->record->getId()}');",
		);
		
$formats = $this->configJson->biblio->recordCard->exportFormats ?? null;
if (!empty($formats))
	foreach ($formats as $k=>$v)
	$exportMenu[] = array (
		'title' => $v,
		'link' 	=> $this->selfUrl('.html', '.'.$k)
		);
$sideMenu[] = array (
		'ico' 	=> 'fa fa-download',
		'title' => $this->transEsc('Export record to').'...',
		'submenu'=> $exportMenu
		);

if (is_array($this->buffer->isOnMyLists($this->record->getId())))
	$sideMenu[] = array (
		'ico' 	=> 'fa fa-minus',
		'title' => $this->transEsc('Remove from Book Bag'),
		'id'	=> 'ch_'.str_replace('.','_',$this->record->getId()),
		'onclick' 	=> "results.myList('{$this->record->getId()}', 'myListSingleRec')",
		'class'	=> 'active'
		);
	else 
	$sideMenu[] = array (
		'ico' 	=> 'fa fa-plus',
		'title' => $this->transEsc('Add to Book Bag'),
		'id'	=> 'ch_'.str_replace('.','_',$this->record->getId()),
		'onclick' 	=> "results.myList('{$this->record->getId()}', 'myListSingleRec')"
		);
	
$barMenu = $this->helper->drawSideMenu($sideMenu);
$barMenu .= '<div class="list-group">
		<button class="list-group-item" style="cursor:pointer" OnClick="results.fixedLink(\''.$this->transEsc('Fixed link to this record').'\',\''.$this->record->getId().'\');">
			<i class="ph-bold ph-share-network"></i> '.$this->transEsc('Share').'
		</button>
		</div>';



?>
<?= $barMenu ?>