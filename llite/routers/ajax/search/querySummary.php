<?php 
if (empty($this)) die;
$this->addClass('buffer', 	new marcBuffer());
$this->addClass('solr', new solr($this->config)); 

$this->addJS('$("#querySummary").css("opacity","1"); ');

$search = $this->getConfig('search');
$facets = $this->getConfig('facets');

$sortNames = $this->getIniParam('search', 'sortnames');
$authorFormat = $this->getIniArray('facets', 'facetOptions','authorFormats');


################################################################################
##				Akcje w formularzu
################################################################################
if (!empty($_SESSION['advSearch']['form']))
	$groups = $_SESSION['advSearch']['form'];

if (!empty($this->POST['action'])) {
	$actions = $this->POST['action'];
	if (!empty($actions['newValue'])) {
		$tmp = explode('-', $actions['field']);
		if (count($tmp)==3)
			$groups[$tmp[0]][$tmp[1]][$tmp[2]] = $actions['newValue'];
		if (count($tmp)==2)
			$groups[$tmp[0]][$tmp[1]] = $actions['newValue'];
		}
	$this->addJS('advancedSearch.facets();');
	}
$_SESSION['advSearch']['form'] = $groups;
################################################################################
##				Akcje na facetach 
################################################################################

if (!empty($this->GET['change'])) {
	$currentFacet = $this->routeParam[0];
	$lp = $this->routeParam[1];
	if (empty($_SESSION['facetsChosen'][$currentFacet]))
		$_SESSION['facetsChosen'][$currentFacet] = [];
	if (array_key_exists($this->GET['change'], $_SESSION['facetsChosen'][$currentFacet])) {
		unset($_SESSION['facetsChosen'][$currentFacet][$this->GET['change']]);
		$this->addJS("$('#tcheck_{$currentFacet}_{$lp}').removeClass('ph-check-square-bold'); ");
		$this->addJS("$('#tcheck_{$currentFacet}_{$lp}').addClass('ph-square-bold'); ");
		} else {
		$_SESSION['facetsChosen'][$currentFacet][$this->GET['change']] = $lp;
		$this->addJS("$('#tcheck_{$currentFacet}_{$lp}').addClass('ph-check-square-bold'); ");
		$this->addJS("$('#tcheck_{$currentFacet}_{$lp}').removeClass('ph-square-bold'); ");
		}
	#echo "#tcheck_{$currentFacet}_{$lp}";
	}
	
	
	
if (!empty($this->GET['sortby'])) {
	$currentSort = $this->routeParam[1];
	if ($this->routeParam[0] == 'add'){
		$_SESSION['advSortBy'][$currentSort] = $this->GET['sortby'];
		} 
	if ($this->routeParam[0] =='remove') {
		echo "removing $currentSort<br/>";
		unset($_SESSION['advSortBy'][$currentSort]);
		}
	$this->addJS('advancedSearch.sortby();');
	}	


################################################################################
##				wyświetlanie czego szukamy 
################################################################################

$lookForStr = '<h4>'.$this->TransEsc('Look for').':</h4>';	
$LP = 0;
if (!empty($_SESSION['advSearch']['form'])) {
	
	$formsChosen = $_SESSION['advSearch']['form'];
	if (!empty($_SESSION['advSearch']['form']['operator']))
		$formsOper = $_SESSION['advSearch']['form']['operator'];
		else {
		$formsOper['g'] = 'and';	
		foreach ($formsChosen as $k=>$v)
			$formsOper[$k] = 'and';
		}
	#unset($formsChosen['oparator']);
	
	foreach ($formsChosen as $groupKey=>$group) 
		if (is_numeric($groupKey)) {
			$lp = 0;
			$LP++;
			if ($LP>1)
				$lookForStr.= '<div style="margin:6px; margin-top:-18px;">'.$formsOper['g'].'</div>';
	
			$lookForStr.= '<ul class="list-group">';
			foreach ($group as $itemKey=>$item) {
				$lp++;
				if ($lp>1)
					$op = $formsOper[$groupKey].' ';
					else 
					$op = '';
				if (empty($item['lookfor']))
					$lookfor = '""';
					else 
					$lookfor = '"'.$item['lookfor'].'"';
				$lookForStr.= '<li class="list-group-item">'.$this->transEsc($op).
					$this->transEsc($item['type']).' '.
					$this->transEsc($item['meth']).' '.
					$lookfor.
					'</li>';
				}
			$lookForStr.= '</ul>';
			}
		
	$lookForStr.="<pre>".print_r($formsChosen,1)."</prE>";
	
	$searchKey = md5($searchJson = json_encode($formsChosen));
	$_SESSION['advSearch']['json'] = $searchJson;
	}


################################################################################
##				wybrane filtry 
################################################################################

