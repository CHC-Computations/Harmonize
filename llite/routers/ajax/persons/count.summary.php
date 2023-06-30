<?php 
if (empty($this)) die;
$this->JS[] = "page.ajax('col2', 'persons/full.list/viafid')";

$this->addClass('buffer', new marcBuffer()); 
$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this->config)); 

$this->buffer->setSql($this->sql);
$facets = $this->getConfig('facets');

$currFacet = "author_facet_s";
$facetName = $facets['facetList'][$currFacet];

$queryoptions[]=[ 
				'field' => 'facet.sort',
				'value' => 'index'
				];
$queryoptions[]=[ 
				'field' => 'json.facet', //json.facet={x:"unique(author_facet_s)"}
				'value' => '{x:"unique('.$currFacet.')"}'
				];
$queryoptions[]=[
				'field' => 'facet.limit',
				'value' => '5'
				];
				
$results = $this->solr->getFacets('biblio', [$currFacet], $queryoptions);

echo '<a href="" class="side-menu-item">';
echo $this->transEsc('All persons in LiBRI').' <span class="badge">'.$this->solr->getFacetsCount().'</span>';
echo '</a>';



$res = $this->sql->query("SELECT count(*) as ile FROM libri_biblio_persons;");
if (!empty($res->num_rows) && ($res->num_rows>0)) {
	$row = mysqli_fetch_assoc($res);
	echo '<a href="" class="side-menu-item active">';
	echo $this->transEsc('Persons with VIAF').' <span class="badge" id="countWithViaf">'.$row['ile'].'</span>';
	echo '</a>';

	}


$res = $this->sql->query("SELECT count(*) as ile FROM  libri_persons ;");
if (!empty($res->num_rows) && ($res->num_rows>0)) {
	$row = mysqli_fetch_assoc($res);
	echo '<a href="" class="side-menu-item">';
	echo $this->transEsc('Persons with auto-info-box').' <span class="badge" id="countWithIB">'.$row['ile'].'</span>';
	echo '</a>';

	}
	
	
echo '<a href="" class="side-menu-item">';
echo $this->transEsc('Persons with manual/checked info').' <span class="badge">???</span>';
echo '</a>';






#echo "<pre>".print_r($results,1)."</pre>";

?>