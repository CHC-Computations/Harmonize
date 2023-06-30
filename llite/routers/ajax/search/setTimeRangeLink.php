<?php 
if (empty($this)) die;
$this->addClass('buffer', 	new marcBuffer());
$this->addClass('solr', new solr($this->config)); 


#echo "routeParam<pre>".print_r($this->routeParam,1)."</pre>";
#echo "GET<pre>".print_r($this->GET,1)."</pre>";


# $this->addJS('$("#recalculateLink").css("opacity","1"); ');
$this->facetsCode = $this->routeParam[1];
$sort = $this->routeParam[0];

if (is_array($this->buffer->getFacets($this->sql, $this->facetsCode))) {
	$usedFacets = $this->buffer->usedFacetsStr;
	}

if (!empty($this->GET['change'])) {
	$tmp = explode(',', $this->GET['change']);
	$currentFacet = $tmp[0];
	$yearStart = $tmp[1];
	$yearStop = $tmp[2];
	$usedFacets[] = $currentFacet.':['.$yearStart.' TO '.$yearStop.']';
	$this->facetsCode = $this->buffer->createFacetsCode(
				$this->sql, 
				$usedFacets
				);
	
	unset($this->GET['change']);
	}


################################################################################
##				Wyświetlanie
################################################################################	
	

$redirectLink = $this->buildUri('search/results',['page'=>'1']);

echo '	<div class="text-center" style="padding-bottom:15px; margin-top:-20px; padding-top:-20px;">
			<button type=button class="btn btn-default disabled" >'.$this->transEsc('Redirecting...').'</button>
		</div>
	';
echo "
	<script>
		 window.location.assign('$redirectLink');
	</script>
	";
	
