<?php

############################################################
##	Marcin Giersz, ostatnia modyfikacja: 2014-07-22
##	Współpracuje z:
############################################################


class lists {
	
	public $MAX = 50;
	public $startPoint = 0;
	public $showSubPages = 4;
	public $dataColumns = array();
	public $controlFields;
	public $sorting;
	public $onclick = '';
	public $onmouseover = '';
	public $post = '';
	public $cms;
	
	
	
	public function __construct() {
		$this->controlFields = (object)['currentPage'=>0, 'sorting'=>'', 'sortingdesc'=>''];
		foreach ($this->controlFields as $k=>$v) {
			if (!empty($_SESSION[$k])) {
				$this->controlFields->$k = $_SESSION[$k];
				}
			if (!empty($_GET[$k])) {
				$this->controlFields->$k = $_GET[$k];
				}
			if (!empty($_POST[$k])) {
				$this->controlFields->$k = $_POST[$k];
				}
			
			}
		}
	
	public function register($key, $value) {
		@$this->$key = $value;
		}
	
	public function setMax($max = 50) {
		$this->MAX = $max;
		return $this->MAX;
		}

	public function saveConditions($blockName, $conditions) {
		$_SESSION['conditions'][$blockName] = $conditions;
		}

	public function getConditions($blockName) {
		return $_SESSION['conditions'][$blockName] ?? [];
		}


	public function subPages($items) {
		$content = '';
		if ($items>0){
			
			$sort = $this->controlFields->sorting;
			$sortdesc = $this->controlFields->sortingdesc;
			
			$last = $items % ($this->MAX);
			$pages = round((($items - $last)/$this->MAX),0)-1; //,PHP_ROUND_HALF_DOWN
			if ($last > 0) $pages++;
			if ($pages == 1) {
				$content = "<div class='pagination'>{$this->cms->transEsc('All items')}: ".$this->cms->helper->numberFormat($items)."</div>";
				} else {
				if (!empty($this->controlFields->currentPage))
					$currentPage = $this->controlFields->currentPage;
					else 
					$currentPage = 0;
				
				if ($currentPage > $pages) $currentPage = $pages;
				if ($currentPage < $this->showSubPages) { 
					$pagesMin = 0;         
					$pagesMax = 2*$this->showSubPages; 
					} else if ($currentPage<$pages-$this->showSubPages) { 
						$pagesMin = $currentPage-$this->showSubPages;   
						$pagesMax = $currentPage+$this->showSubPages;
						} else { 
						$pagesMin = $pages-2*$this->showSubPages; 
						$pagesMax = $pages;
						}
				if ($pagesMin<0) $pagesMin = 0;
				if ($pagesMax>$pages) $pagesMax = $pages;
		  
				$next = $currentPage+1;
				$prev = $currentPage-1;
				if ($prev==-1)
					$prev = 0;
				$content = "
					<ul class='pagination'>
						<li class='disabled'><a>{$this->cms->transEsc('Pages')}: <b>".$this->cms->helper->numberFormat($pages)."</b>, {$this->cms->transEsc('Items')}: <b>".$this->cms->helper->numberFormat($items)."</b></a></li>
						<li><a type=button OnClick=\"page.results('0','$sort','$sortdesc');\" title='{$this->cms->transEsc('First page')}' style='cursor:pointer;'><span class='glyphicon glyphicon-fast-backward'></span><span class='sr-only'>{$this->cms->transEsc('First page')}</span></a></li>
						<li><a type=button OnClick=\"page.results('$prev','$sort','$sortdesc');\" title='{$this->cms->transEsc('Previous page')}' style='cursor:pointer;'><span class='glyphicon glyphicon-chevron-left'></span><span class='sr-only'>{$this->cms->transEsc('Previous page')}</span></a></li>
						<li><a type=button OnClick=\"page.results('$next','$sort','$sortdesc');\" title='{$this->cms->transEsc('Next page')}' style='cursor:pointer;'><span class='glyphicon glyphicon-chevron-right'></span><span class='sr-only'>{$this->cms->transEsc('Next page')}</span></a></li>
						<li><a type=button OnClick=\"page.results('$pages','$sort','$sortdesc');\" title='{$this->cms->transEsc('Last page')} - $pages' style='cursor:pointer;'><span class='glyphicon glyphicon-fast-forward'></span><span class='sr-only'>{$this->cms->transEsc('Last page')}</span></a></li>
					</ul> 
					<ul class='pagination' role='group' aria-label='{$this->cms->transEsc('Selected pages')}'>
					";
				for ( $x = $pagesMin; $x <= $pagesMax; $x++ ) {
					$px = $x+1;
					if ($currentPage==$x) 
						$content.="<li class=\"active\"><a type=button >$px</a></li>"; 
						else 
						$content.="<li><a type=button OnClick=\"page.results('$x','$sort','$sortdesc');\" style='cursor:pointer;'>$px</a>";
					} 
				$this->startPoint = $currentPage*$this->MAX;
				$pages++;
				$content.=" 
					</ul>
					<br/>
					";
				}
			}
		return $content;
		}
		
