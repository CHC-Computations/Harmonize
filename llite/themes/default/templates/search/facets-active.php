<?php 
$currentCore = 'biblio';
$key ='';
$active = '';

foreach ($activeFacets as $groupCode=>$arr) {
	
	$active .= '<div style="padding:5px;">'.$this->transEsc( $this->configJson->$currentCore->facets->solrIndexes->$groupCode->name ).':</div>';
	$lp=0;
	foreach ($arr as $k=>$v) {
		$lp++;
		
		$value = str_replace('"', '', $v['value']);

		/*
		if (stristr($value,' OR ')) {
			$tmpV = explode(' OR ', $value);
			$i=0;
			foreach ($tmpV as $tmpK=>$tmpNV) {
				$i++;
				if ($i==1) {
					$nstr = $this->helper->authorFormat($tmpNV);
					$nstr .= '<br/> '.$this->transEsc('OR').'<br/>';
					}
				if ($i>1) {
					$tmp = explode(':', $tmpNV);
					
					$nstr.= str_replace($tmp[0].':', '', $tmpNV);
					$nstr.= '('.$this->transEsc('as').' '.$this->transEsc( $this->helper->inArray($tmp[0], $facets['facetList'])).')';
					
					}
				}
			$value =	 $nstr;
			}
		*/
		
		$tvalue = $this->helper->convert($groupCode, $value);
		$key = $this->buffer->createFacetsCode($this->buffer->removeFacet($groupCode, $value));
		
		$active .= '<a href="'.$this->buildUri('results', ['core'=>'biblio', 'page'=>1, 'facetsCode'=>$key]).'" class="facet">
				<span class="text" style="padding-left:1.5rem;">'.( (($v['operator']=='or')&($lp>1)) ?  $this->transEsc('or').' ' : '' ).$tvalue.'</span>
				<i class="right-icon fa fa-remove"></i>
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