$activeFacetsStr = '';	
if (!empty($_SESSION['facetsChosen'])) {
	$LP = 0;
	$facetsChosen = $_SESSION['facetsChosen'];
	foreach ($facetsChosen as $currentFacet=>$arr) 
		if (count($arr)>0) {
			$LP++;
			if ($LP>1)
				$activeFacetsStr.= '<div style="margin:6px; margin-top:-18px;">'.$this->transEsc('and').'</div>';
			$activeFacetsStr.= '<ul class="list-group">
					<li class="list-group-item">'.$this->transEsc( $facets['facetList'][$currentFacet]).":</li>";
			$lp=0;
			
			foreach ($arr as $facetValue=>$key) {
				$activeFacetsStr.= '<li class="list-group-item">';
				$lp++;
				if (in_array($currentFacet, $authorFormat))
					$tvalue = $this->helper->authorFormat($facetValue);
					else if ( array_key_exists($currentFacet, $facets['facetDate']) ) {
					$tvalue = str_replace(',',' - ',$facetValue);
					
					} else 
					$tvalue = $this->transEsc($facetValue);
				if ($lp>1)
					$activeFacetsStr.= $this->transEsc('or').' ';
				$oc = "advancedSearch.AddRemove('change','$facetValue', '$currentFacet', '$key')";
	
				
				$activeFacetsStr.= '<b>'.$tvalue.'</b> <button class="close" title="remove" OnClick="'.$oc.'"><i class="ph-x-bold"></i></button>';
				$activeFacetsStr.= '</li>';
				if ( array_key_exists($currentFacet, $facets['facetDate']) ) {
					$input_value='~'.$currentFacet.':['.str_replace(',',' TO ',$facetValue).']';
					$input_key=$currentFacet.':['.str_replace(',',' TO ',$facetValue).']';
					} else {
					$input_value='~'.$currentFacet.':"'.$facetValue.'"';
					$input_key=$currentFacet.':"'.$facetValue.'"';
					}
				$facetsList[$input_key] = $input_value;
				
				}
			$activeFacetsStr.= '</ul>';
			}
	#echo "<pre>".print_R($facetsList,1).'</pre>';
	if (!empty($facetsList)) {
		$this->facetsCode = $this->buffer->createFacetsCode($this->sql, $facetsList);
		$query[] = $this->buffer->getFacets($this->sql, $this->facetsCode);	
		}
	}	
if ($activeFacetsStr !== '')
	$activeFacetsStr = '<h4>'.$this->TransEsc('Limit to').':</h4>'.$activeFacetsStr;

################################################################################
##				Sortowanie
################################################################################
$sortByStr = '';
$sortLink = [];

if (!empty($_SESSION['advSortBy'])) {
	$sorts = $_SESSION['advSortBy'];
	$sortByStr .= '<div class="list-group">';
	$sortByStr .= '<div class="list-group-item">';
	foreach ($sorts as $sortKey=>$sortValue) {
		#echo "$sortKey<pre>".print_R($sortOptions,1)."</prE>";
		if ($sortKey>1) 
			$sortByStr.=$this->transEsc('then').' ';
		$sortByStr .= $this->transEsc($sortNames[$sortValue]).'<br/>';
		$sortLink[$sortKey]=$sortValue;
		}
	$sortByStr .= '</div>';
	$sortByStr .= '</div>';
	}
$sort = implode(',',$sortLink);

if ($sortByStr !== '')
	$sortByStr = '<h4>'.$this->TransEsc('Sort by').':</h4>'.$sortByStr;

	
	
	
	
	
	
	
	
	
	

	

################################################################################
##				Wyświetlanie
################################################################################	
	
	
echo $lookForStr;
echo $activeFacetsStr;	
echo $sortByStr;
	
echo '
		<div class="text-right">
			
			<a href="'.$this->buildUri('search/results',['page'=>'1', 'sort'=>$sort, 'sk'=>$searchKey, 'sj'=>$searchJson]).'" class="btn btn-primary">
				<i class="ph-magnifying-glass-bold"></i> '. $this->transEsc("Search") .'
			</a>
		</div>
	';	


$query[] = [ 
		'field' => 'q',
		'value' => $this->solr->advandedSearch($searchJson)
		];	
$this->solr->getQuery('biblio',$query); 		
$results_number = number_format($this->solr->totalResults(),0,'','.'); 	
echo $this->transEsc('Expected number of results').': <span id=results_number><b>'.$results_number.'</b></span>';
	


	
/*	
echo "routeParam<pre>".print_r($this->routeParam,1)."</pre>";
echo "GET<pre>".print_r($this->GET,1)."</pre>";
echo "POST<pre>".print_r($this->POST,1)."</pre>";
*/