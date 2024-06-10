<?php 
if (empty($this)) die;
$lp = $this->GET['lp'];

$this->addClass('buffer', new buffer()); 
$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this)); 

$currAction = $this->routeParam[0];
$currFacet = $this->routeParam[1];
$userLang = $this->routeParam[2];
$core = $this->routeParam[4];
$this->facetsCode = $this->routeParam[6] ?? null;
$this->buffer->getFacets($this->facetsCode);	

$this->loadJsonSettings($core);

$facetName = $this->configJson->$core->facets->solrIndexes->$currFacet->name ?? $this->configJson->$core->facets->facetsMenu->$currFacet->name ;

	
if (!empty ($this->GET['add'])) {
	if (!empty ($_SESSION['facets_chosen'][$currFacet][$this->GET['add']]))
		$this->GET['remove'] = $this->GET['add'];
		else {
		$key = $this->buffer->shortHash($this->GET['add']);
		$_SESSION['facets_chosen'][$currFacet][$this->GET['add']] = $key;
		
		$this->addJS("$('#tcheck_{$currFacet}_{$key}').addClass('ph-check-square-bold'); ");
		$this->addJS("$('#tcheck_{$currFacet}_{$key}').removeClass('ph-square-bold'); ");
		}
	}	
if (!empty ($this->GET['remove'])) {
	$key = $this->buffer->shortHash($this->GET['remove']);
	unset($_SESSION['facets_chosen'][$currFacet][$this->GET['remove']]);
	
	$this->addJS("$('#tcheck_{$currFacet}_{$key}').removeClass('ph-check-square-bold'); ");
	$this->addJS("$('#tcheck_{$currFacet}_{$key}').addClass('ph-square-bold'); ");
	}	
	
#unset($this->routeParam);	 
echo $this->render('search/inmodal/facet-search-box-chosen-core.php', ['facetName'=>$facetName, 'currFacet'=>$currFacet, 'core'=>$core ] );



#echo "$currFacet,$lp";
#echo $this->facetsCode.$this->helper->pre($this->buffer->usedFacetsStr	);
#echo $this->facetsCode.$this->helper->pre($this->routeParam);
?>