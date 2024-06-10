<?php
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.buffer.php');

$this->addClass('buffer', 	new buffer()); 
$this->addClass('helper', 	new helper()); 

$id = $this->routeParam[0];


$xmlf = $this->buffer->loadFromViaf($id);

#echo "Id: $id<br>";

$xml = $this->buffer->xml2array($xmlf);




$tmp = explode('(WKP)',$xmlf);
if (count($tmp)>1) {
	$tmp = explode('<',$tmp[1]);
	$wikiId = trim(current($tmp));
	
	echo '<div id="wikiBox">'.$this->helper->loader( $this->transEsc('getting information from').' wikidata').'</div>';
	$JS = "page.ajax('wikiBox','load.from.wiki/$wikiId/$id');";
	} else {
	echo "No Wiki Id :-(";
	
	# echo "<pre>".print_R($this->GET,1)."</pre>";
	# echo "<pre>".print_R($this->routeParam,1)."</pre>";
	$JS = '
		var k = $("#hf_request_uri").val();
		let mk = k.replace("/viaf_id'.$id.'","");
		window.location.assign(mk);
		';
	}

/*
echo "<textarea style='width:100%'>$xmlf</textarea>";
echo $this->helper->panelCollapse(
		uniqid(), 
		"marc21 from viaf", 
		"<pre style='background-color:#fff; border:0px;' id='viaf-viewer'>".print_r($xml,1).'</pre>', 
		"<a href='http://viaf.org/viaf/$id'>See more on VIAF web page</a>", 
		false
		);
$JS .= "var input = ".json_encode($xml).";
		$('#viaf-viewer').jsonViewer(input, {collapsed: true, rootCollapsable: false});";		

*/		
echo "<script>$JS</script>";
echo "<br/> ";
				


// https://www.wikidata.org/wiki/Special:EntityData/$wikiId.json
			
?>