	public function headers() {
		$tableHeaders = $dir = $addClass = '';
		foreach ($this->dataColumns as $rowNo => $tab) {
			if (array_key_exists('field',$tab)) {
				if (!empty($tab['orderField']) && ($this->controlFields->sorting == $tab['orderField'])) {
					
					if (!empty($this->controlFields->sortingdesc)) {
						$styl="style='background-color:#aca; cursor:pointer;'";
						$arr = ' <i class="ph ph-arrow-up"></i>';
						$tmp=explode(',',$tab['orderField']);
						foreach ($tmp as $order)
							$Torder[]="$order DESC";
						$this->sorting = implode(', ',$Torder);
						$dir='';
						} else {
						$styl="style='background-color:#aca; cursor:pointer;'";
						$arr = ' <i class="ph ph-arrow-down"></i>';
						$dir='desc';
						$this->sorting = "$tab[orderField]";
						}
					$addClass = 'class="active"';
					} else {
					$addClass = '';
					$arr="";
					$dir='';
					}
				if (array_key_exists('orderField',$tab)) {
					$OCA = " OnClick=\"page.results('{$this->controlFields->currentPage}','$tab[orderField]','$dir');\" ";
					$style = "style='cursor:pointer;'";
					} else {
					$OCA = '';
					$style = '';
					}
					
				$tableHeaders.="<td $addClass $style $OCA>$tab[title]$arr</td>  ";
				} else 
				$tableHeaders.="<td>$tab[title]</td>  ";				
			}
		$tableHeaders = "<thead><tr>$tableHeaders</tr></thead>";
		return $tableHeaders;
		}	
	 
	public function content($table) {
		$content = '';
		$fo=$fc='';
		$lp=0;
		
		if (is_Array($table))
			foreach ($table as $row) {
				$key = '';
				if (!empty($this->onclick)) {
					$key = $this->onclick;
					$fc="OnClick={$this->onclick}('$row[$key]');";
					}
				if (!empty($this->onmouseover)) {
					$key = $this->onmouseover;
					$fo="OnMouseOver={$this->onmouseover}('$row[$key]');";
					}
				if (isset($row[$key])and($row[$key]<>''))
					$row_id="row_$row[$key]";
					else
					$row_id="row_$lp";
											
				if (!array_key_exists('class',$row))
					$row['class']='';
				$content.="<tr $fc $fo id='$row_id' $row[class]>";
				foreach ($this->dataColumns as $tab) {
					if (strtolower($tab['class'])=='boolean') {
						switch ($row[$tab[pole]]) {
							case 't': $row[$tab[pole]]='<span style="color:green;"><span class="glyphicon glyphicon-ok"></span> Tak</span>'; break;
							case 'f': $row[$tab[pole]]='<span style="color:#888;"><span class="glyphicon glyphicon-remove"></span>  Nie</span>'; break;
							case '' : $row[$tab[pole]]='<span style="color:#555;"><span class="glyphicon glyphicon-minus"></span> nie określono</span>'; break;
						}
						$tab['class']='boolean center';
					}
					$content.="<td class='$tab[class]'>";
					if (array_key_exists('function',$tab))
						switch ($tab['function']) {
							case 'counter': 
								$lp++;
								$content .= $this->startPoint + $lp.'.';
								break;
							}
					if (array_key_exists('field',$tab))
						if (array_key_exists($tab['field'],$row))
							$content.=$row[$tab['field']];						
					$content.="</td>";
					}
				$content.="</tr>";
				}
		return $content;
		}
	
	
	
	
	
