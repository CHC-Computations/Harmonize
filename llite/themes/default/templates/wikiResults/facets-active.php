<?php 
$key ='';
$active = '';

foreach ($activeFacets as $groupCode=>$arr) {
	$translated = false;
	
	$name = $this->configJson->$currentCore->facets->facetsMenu->$groupCode->name ?? '';
	
	$active .= '<div style="padding:5px;">'.$this->transEsc( $name ).':</div>';
	$lp=0;
	foreach ($arr as $k=>$v) {
		$lp++;
		
		$value = str_replace('"', '', $v['value']);
		$tvalue = $this->helper->convert($groupCode, $value);
		
		if (!empty($this->configJson->$currentCore->facets->facetsMenu->$groupCode->formatter)) $tvalue = $this->helper->{$this->configJson->$currentCore->facets->facetsMenu->$groupCode->formatter}($value);
		if ($this->configJson->$currentCore->facets->facetsMenu->$groupCode->translated) $tvalue = $this->transEsc($value);
		$key = $this->buffer->createFacetsCode(
				$this->buffer->removeFacet($groupCode, $value)
				);
		
		$active .= '<a href="'.$this->buildUri('results', ['core'=>$currentCore, 'facetsCode'=>$key], true).'" class="facet">
				<span class="text" style="padding-left:1.5rem;">'.( (($v['operator']=='or')&($lp>1)) ?  $this->transEsc('or').' ' : '' ).$tvalue.'</span>
				<i class="right-icon ph ph-x"></i>
				</a>';
		}
	}

?>
<div class="panel panel-primary">
	<div class="panel-heading"><?= $this->transEsc('Active filters') ?></div>
	<div class="panel-body">
		<?= $active ?>
	</div>
</div>



<?php 

# echo "Tfq<pre>".print_r($this->buffer->Tfq,1).'</pre>';
# echo "Top<pre>".print_r($this->buffer->Top,1).'</pre>';
# echo "facets<pre>".print_r($facets,1).'</pre>';

?>