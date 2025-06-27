<?php 
if (empty($this)) die;

require_once('functions/class.forms.php');
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.helper.php');
require_once('functions/class.converter.php');
require_once('functions/class.maps.php');
require_once('functions/class.wikidata.php');
require_once ('./functions/class.lists.php');


$rec_id = $recordCalled = $this->routeParam[0];


$this->addClass('forms', new forms);
$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->addClass('convert', 	new converter()); 
$this->addClass('wikiData', new wikiData($this)); 
$this->addClass('lists', new lists());

$solrRecord = $this->solr->getRecord('biblio', $rec_id);

			
if (!empty($solrRecord->id)) {
	
	$marcJson = $this->convert->mrk2json($solrRecord->fullrecord);
	$this->addClass('record', new bibliographicRecord($solrRecord, $marcJson));
	
	
	$languageFile = file('config/import/languageMap.csv');
	foreach ($languageFile as $line) {
		$l = str_getcsv($line, ';');
		$languageMap1[$l[0]] = $l[2];
		$languageMap2[$l[0]] = $l[1];
		}
	$publicationLanguage = $this->record->getLanguage('publication');
	$publicationLanguageCode = $languageMap1[$publicationLanguage] ?? '';
	
	

	$this->forms->setGrid(2,8);
	
	$this->forms->values([
		'language' => $this->record->getLanguage('publication'),
		'title_'.$publicationLanguageCode => $this->record->getTitle(),
		]);
	
	
	echo '<form class="elb-forms">';
	
	
	$titleStr = '';
	foreach ($this->configJson->settings->multiLanguage->order as $langCode) {
		if ($langCode == $publicationLanguageCode)
			$lineTitle = '<b>'.$this->transEsc($langCode).'</b>';
			else 
			$lineTitle = $this->transEsc($langCode);
		
		$titleStr .= $this->forms->row(
			$rowId = 'title_'.$langCode,
			$lineTitle,
			$this->forms->input('text', $rowId)
			);
		}
	echo $this->helper->panelCollapse(uniqid(), $this->transEsc('Title').': <b>'.$this->record->getTitle().'</b>', $titleStr);
	
	echo $this->helper->panelSimple(
		$this->transEsc('Publication language').': <b>'.$publicationLanguage.'</b> ('.$publicationLanguageCode.')<br/>'.
		$this->transEsc('Publication year').': <b>'.$this->record->getPublicationYear().'</b> <br/>'
		);
	
	
	
	$takeRoles = ['mainAuthor', 'coAuthor'];
	$i = 0;
	foreach ($takeRoles as $roleToTake) {
		$persons = $this->record->get('persons', $roleToTake);
		$total = count((array)$persons);
		foreach ($persons as $person) {
			$authors[$i] = (array)$person;
			$roles = $person->roles; 
			$authors[$i]['rolesStr'] = implode(' ', (array)$person->roles); 
			if ($i == 0) 
				$authors[$i]['actions'] = '<button class="table-list-btn" disabled title="'.$this->transEsc('First position').'"><i style="color:transparent" class="ph ph-arrow-up"></i></button>';
				else 
				$authors[$i]['actions'] = '<button class="table-list-btn" onClick="" title="'.$this->transEsc('Move up').'"><i class="ph ph-arrow-up"></i></button>';	
			if ($i == $total-1)
				$authors[$i]['actions'] .= '<button class="table-list-btn" onClick="" title="'.$this->transEsc('Move down').'"><i class="ph ph-arrow-down"></i></button>';
				else 
				$authors[$i]['actions'] .= '<button class="table-list-btn" disabled title="'.$this->transEsc('last position').'"><i style="color:transparent" class="ph ph-arrow-down"></i></button>';
			$authors[$i]['actions'] .= '
				<button class="table-list-btn" onClick="" title="'.$this->transEsc('remove').'"><i class="ph ph-trash"></i></button>
				';
			if ($i == 0) 
				$authors[$i]['actions'] .= '<button class="table-list-btn" disabled onClick="" title="'.$this->transEsc('Main author').'"><i class="ph ph-crown"></i></button>';
			$i++;
			}
		}
	$total = count($authors);
	$this->lists->dataColumns = array (
			array( 'class' => 'technical right',	'function' => 'counter',	'title' => $this->transEsc('No.') ),
			array( 'class' => 'major bold',			'field' => 'name', 			'title' => $this->transEsc('Name') ), 
			array( 'class' => 'technical',			'field' => 'dates', 		'title' => $this->transEsc('Dates range') ), 
			array( 'class' => 'technical',			'field' => 'viaf', 			'title' => $this->transEsc('VIAF') ), 
			array( 'class' => 'technical bold',		'field' => 'wikiQ', 		'title' => $this->transEsc('wikidata ID') ), 
			array( 'class' => 'technical',			'field' => 'rolesStr', 		'title' => $this->transEsc('Roles') ), 
			array( 'class' => 'technical', 			'field' => 'actions',		'title' => $this->transEsc('Actions') )
			);	
	$max 			= $this->lists -> SetMax(50);
	$subPages 		= $this->lists -> subPages ($total);
	$tableHeaders 	= $this->lists -> headers ();
	$lp				= $this->lists -> startPoint;
	$sort			= $this->lists -> sorting;
	$tableContent	= $this->lists -> content($authors);
	echo $this->helper->panelCollapse(uniqid(), $this->transEsc('Authors'),
		'
		<div style="margin:-14px;">
		<table class="table table-hover table-lists">
			'.$tableHeaders.$tableContent.'
		</table>
		</div>
		<form class="form-horizontal">
		<div class="form-group">
			<label class="control-label col-sm-2">
				'.$this->transEsc('Add').': 
			</label>
			<div class="col-sm-10">
				<input type="text" id="ac_input_author" class="form-control">
				<div id="ac_search_author"></div>
			</div>
		</div>
		
		</form>
		'
		);
	$this->addJS("ac.lookfor('author', 'persons')");
	#echo $this->helper->pre($authors);
	




	echo '</form>';
	echo '<br/><br/>';	
	

	#echo $this->helper->pre($this->forms->values);
		
	} else {
		
	echo $this->transEsc('Record with id __recID__ not exists', ['recID'=>$rec_id]);	
	}




?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" ><i class="ph ph-check"></i> <?= $this->transEsc('Save') ?></button>
    <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="ph ph-x"></i> <?= $this->transEsc('Close') ?></button>
</div>