<?php 
$limit = 50;


$choosen = '';	
$ch_array = [];
$lp = 0;
$facet = [];


if (!empty($_SESSION['facets_chosen'][$currFacet])) 
	if (count($_SESSION['facets_chosen'][$currFacet])>0) {
		$choosen = "<hr/>
			<form method=GET>
			".$this->transEsc('Choosen facets').':<br/>';
		if (!empty($this->buffer->usedFacetsStr) && is_array($this->buffer->usedFacetsStr)) 
			$facet = $this->buffer->usedFacetsStr;	
			
		foreach ($_SESSION['facets_chosen'][$currFacet] as $k=>$v) {
			
			$tk = $this->helper->convertC($core, $currFacet, $k);
			
			$lp++;
			$input_value='~'.$currFacet.':"'.$k.'"';
			$input_key=$currFacet.':"'.$k.'"';
			$choosen .= "<a id='btn_{$lp}' class='btn btn-choosen' OnClick=\"facets.cores.AddRemove('remove','$k','$lp')\">$tk <span class='fa fa-trash'></span></a>";
			$choosen .= '<input type="hidden" name="facet[]" value="'.$input_value.'">';
			$facet[$input_key] = $input_value;
			}
		
		if (!empty($facet) && !empty($this->buffer->usedFacetsStr))
			$facet = array_merge($facet, $this->buffer->usedFacetsStr);
		$key = $this->buffer->createFacetsCode($facet);
		
		$ch_array = $_SESSION['facets_chosen'][$currFacet];
		
		if (!empty($this->GET['remove']))
			unset($this->GET['remove']);
		if (!empty($this->GET['add']))
			unset($this->GET['add']);
		if (!empty($this->GET['q']))
			unset($this->GET['q']);
		
		$this->facetsCode = $key;
		$choosen .='<div class="text-right">';
		$choosen .='<a href="'.$this->buildUri('results', ['core'=>$core, 'facetsCode'=>$this->facetsCode]).'" class="btn btn-success"><i class="fa fa-check"></i> '.$this->transEsc('Use choosen').'</a>';
		#$choosen .="<button type=submit class='btn btn-success'><i class='fa fa-check'></i> ".$this->transEsc('Use choosen').'</button>';
		$choosen .='</div>';
		$choosen .="</form>";
		} 


			
echo $choosen;

#if (!empty($key)) echo $key.'<br/>';
?>