	public function raportMenu($t, $field = '', $fieldName='') {
		$link = '';
		$content = '';
		$active = '';
		
		if (!empty($this->cms->GET[$field]))
			$active = ' active';
			
		if (is_array($t))
			foreach ($t as $row) {
				if ($row[$field]=='')
					$row[$field]='NULL';
				if (!empty($this->cms->GET[$field]) && ($row[$field]==$this->cms->GET[$field]))
					$content.='<li><a href="?'.$link.'"><span>'.$row[$field].' </span><span ><b>'.$row['item_count'].'</b></span><span><i class="ph ph-x"></i></span></a></li>';
					else 
					$content.="<li><a href='?$link&amp;$field=".urlencode($row[$field])."'><span>$row[$field] </span><span ><b>".$this->cms->helper->numberFormat($row['item_count'])."</b></span></a></li>";
			}
		return '<li class="dropdown'.$active.'">
				  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$fieldName.' <span class="caret"></span></a>
				  <ul class="dropdown-menu elb-dropdown">
					'.$content.'
				  </ul>
				</li>';	
		}


	public function graphMenu($t, $field = '', $fieldName='') {
		$link = '';
		$content = '';
		$active = '';
		if (!empty($this->cms->GET[$field]))
			$active = ' active';
		if (is_array($t)) {
			$max = 0;
			foreach ($t as $k=>$row) {
				if ($row[$field]=='')
					$row[$field]='NULL';
				if (($row[$field]==0)or($row[$field]==100) or !empty($active)) {
					if (!empty($this->cms->GET[$field]) && ($row[$field]==$this->cms->GET[$field]))
						$content.='<li><a href="?'.$link.'"><span>'.$row[$field].' </span><span ><b>'.$row['item_count'].'</b></span><span><i class="ph ph-x"></i></span></a></li>';
						else 
						$content.="<li><a href='?$link&amp;$field=".urlencode($row[$field])."'><span>$row[$field]% </span><span ><b>".$this->cms->helper->numberFormat($row['item_count'])."</b></span></a></li>";
					unset($t[$k]);
					} else if ($max < $row['item_count'])
					$max = $row['item_count'];
				}
			if (empty($active)) {
				$content .= '<li class="elb-graph">';	
				foreach ($t as $row) {
					if ($row[$field]=='')
						$row[$field]='NULL';
					$height = round(($row['item_count']/$max)*200);
					$content.='
							<a href="?'.$link.'&amp;'.$field.'='.urlencode($row[$field]).'" data-toggle="tooltip" title="'.$row[$field].'% '.$this->cms->helper->numberFormat($row['item_count']).'"
								onMouseOver = "$(\'#graphcurrent\').html(\''.$row[$field].'% '.$this->cms->helper->numberFormat($row['item_count']).'\')"
								onMouseOut = "$(\'#graphcurrent\').html(\' \')"
								>
								<span class="elb-graph-bar" style="height:'.$height.'px"></span>
								<span class="elb-graph-label">'.$row[$field].'% '.$this->cms->helper->numberFormat($row['item_count']).'</span>
							</a>
							';
					}
				$content .= '</li>
					<li>
						<a><span id="graphcurrent"> </span></a>
					</li>
					';	
				}
			}
			
			
		return '<li class="dropdown'.$active.'">
				  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$fieldName.' <span class="caret"></span></a>
				  <ul class="dropdown-menu elb-dropdown">
					'.$content.'
				  </ul>
				</li>';
		}
	
	


	}

?>