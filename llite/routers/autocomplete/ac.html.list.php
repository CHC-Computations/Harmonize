<?php 
if (empty($this)) die;

$sstring = $this->POST['sstring'] ?? null;
$core 	 = $this->POST['score']	?? null;
$sfield  = $this->POST['sfield'] ?? null;
# echo $this->helper->pre($_POST);

$this->addClass('solr', new solr($this)); 
#$this->addClass('buffer', 	new buffer()); 

#$query[] = $uf = $this->buffer->getFacets( $this->facetsCode);	
$facet = new stdclass;

$lines = [];
#echo $sfield.":";
if (!empty($sstring) && ($sstring<>'')) {
	switch ($core) {
		case 'biblio' :
			if (!empty($sfield)) {
				if ($sfield == 'author')
					$facet->solr_index = 'author';
					else 
					$facet->solr_index = $sfield.'_ac';
				} else 
				$facet->solr_index = 'biblio_labels';
			
			
			$query['limit'] = ['field' => 'facet.limit', 'value' => 8 ];
			
			$query[] = [ 'field' => 'q.op',	'value' => 'OR' ];
			$query[] = [ 'field' => 'indent',	'value' => 'true' ];
			$query[] = [ 'field' => 'rows',	'value' => 0 ];
			$query['q'] = [ 'field' => 'q',	'value' => '*:*' ];
			$query['fci'] = [ 'field' => 'facet.contains.ignoreCase',	'value' => 'true' ];
			$query['fc'] = [ 'field' => 'facet.contains',	'value' => trim($sstring) ];
			
			#$query[] =  $this->solr->facetsCountCode($facet->solr_index);
			$results = $this->solr->getFacets($core, [$facet->solr_index], $query);
			
			#echo $this->helper->pre($results);
			
			if (is_Array($results)) {
				$lines = [];
				if (!empty($results[$facet->solr_index])) {
					foreach ($results[$facet->solr_index] as $name=>$count) {
						if ($count>0) {
							$tname = $name;
							if ($sfield == 'author') {
								$tname = explode('|',$name)[0];
								}
							$lines[] = '<a OnClick="document.getElementById(\'searchForm_lookfor\').value=\''.$tname.'\'; $(\'#searchForm\').submit();" class="ac-item" >'.$tname.'</a>';
										
							}
						}
					
					}
				if (count($lines)>=1) {
					echo '<div id="acItemsList">'.implode('', $lines).'</div>';
					} else {
					echo '<span class="ac-header-big">'.$this->transEsc('No hint').'</span>';
					}
				}
			break;
		default: 	
			
			$queryTable = explode(' ',$sstring);
			$queryString = implode('* AND ', $queryTable).'*';
			
			
			$facet->solr_index = 'biblio_labels';
			
			$query[] = 		[ 'field' => 'q.op',	'value' => 'OR' ];
			$query[] = 		[ 'field' => 'indent',	'value' => 'true' ];
			$query[] = 		[ 'field' => 'rows',	'value' => 8 ];
			$query['q'] = 	[ 'field' => 'q',		'value' => $queryString ];
			$query['fl'] = 	[ 'field' => 'fl', 		'value' => 'labels,biblio_count' ];
			$query['so'] =	[ 'field' => 'sort', 	'value' => 'biblio_count desc' ];
			
			$resfile = $this->solr->querySelect($core, $query); 
			
			# echo $this->helper->pre($resfile);
			
			if (!empty($resfile->response->docs) && is_array($resfile->response->docs)) {
				$lines = [];
				foreach ($resfile->response->docs as $result) {
					$res = json_decode($result->labels);
					$tname = null;
					#echo $this->helper->pre($res);
					if (!empty($res->{$this->userLang})) {
						$tname = $res->{$this->userLang};
						} elseif (!empty($res->en)) {
						$tname = $res->en;
						} else 
						$tname = current((array)$res);
						
					if (!empty($tname)) {
						$lines[] = '<a OnClick="document.getElementById(\'searchForm_lookfor\').value=\''.$tname.'\'; $(\'#searchForm\').submit();" class="ac-item" >'.$tname.'</a>';
						}
					}
					
				if (count($lines)>=1) {
					echo '<div id="acItemsList">'.implode('', $lines).'</div>';
					} else {
					echo '<span class="ac-header-big">'.$this->transEsc('No hint').'</span>';
					}
				#echo $this->helper->pre($query);	
				}
			break;
		}

	} else {
	$t = $this->psql->querySelect("SELECT * FROM searchstrings WHERE core={$this->psql->isNull($core)} ORDER BY counter DESC LIMIT 7;");
	if (is_array($t)){
		foreach ($t as $row) {
			
			$tname = $row['string'];
			$lines[] = '<a OnClick="document.getElementById(\'searchForm_lookfor\').value=\''.$tname.'\'; $(\'#searchForm\').submit();" class="ac-item" >'.$tname.'</a>'; //<i class="ph-bold ph-clock-counter-clockwise"></i> 
			}
		}
	if (count($lines)>=1) {
		echo '<span class="ac-header">'.$this->transEsc('Most frequently searched for').':</span>';
		echo '<div id="acItemsList">'.implode('', $lines).'</div>';
		} else {
		echo '<span class="ac-header-big">'.$this->transEsc('Start typing to see hints').'</span>';	
		}	
		
	}


?>
<script>
$("#searchInput-ac").removeClass("inprogress");
</script>