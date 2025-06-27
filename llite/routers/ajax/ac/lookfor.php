<?php 
if (empty($this)) die;
$score = $this->routeParam[0] ?? '';

# echo $this->helper->pre($_POST);
$sstring = $this->POST['lookfor'] ?? null;


$this->addClass('solr', new solr($this)); 

$facet = new stdclass;

switch ($score) {
	case 'author' : 
	case 'persons' : 
			$core = 'persons';
			$facet->solr_index = 'biblio_labels';
			
			$query[] = 		[ 'field' => 'q.op',	'value' => 'OR' ];
			$query[] = 		[ 'field' => 'indent',	'value' => 'true' ];
			$query[] = 		[ 'field' => 'rows',	'value' => 8 ];
			$query['q'] = 	[ 'field' => 'q',		'value' => $sstring ];
			$query['fl'] = 	[ 'field' => 'fl', 		'value' => 'labels' ];
			$query['so'] =	[ 'field' => 'sort', 	'value' => 'total_count desc' ];
			
			$resfile = $this->solr->querySelect($core, $query); 
			
			#echo $this->helper->pre($resfile);
			
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
						$lines[] = '<a OnClick="document.getElementById(\'ac_input_'.$score.'\').value=\''.$tname.'\'; " class="ac-item" >'.$tname.'</a>';
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
		echo $this->transEsc('Unknown search core').' '.$core;
	}
	
?>	