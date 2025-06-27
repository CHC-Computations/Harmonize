<?php 
if (empty($this)) die;
$this->addClass('buffer', 	new buffer());
$this->addClass('solr', new solr($this)); 


echo "routeParam<pre>".print_r($this->routeParam,1)."</pre>";
#echo "GET<pre>".print_r($this->POST,1)."</pre>";

$currentFacet = $this->routeParam[0];
$yearStart = $this->routeParam[1];
$yearStop = $this->routeParam[2];
$baseCondition = base64_decode($this->routeParam[3]);
	
################################################################################
##				Wyświetlanie
################################################################################	
$usedFacets[] = $baseCondition;
$usedFacets[] = $currentFacet.':['.$yearStart.' TO '.$yearStop.']';
$facetsCode = $this->buffer->createFacetsCode($usedFacets);	

$redirectLink = $this->buildUri('results', ['core'=>'biblio', 'facetsCode'=>$facetsCode]);

echo '	<div class="text-center" style="padding-bottom:15px; margin-top:-20px; padding-top:-20px;">
			<button type=button class="btn btn-default disabled" >'.$this->transEsc('Redirecting...').'</button>
		</div>
	';


#echo "<pre> window.location.assign('$redirectLink');</pre>";
echo "<script> window.location.assign('$redirectLink');</script>";
	
