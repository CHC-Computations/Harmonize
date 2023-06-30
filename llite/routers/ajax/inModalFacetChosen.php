<?php 
if (empty($this)) die;
$lp = $this->GET['lp'];

$this->addClass('buffer', new marcBuffer()); 
$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this->config)); 

$this->buffer->setSql($this->sql);
#echo "<prE>".print_r($this->GET,1)."</pre>";


$facets = $this->getConfig('facets');


$currAction=$this->routeParam[0];
$currFacet=$this->routeParam[1];
$this->facetsCode = $this->routeParam[count($this->routeParam)-1];

#echo 'facetCode: '.$this->facetsCode;
$facetName = $facets['facetList'][$currFacet];

	
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
	
echo $this->render('search/inmodal/facet-search-box-chosen.php', ['facetName'=>$facetName, 'currFacet'=>$currFacet ] );
	
#echo "$currFacet,$lp";
?>