<?php 
if (empty($this)) die;
$Trole = [];
$this->addClass('buffer', 	new marcBuffer()); 
$this->addClass('solr', 	new solr($this->config)); 

$maks = $this->routeParam[0];
$facet_code = $this->routeParam[1];
$page = floatval($this->routeParam[2]);
$next = $page+1;


$limit = 100;


$query[] = $W = $this->buffer->getFacets( $this->routeParam[1]);	
$query[]=[ 
		'field' => 'q',
		'value' => '*:*'
		];
$query[]=[ 
		'field' => 'facet',
		'value' => 'false'
		];
$query[]=[ 
		'field' => 'rows',
		'value' => $limit
		];
$query[]=[ 
		'field' => 'facet.limit',
		'value' => '2'
		];		
$query[]=[ 
		'field' => 'start',
		'value' => $page*$limit
		];		


$tmp = explode(' ',str_replace('(author_facet_s:"','',$W['value']));
$a_name=$tmp[0].' '.$tmp[1];

#echo "<pre>".print_R($query,1)."</pre>";;


	
$results = $this->solr->getQuery('biblio',$query); 
$results = $this->solr->resultsList();

if (!empty($_SESSION['tr']))
	$Trole=$_SESSION['tr'];
if (!empty($_SESSION['tid']))
	$Tid = $_SESSION['tid'];



foreach ($results as $result) {
	$marcJson = $this->buffer->getJsonRecord($result->id, $result->fullrecord);
	$this->addClass('marc', new marc21($marcJson));
	$desc = $this->marc->getMainAuthor();
	if (stristr($desc['name'],$a_name) && !empty($desc['role'])) {
		$role = $desc['role'];
		if (is_array($role)) {
			foreach ($role as $rola) {
				$rola = $this->transEsc($rola);
				if (empty($Trole[$rola]))
					$Trole[$rola] = 1;
					else 
					$Trole[$rola]++;
				}
			} else {
			$role = $this->transEsc($role);
			if (empty($Trole[$role]))
				$Trole[$role] = 1;
				else 
				$Trole[$role]++;
			}
		}
		
	
	$alist = $this->marc->getOtherAuthorsData();
	if (is_Array($alist))
		foreach ($alist as $k=>$desc) {
			if (stristr($desc['name'],$a_name) && !empty($desc['role'])) {
				$role = $desc['role'];
				if (is_array($role)) {
					foreach ($role as $rola) {
						$rola = $this->transEsc($rola);
						if (empty($Trole[$rola])) {
							$Trole[$rola] = 1;
							$Tid[$rola][$result->id]=$result->title;
							} else {
							$Trole[$rola]++;
							$Tid[$rola][$result->id]=$result->title;
							}
						}
					} else {
					$role = $this->transEsc($role);
					if (empty($Trole[$role])) {
						$Trole[$role] = 1;
						$Tid[$role][$result->id]=$result->title;
						} else {
						$Trole[$role]++;
						$Tid[$role][$result->id]=$result->title;
						}
					}
				}
			}
	
	}
	
$_SESSION['tr'] = $Trole;	
if (!empty($Tid))
	$_SESSION['tid'] = $Tid;	


arsort($Trole);

	
	

if (is_array($Trole)) {
	echo "<div class='stat-row'>";
	echo $this->transEsc('Other creative roles').':<br/>';
	echo "<ol>";
	foreach ($Trole as $k=>$v) {
		# echo "<li>".$this->transEsc($k)." <span class='badge'>$v</span></li>";
		if (!empty($Tid[$k]))
			echo TroleMenu($this->transEsc($k), $v, $this->buildUri('search/record'), $Tid[$k]);
			else 
			echo TroleMenu($this->transEsc($k), $v, $this->buildUri('search/record'), []);	
		}
	echo "</ol>";
	echo "</div>";
	} 

if ($maks >= $next*$limit) {
	
	echo "<div class='stat-row'>";
	echo $this->transESC('Searching for other roles (as translator, as illustrator, etc)').'...';
	echo $this->helper->progressThin($page*$limit, $maks);
	echo "</div>";
	
	$this->JS[] = "page.ajax('otherRolesBox','persons/person.roles.stat/{$maks}/{$facet_code}/$next');";
	}  

# echo "<pre>".print_R($Tid,1)."</pre>";



function TroleMenu($title, $count, $link, $arr) {
	$link = str_replace('?', '', $link);
	if (is_array($arr)) {
		$sub ="<ul class='author-roles-submenu'>";
		foreach ($arr as $k=>$v) {
			$sub.="<li class='author-role-submenu-item'><a href='$link/$k.html' target=_blank>$v</a></li>";
			}
		$sub .="</ul>";
			
		}
	
	return "<li class='author-role-list'>$title <span class='badge'>$count</span>$sub</li>";
	}

?>