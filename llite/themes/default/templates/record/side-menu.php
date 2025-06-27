<?php 

$citeThis = $this->render('search/record/inmodal/cite.php');
$citeThisB = base64_encode($citeThis);

$box_id = str_replace('.','_', $this->record->getId());


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


	
$barMenu = '';
$barMenu = $this->helper->drawSideMenu($sideMenu);

if (empty($this->bookcart->isOnLists($this->record->getId())))
	$listClass = '';
	else 
	$listClass = ' active';

$onLine = '';
if (!empty(($this->record->elbRecord->linkedResources))) {
	foreach ($this->record->elbRecord->linkedResources as $linkedResource) {
		if (!empty($linkedResource->fullText) && $linkedResource->fullText) {
			$onLine = '<div class="list-group">
				<a href="'.$linkedResource->link.'"  title="'.$linkedResource->desc.'" class="list-group-item" style="cursor:pointer" >
					<i class="ph-bold ph-arrow-square-out"></i> '.$this->transEsc('Full text / online').'
				</a>
				</div>';
			}
		}
	}

$barMenu .= '
		<div class="list-group">
		<button class="list-group-item" style="cursor:pointer" OnClick="results.fixedLink(\''.$this->transEsc('Fixed link to this record').'\',\''.$this->record->getId().'\');">
			<i class="ph-bold ph-share-network"></i> '.$this->transEsc('Share').'
		</button>
		'.$onLine.'
		</div>
		'. $this->bookcart->resultCheckBox($this->record->getId()) .' '.$this->transEsc('Your lists').'';





if ($this->user->isLoggedIn() && $this->user->hasPower('editor')) {
	$rowOnClick = "page.postInModal('{$this->transEsc('Edit record content')}', 'service/data/biblio.record.edit/{$this->record->getId()}', '{$this->record->getId()}');";
	
	/*
	$barMenu .= '<div class="list-group">
		<button class="list-group-item list-group-item-success" style="cursor:pointer" OnClick="'.$rowOnClick.'">
			<i class="ph-bold ph-pencil"></i> '.$this->transEsc('Edit record').'
		</button>
		</div>';
	*/
	$this->user->addToMenu('<button class="btn btn-success" style="cursor:pointer; margin:8px;" OnClick="'.$rowOnClick.'">
			<i class="ph-bold ph-pencil"></i> '.$this->transEsc('Edit record').'
			</button>');
	}

?>
<?= $barMenu ?>
<div id="stickyArea<?=$box_id?>"><?= $this->bookcart->resultStickyNote($this->record->getId()) ?></div>