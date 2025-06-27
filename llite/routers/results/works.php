<?php 
if (empty($this)) die;

require_once('functions/class.helper.php');
require_once('functions/class.forms.php');
require_once('functions/class.converter.php');
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.bookcart.php');

$this->addClass('buffer', 	new buffer($this)); 
$this->addClass('solr', 	new solr($this)); 
$this->addClass('helper', 	new helper($this)); 
$this->addClass('forms', 	new forms($this)); 
$this->addClass('convert', 	new converter($this));
$this->addClass('bookcart',	new bookcart());

$currentCore = 'biblio';
$workKey = $this->routeParam[0];
$maxPage = $this->configJson->$currentCore->summaryBarMenu->pagination->maxPagesAlowed ?? 100;


if ($this->getCurrentPage()	< $maxPage) {

	$this->forms->values($this->GET);
 
	if (!empty($this->configJson->$currentCore->summaryBarMenu))
		foreach ($this->configJson->$currentCore->summaryBarMenu as $block=>$values) 
			if (!empty($this->GET[$block]))
				$this->saveUserParam($currentCore.':'.$block, $this->GET[$block]);
				else if (empty($this->getUserParam($currentCore.':'.$block))) 
				$this->saveUserParam($currentCore.':'.$block, $this->configJson->$currentCore->summaryBarMenu->$block->default);



	if (!empty($this->GET['swl'])) { // start with letter ...
		$sl = strtolower(substr($this->GET['swl'],0,1));
		#echo "Starting with: $sl<br/>";
		switch ($sort) {
			case 'author_sort asc': $sfield = 'author_sort'; break;
			case 'title_sort asc': $sfield = 'title_sort'; break;
			default : $sfield = '';
			}
		if ($sfield<>'')
			$query[] = [
					'field' => 'q',
					'value' => "($sfield:$sl*)"
					];
		}


	$lookfor = $this->getParam('GET', 'lookfor');
	$type = $this->getParam('GET', 'type');


	$query['q'] = [ 
				'field' => 'q',
				'value' => '*:*'
				];
	
	
	$query['sort']=[ 
				'field' => 'sort',
				'value' => 'datesort_str_mv ASC'
				];
	
	$query['fq'] = [ 
				'field' => 'fq',
				'value' => 'workkey_str:"'.$workKey.'"'
				];
			
	$query['facet']=[ 
				'field' => 'facet',
				'value' => 'true'
				];
			
	$query['rows']=[ 
			'field' => 'rows',
			'value' => 500
			];
	
	
	$query['start']=[ 
			'field' => 'start',
			'value' => 0
		 	];		
	
	$query['q.op']=[ 
			'field' => 'q.op',
			'value' => 'OR'
			];
	
	
	# echo $this->helper->pre($query);
	$results = $this->solr->getQuery('biblio',$query); 
	$results = $this->solr->resultsList();
	
	if (!empty($lookfor))
		$this->buffer->saveSearch('biblio', $lookfor);
	
	
	$workRecord = new stdClass;
	$workRecord->id = $workKey;


	foreach ($results as $result) {
		$record = 
		$relations = json_decode($result->relations);
		
		if (!empty($result->fullrecord)) {
			$this->addClass('record', new bibliographicRecord($result, $this->convert->mrk2json($result->fullrecord)));
			$field = 246;
			if (!empty($this->record->marcJson->$field) && count($this->record->marcJson->$field)==1)
				foreach ($this->record->marcJson->$field as $line) {
					if (!empty($line->code->f))
						@$workRecord->yearOfCreation[$line->code->f]++;
					}
			} else 
			$this->addClass('record', new bibliographicRecord($result));
		
		
		if (!empty($relations->titleOrgin->title))
			@$workRecord->titleOrgin[$relations->titleOrgin->title]++;
		
		
		if (!empty($relations->subject->strings))
			foreach ($relations->subject->strings as $subArr)
				foreach ($subArr as $topic)
					@$allSubjects[$topic]++;
		if (empty($relations->language->original) && !empty($relations->language->publication))
			$relations->language->original = $relations->language->publication;
					
		if (!empty($relations->language->publication))
			foreach ($relations->language->publication as $lang) {
				@$publicationLanguages[$lang]++;
				@$workRecord->titles[$record->title][$lang]++;
				}
		if (!empty($relations->language->original))
			foreach ($relations->language->original as $lang)
					@$originalLanguages[$lang]++;
		
		
		if (!empty($relations->isbn))
			foreach ($relations->isbn as $isbn)
					@$workRecord->isbn[$isbn]++;
		
		if (!empty($relations->persons->all))
			foreach ($relations->persons->all as $key=>$person) {
				@$workRecord->personsCount[$key]++;
				if ($person->role != 'mainAuthor')
					foreach ($person->roles as $role) {
						$workRecord->coAuthors[$role][$key] = $person;
						
						}
				}
		
		if (!empty($relations->places->publicationPlace))
			foreach ($relations->places->publicationPlace as $key=>$place) {
				@$workRecord->publicationPlacesCount[$key]++;
				$workRecord->publicationPlace[$key] = $place;
				}
		
		if (!empty($relations->publicationYear)) {
			$where = ['unknown'];
			if (!empty($relations->publishedIn))
				$where = (array)$relations->publishedIn;
			foreach ($relations->publicationYear as $year) 
				foreach ($where as $publishedIn)
					@$workRecord->publicationYear[$year][$publishedIn] = $relations->id;
				
			}
		
		$this->buffer->addToBottomSummary($relations);
		}
		
	$title = '';
	if (!empty($workRecord->titleOrgin)) {
		arsort($workRecord->titleOrgin);
		foreach ($workRecord->titleOrgin as $titleVersions => $titlesCount) {
			if (empty($title))
				$title = $titleVersions;
			}
		} else {
		if (!empty($workRecord->titles)) {
			arsort($workRecord->titles);
			foreach ($workRecord->titles as $titleVersions => $titlesCount) {
				if (empty($title))
					$title = $titleVersions;
				}	
			}
		}
	$workRecord->title = $title;
		
	$this->setTitle($this->transEsc('Work').': '.$title);
	
	
	if (!empty($allSubjects))
		arsort($allSubjects);
	$workRecord->subjects = $allSubjects ?? [];
	if (!empty($publicationLanguages))
		arsort($publicationLanguages);
	$workRecord->publicationLanguages = $publicationLanguages ?? [];
	if (!empty($orginalLanguages))
		arsort($originalLanguages);
	$workRecord->originalLanguages = $originalLanguages ?? [];
	
	
	#echo $this->helper->pre($relations);
	$workRecord->mainAuthor = $relations->persons->mainAuthor ?? new stdClass;
	
	@$this->workRecord = $workRecord;
	} else {
	$results = new stdClass;
	$results->exception = '<h2>'.$this->transEsc('For reasons of server performance, we can now only present the first 100 pages of results').'. '.$this->transEsc('Try using filters or search').'.</h2>';
	$results->exception .= '<a href="'. $this->buildUrl('results/biblio', ['facetsCode'=>$this->facetsCode, 'page'=>1]) .'">'. $this->transEsc('Go back to the first page') .'</a>';
	}

if (!empty($this->buffer->usedFacets['user_list']) & ($this->solr->totalResults()==0)){
	$results = new stdClass;
	$results->exception = $this->helper->alertIco('warning', 'ph ph-clock-clockwise', $this->transEsc('The data in ELB has been re-indexed. Restoration of collection is in progress.'.'<div id="restoreCollectionAjaxBox">'.$this->helper->loader2().'</div>')); 
	$this->addJS("page.post('restoreCollectionAjaxBox', 'user/lists/restore.collection', ".json_encode($this->buffer->usedFacets['user_list']).")");
	}	

$this->head->meta = implode("\n", $this->meta);
?> 

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<?= $this->render('works/core.php', ['results'=>$results, 'workRecord'=>$workRecord, 'currentCore'=>$currentCore] ) ?> 

<?= $this->render('helpers/report.error.php') ?> 
<?= $this->render('core/footer.php') ?>
