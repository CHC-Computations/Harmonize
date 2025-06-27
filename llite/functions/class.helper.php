<?PHP


class helper {
	
	protected $cms;
	
	public $lastId;
	public $facets;
	public $useFileSizeFormat = false;
	
	private $wikiLabels;
	
	public function register($key, $value) {
		$this->$key = $value;
		}
		
	public function Alert($klasa,$tresc) {
		return "
			<div class='alert alert-$klasa alert-dismissible' role='alert'><button type='button' class='close' data-dismiss='alert'><span aria-hidden='true'>&times;</span><span class='sr-only'>Zamknij</span></button>
			$tresc
			</div>
			";
		}
	
	public function cloudMessage($class,$mesage) {
		return "
			<div class='alert alert-cloud alert-$class alert-dismissible' role='alert'><button type='button' class='close' data-dismiss='alert'><span aria-hidden='true'>&times;</span><span class='sr-only'>Zamknij</span></button>
			$mesage
			</div>
			";
		}
	
	public function alertIco($klasa,$glyphicon,$tresc=null) {
		return $this->Alert($klasa,"
						<div class=row>
							<div class='col-sm-2 text-center'><span class='$glyphicon' style='font-size:3em; padding:15px;'></span></div>
							<div class=col-sm-9>
							$tresc
							</div>
						</div>");
		}
	
	
	public function Modal() {
		return "<!-- Modal -->
		<div id='myModal' class='modal fade' role='dialog'>
		  <div class='modal-dialog modal-lg'>

			<!-- Modal content-->
			<div class='modal-content' >
			  <div class='modal-header'>
				<button type='button' class='close' data-dismiss='modal'>&times;</button>
				<h4 class='modal-title' id='inModalTitle'></h4>
			  </div>
			  <div class='modal-body' id='inModalBox'>
				<div class='loader'></div>
			  </div>
			  
			</div>

		  </div>
		</div>";
		}
	
	public function pre($var) {
		return '<pre>'.print_r($var,1).'</pre>';
		}
		
	public function preCollapse($name, $var) {
		$id = 'preCollapse'.$name;
		$kl='ph-caret-down-bold';
		$rozwiniety='false';
		$js="$('#{$id}_body').collapse('hide');";
		$in='';
		
		return "
			<div id='{$id}_panel' class='panel panel-default'>
			<div class='panel-body'>
				<div role='tab'>
					<button type='button' data-toggle='collapse' data-target='#{$id}_body'><span class=' $kl' id='{$id}_iko'></span></button> $name
				</div>
				<div id='{$id}_body' class='collapse'>
					<pre style='background-color:transparent; border:0;'>".print_r($var,1)."</pre>	
				</div>
			</div>
			</div>
			<script>
				// $('#{$id}_body').collapse({ toggle: $rozwiniety });
				$('#{$id}_body').on('shown.bs.collapse', function () {
					$('#{$id}_iko').removeClass('ph-caret-down-bold').addClass('ph-caret-up-bold');
					});
				$('#{$id}_body').on('hidden.bs.collapse', function () {
					$('#{$id}_iko').removeClass('ph-caret-up-bold').addClass('ph-caret-down-bold');
					});
				
			</script>
			";
		// $('#{$id}_body').collapse('hide');	
		}	
	
	public function ToolTip($symbol,$tresc,$kolor='') {
		$tresc=str_replace('<br/>',"\n",$tresc);
		$tresc=strip_tags($tresc);
		return "<span style='cursor: help; text-align:left;' data-toggle='tooltip' data-placement='top' title='$tresc'><span class='glyphicon glyphicon-$symbol $kolor'></span></span>";
		}

	public function PopOver($symbol,$naglowek,$tresc,$kolor='') {
		$tresc=str_replace('<br/>',"\n",$tresc);
		$tresc=strip_tags($tresc);
		return "<a style='cursor: help; text-align:left;' data-toggle='popover' data-placement='top' title='$naglowek' data-content='$tresc'><span class='glyphicon glyphicon-$symbol $kolor'></span></a>";
		}
	
	
	public function panelSimple($content, $class='default', $addOns='') { 
		return '<div class="panel panel-'.$class.'" '.$addOns.'><div class="panel-body">'.$content.'</div></div>';
		}
	
	Public function PanelCollapse($id, $tytul, $tresc, $stopka='', $rozwiniety='true', $klasa='default') {
		if ($stopka<>'')
			$stopka="<div class='panel-footer'>$stopka</div>";
		if (($rozwiniety=='true')or($rozwiniety===true)) {
			$kl='ph-caret-up-bold';
			$in='in';
			$rozwiniety='true';
			} else {
			$kl='ph-caret-down-bold';
			$rozwiniety='false';
			$js="$('#{$id}_body').collapse('hide');";
			$in='';
			}
		return "
			<div class='panel panel-$klasa' id='{$id}_panel'>
				<div class='panel-heading' role='tab'>
					<button type='button' class='close' data-toggle='collapse' data-target='#{$id}_body'><span class=' $kl' id='{$id}_iko'></span></button> $tytul
				</div>
				<div id='{$id}_body' class='panel-collapse collapse {$in} sidefl'>
					<div class='panel-body'>
						$tresc	
					</div>
					$stopka
				</div>
			</div>
			<script>
				// $('#{$id}_body').collapse({ toggle: $rozwiniety });
				$('#{$id}_body').on('shown.bs.collapse', function () {
					$('#{$id}_iko').removeClass('ph-caret-down-bold').addClass('ph-caret-up-bold');
					});
				$('#{$id}_body').on('hidden.bs.collapse', function () {
					$('#{$id}_iko').removeClass('ph-caret-up-bold').addClass('ph-caret-down-bold');
					});
			</script>
			";
		// $('#{$id}_body').collapse('hide');
		}

	public function tabsCarousel($tabs=array(), $active = '') {
		if (is_array($tabs)) {
			$id = uniqid();
			$result = '<ul class="nav nav-tabs">';
			$car = '<div id="myCarousel'.$id.'" class="carousel slide" data-ride="carousel">
					<div class="carousel-inner">
					';
			$lp = 0;
			foreach ($tabs as $tabcode=>$tab) {
				if ($active==$tabcode) 
					$aclass='active';
					else 
					$aclass='';
				$onClick = "$('.ind_{$id}').removeClass('active'); $('#ind_{$id}_{$lp}').addClass('active'); ";
				if (!empty($tab['onClick']))
					$onClick.= $tab['onClick'];
				
				$result .= '
					<li class="'.$aclass.' ind_'.$id.'" id="ind_'.$id.'_'.$lp.'" data-target="#myCarousel'.$id.'" data-slide-to="'.$lp.'">
						<a href="#'.$tabcode.'" OnClick="'.$onClick.'">'.$tab['label'].'</a>
					</li>';
				$car.='
					<div class="item '.$aclass.'">
					  '.$tab['content'].'
					</div>
					';
				$lp++;
				}
			$car .='</div></div>';
			$result .='</ul>'.$car;
			
			$result .= '
				<script>
					$(document).ready(function(){
						$("#myCarousel'.$id.'").carousel({interval: false});
						});
				</script>
				';
			
			$this->lastId = $id;
			return $result;
			} else 
			return null;
		}
	
	public function setLength($str, $len) {
		$wstr = $str;
		$wlen = @strlen($str);
		
		if ($wlen>$len) {
			$tmp = explode(' ', $str);
			$z = count($tmp);
			for ($i = 0; $i<=$z; $i++) {
				$step = $z - $i;
				unset($tmp[$step]);
				$nstr = implode(' ', $tmp);
				if (strlen($nstr)<$len) {
					$str = $nstr;
					break;
					}
				}
			
			$str.='(...)';
			$str = '<span title="'.strip_tags($wstr).'">'.$str.'</span>';
			return $str;
			}
		return '<span>'.$str.'</span>';
		}
	
	
	
	public function dropDown( $options = [] , $selected = null, $label = null) {
		$opt='';
		foreach ($options as $k=>$v) {
			$value = trim(chop($v['name']));
			if ($v['key'] == $selected) {
				$active = 'class="active"';
				$label.=': <b>'.$v['name'].'</b>';
				} else 
				$active = '';
			if (!empty($v['href']))
				$href = 'href="'.$v['href'].'"';
				else 
				$href = '';
			if (!empty($v['onclick']))
				$onclick = 'onclick="'.$v['onclick'].'"';
				else 
				$onclick = '';
			$opt.="<li $active><a $href $onclick>$value</a>";
			}
		
		$tresc = '';
		$tresc .= '
			<div class="dropdown">
			  <a class="dropdown-toggle" style="cursor:pointer;" data-toggle="dropdown">
				'.$label.'
				<span class="caret"></span>
			  </a>
			  <ul class="dropdown-menu dropdown-menu-right">
				'.$opt.'
			  </ul>
			</div> 
			';
		return $tresc;
		}
		
	public function drawSideMenu($menu) {
		$res = '<div class="panel list-group">';
		foreach ($menu as $row) {
			if (!empty($row['ico']))
				$ico = "<i class=\"$row[ico]\"></i> ";
				else 
				$ico = '';
			if (!empty($row['class']))
				$class = $row['class'];
				else 
				$class = '';
			if (!empty($row['link']))
				$link = "href=\"$row[link]\" ";
				else 
				$link = '';
			if (!empty($row['onclick']))
				$link .= "OnClick=\"$row[onclick]\" ";
				else 
				$link .= '';
			if (!empty($row['id']))
				$link .= "id=\"$row[id]\" ";
				else 
				$link .= '';
			
			if (!empty($row['submenu'])) {
				$idBox = 'collapse'.uniqid();
				$submenu = "<div class=\"sublinks collapse\" id=\"$idBox\">";
				
				foreach ($row['submenu'] as $row2) {
					if (!empty($row2['ico']))
						$ico2 = "<i class=\"$row2[ico]\"></i> ";
						else 
						$ico2 = '';
					if (!empty($row2['link']))
						$link2 = "href=\"$row2[link]\" ";
						else 
						$link2 = '';
					$submenu.='<a '.$link2.' class="list-group-item list-group-item-warning small" style="padding-left:4rem;">'.$ico2.$row2['title'].'</a>';
					}
				
				$submenu.="</div> ";
				$link = "data-toggle=\"collapse\" data-target=\"#$idBox\"";
				} else {
				$submenu = '';
				}
			
			
			$res.='<a class="list-group-item '.$class.'" '.$link.'rel="nofollow" >'.$ico.$row['title'].'</a>'.$submenu;
			}
		$res .= '</div>';
		return $res;
		}
		
	
	public function list($rec, $nr = true) {
		if (count($rec)>1)
			if ($nr)
				return "<ol><li>".implode('</li><li>',$rec)."</li></ol>";
				else 
				return "<ul><li>".implode('</li><li>',$rec)."</li></ul>";	
			else 
			return implode(', ',$rec);
		}	
	
	public function loader($komunikat = null) {
		return "<div class=\"progress\"><div class=\"progress-bar progress-bar-striped active\" role=\"progressbar\" aria-valuenow=\"100\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width:100%\">$komunikat</div></div>";
		}
	
	public function loader2($komunikat = null) {
		return '<div class="text-center"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>';
		}
	
	public function percentBox ($liczba, $maks=100, $color='#eee') {
		if ($liczba!='') {
			$proc=ceil(($liczba/$maks)*100);
			if ($this->useFileSizeFormat)
				$liczbaStr = $this->fileSize($liczba);
				else 
				$liczbaStr = number_format($liczba,0,'','.');
			
			return "
				<div class='procent-box'>
					<span class=overlaygrow style='width:{$proc}%; background-color: {$color};'></span>
					<span class=overlay><span class=liczba>".$liczbaStr."</span></span>
				</div>";
			} else 
			return '---';
		}
	
	public function percent($ile, $suma, $klasa='primary') {
		if ($suma>0) {
			$procent=round(($ile/$suma)*100,2);
			if ($procent>100) 
				$procent=100;
			$sl=round(($ile/$suma)*100,0);
			$slopek="
					<div class='progress'>
					  <div class='progress-bar progress-bar-$klasa' role='progressbar' aria-valuenow='$ile' aria-valuemin='0' aria-valuemax='$suma' style='width:$sl%'>
						$procent% 
					  </div>
					</div> 
					";
			} else 
			$slopek="
					<div class='progress'>
					  <div class='progress-bar progress-bar-$klasa' role='progressbar' aria-valuenow='0' aria-valuemin='0' aria-valuemax='0' style='width:100%'>
						0 
					  </div>
					</div> 
					";	
		return $slopek;		
		}	
		
	public function progress($ile, $suma, $klasa='primary') {
		if ($suma>0) {
			$procent=round(($ile/$suma)*100,2);
			if ($procent>100) 
				$procent=100;
			$sl=round(($ile/$suma)*100,0);
			$slopek="
					<div class='progress'>
					  <div class='progress-bar progress-bar-$klasa' role='progressbar' aria-valuenow='$ile' aria-valuemin='0' aria-valuemax='$suma' style='width:$sl%'>
						$procent% 
					  </div>
					</div> 
					";
			} else 
			$slopek="
					<div class='progress'>
					  <div class='progress-bar progress-bar-$klasa' role='progressbar' aria-valuenow='0' aria-valuemin='0' aria-valuemax='0' style='width:100%'>
						0 
					  </div>
					</div> 
					";	
		return $slopek;		
		}	
		
	public function progressThin($ile, $suma, $klasa='primary') {
		if ($suma>0) {
			$procent=round(($ile/$suma)*100,2);
			if ($procent>100) 
				$procent=100;
			$sl=round(($ile/$suma)*100,0);
			$slopek="
					<div class='progress-thin' title='$ile/$suma'>
					  <div class='progress-bar-thin progress-bar-thin-$klasa' role='progressbar' aria-valuenow='$ile' aria-valuemin='0' aria-valuemax='$suma' style='width:$sl%'>
					  </div>
					</div> 
					";
			return $slopek;		
			}
		return '';
		}
	
	public function progressThinMulti($array) {
		$array = (array)$array;
		$sum = 0;
		foreach ($array as $k=>$v) {
			if (empty($v['count']))
				$array[$k]['count'] = $v['count'] = 0;
			$sum += $v['count'];
			}
		$graph = '<div class="progress-thin-multi">';	
		foreach ($array as $key=>$values) {	
			$currsor = '';
			$title = '';
			$i = $values['count'];
			$percent = round(($i/$sum)*100,2);
			if ($percent > 100) 
				$percent = 100;
			$sl = round(($i/$sum)*100,0);

			if (!empty($values['title'])) 
				$title = "title='$values[title]'";
			if (!empty($values['onClick'])) {
				$title .= "onClick='$values[onClick]'";
				$currsor = '; currsor:pointer';
				}
				
			$graph .= "
				<div class='progress-thin-multi-bar' id='progressPart{$key}' style='background-color:$values[color]; width:$sl% $currsor' role='progressbar' aria-valuenow='$i' aria-valuemin='0' aria-valuemax='$sum' $title>
				</div>
				";
			}
		$graph .='</div>';	
		return $graph;			
		}
	
		
	public function multiPercent($dane=array(), $max=1) {
		$bar = '';
		foreach ($dane as $row) {
			$procent=round(($row['count']/$max)*100,2);
			$bar.="<div class='progress-bar progress-bar-$row[class]' style='width: {$procent}%' title='$row[title]' data-toggle='tooltip'>
					<span class='sr-only'>{$procent}% $row[title]</span>
				</div>";
			}		
					
		$slopek="		
			<div class='progress'>
				$bar
			</div>";
				
		return $slopek;		
		}
	
	public function fileSize($size) {
		
		if ($size>1073741824) {
			$size = number_Format( ($size/1073741824), 1, '.', ' ').'GB';
			return $size;
			}
		if ($size>1048576) {
			$size = number_Format( ($size/1048576), 1, '.', ' ').'MB';
			return $size;
			}
		if ($size>1024) {
			$size = number_Format( ($size/1024), 1, '.', ' ').'kB';
			return $size;
			}
		
		return $size.' b';
		}
		
	function convertToBytes($size) {
		preg_match('/[a-zA-Z]+/', $size, $matches);
		$unit = strtoupper($matches[0][0]);
		$number = floatval($size);

		switch ($unit) {
			case 'T':
				return round($number * pow(1024, 4)); // Terabytes to bytes
			case 'G':
				return round($number * pow(1024, 3)); // Gigabytes to bytes
			case 'M':
				return round($number * pow(1024, 2)); // Megabytes to bytes
			case 'K':
				return round($number * 1024);         // Kilobytes to bytes
			case 'B':
			default:
				return $number;                // Bytes
			}
		}	
		
		
	function getDiscUsage($folder) {
		$result = shell_exec("du -h --max-depth=1 $folder 2>&1");
		$result = explode("\n", $result);
		$values = [];
		foreach ($result as $line) 
			if (!empty($line)) {
				$data = explode("\t", $line);
				$subFolder = str_replace($folder, '', $data[1]);
				if ($subFolder == '') $subFolder = 'total';
				#echo $this->pre($line);
				
				$subValue = $this->convertToBytes($data[0]);
				$values[$subFolder] = $subValue;
				}
		
		return $values;		
		}	
			
		
		
	public function randColor() {
		$r=dechex (rand(150,240));
		$g=dechex (rand(150,240));
		$b=dechex (rand(150,240));
		
		$kolor="#$r$g$b";
		return $kolor;
		}
	
	public function fontColor($rgb) {
		$rgb=str_replace('#','',$rgb);
		if (strlen($rgb)>4) {
			$r=hexdec(substr($rgb,0,2));
			$g=hexdec(substr($rgb,2,2));
			$b=hexdec(substr($rgb,4,2));
			} else {
			$r=hexdec(substr($rgb,0,1));
			$g=hexdec(substr($rgb,1,1));
			$b=hexdec(substr($rgb,2,1));
			} 
				
		$sum=$r+$g+$b;		
		#echo "$rgb, $r, $g, $b, $sum<Br/>";		
		if(($r+$g+$b) > 400) 
			return '#000000'; 
			else 
			return '#fff';
		}

			
	public function removeLastSlash($t1) {
		$t2 = '';
		$t1 = (string)$t1;
		$pos = strrpos($t1,'/');
		
		if (($pos>0)and($pos>=strlen($t1)-3))
			return substr($t1, 0, $pos);
			else 
			return $t1;
		}
		
		
		
	public function drawGooglePie ($title, $arr) {
		$formid = uniqid();
		$sum = 0;
		foreach ($arr as $k=>$v) {
			if ((!stristr($k,'other options'))and($v<>0)) {
				$rows[]="['$k', $v]";
				$sum += $v;
				#echo "$k, $v<br>";
				} else {
				$oth = $v;	
				
				}
			} 
		$count = count($rows);	
		$proc = round( ($sum/(array_sum($arr)))*100 ,1);
		return "
			<div id='$formid'></div>
			<script type=\"text/javascript\">
			// Load google charts
			google.charts.load('current', {'packages':['corechart']});
			google.charts.setOnLoadCallback(drawChart);

			// Draw the chart and set the chart values
			function drawChart() {
			  var data = google.visualization.arrayToDataTable([
			  ['Value', 'counts'],
			  ".implode(",\n", $rows)."
			]);

			  var options = {'width':400, 'height':360, 'legend':'top', };
			  var chart = new google.visualization.PieChart(document.getElementById('$formid'));
			  chart.draw(data, options);
			}
			</script>
			";
		}	
	
	public function convertC($core, $facet, $value) {
		$searchFields = ['solrIndexes', 'facetsMenu'];
		$label = $value;
		foreach ($searchFields as $field) 
			if (!empty($this->cms->configJson->$core->facets->$field->$facet->translated) && ($this->cms->configJson->$core->facets->$field->$facet->translated == 'true')) {
				$label = $this->cms->transEsc($label);
				}
		foreach ($searchFields as $field) 
			if (!empty($this->cms->configJson->$core->facets->$field->$facet->formatter)) {
				$formatter = $this->cms->configJson->$core->facets->$field->$facet->formatter;
				$label = $this->$formatter($label);
				}
		
		return $label;
		}
	
	public function convert($facet, $value) {
		$label = $value;
		if (!empty($this->cms->configJson->biblio->facets->solrIndexes->$facet->translated) && ($this->cms->configJson->biblio->facets->solrIndexes->$facet->translated)) {
			$label = $this->cms->transEsc($label);
			}
		if (!empty($this->cms->configJson->biblio->facets->solrIndexes->$facet->formatter)) {
			$formatter = $this->cms->configJson->biblio->facets->solrIndexes->$facet->formatter;
			$label = $this->$formatter($label);
			}
		
		return $label;
		}
	
	
	public function drawStatBox($title, $data) {
		$lp = 0;
		$maksV = 0;
		foreach ($data as $k=>$arr) {
			if ($maksV<$arr['count'])
				$maksV = $arr['count'];	
			}
		foreach ($data as $k=>$arr) {
			$arr['uid'] = uniqid();
			$lines[] = 
					
					'<tr id="trow_'.$k.'" OnMouseOver="facets.graphActive('.$k.');"  OnMouseOut="facets.graphDisActive('.$k.');" >
						<td>
							<a  href="'.$arr['link'].'"
								data-title="'.$arr['label'].'" 
								data-count="'.$arr['count'].'">
								<span class="text">'.$arr['label'].'</span>
							</a>
						</td>
						<td >'.$this->percentBox($arr['count'],$maksV,$arr['color']).'</td>
					</tr>';
				
				
			}
		if (!empty($lines))
			return "
				<div class='il-panel'>
					<div class='il-panel-header'><h4>$title</h4></div>
					<div class='il-panel-graph'>{$this->drawSVGPie($data, ['width'=>100, 'height' =>100])}</div>
					<div class='il-panel-bottom'><table class='list'><tbody>".implode('',$lines)."</tbody></table></div>
				</div>
				"; 
		}
		
	public function drawStatBoxAdvaced($data, $max = 6) {
		$lp = 0;
		$maksV = 0;
		foreach ($data as $k=>$arr) {
			if ($maksV<$arr['count'])
				$maksV = $arr['count'];	
			}
		foreach ($data as $k=>$arr) {
			$arr['uid'] = uniqid();
			$onMouseMoveActions = '';
			$legend = '';
			if ($lp < $max) {
				$graphData[$k] = $arr;
				$onMouseMoveActions = 'class="onGraph" OnMouseOver="facets.graphActive(\''.$arr['uid'].'\');"  OnMouseOut="facets.graphDisActive(\''.$arr['uid'].'\');"';
				$legend = '<span class="legendBlock" style="background-color: '.$arr['color'].'">&nbsp;</span>';
				}
			$lines[] = 
					
					'<tr id="trow_'.$k.'" '.$onMouseMoveActions.' >
						<td>'.$legend.'</td>
						<td>
							<a  href="'.$arr['link'].'"
								data-title="'.$arr['label'].'" 
								data-count="'.$arr['count'].'">
								<span class="text">'.$arr['label'].'</span>
							</a>
						</td>
						<td >'.$this->percentBox($arr['count'],$maksV,$arr['color']).'</td>
					</tr>';
			$lp++;
			}
			
		if (!empty($lines)) {
			$max = count($lines);
			$uid = uniqid();
			return '
				<div class="col1 il-panel-options"><table class="list"><tbody>'.implode('',$lines).'</tbody></table></div>
				<div class="col2 il-panel-graph">'.$this->drawSVGPie($graphData, ['width'=>180, 'height' =>180]).'</div>
				'; 
			}
		}

	public function drawStatBoxComparison($title, $data, $max=6) {
		$lp = 0;
		$maksV = 0;
		foreach ($data as $k=>$arr) {
			if ($maksV<$arr['count'])
				$maksV = $arr['count'];	
			}
			
		foreach ($data as $k=>$arr) {
			$arr['uid'] = uniqid();
			$onMouseMoveActions = '';
			$legend = '';
			if ($lp < $max) {
				$graphData[$k] = $arr;
				$onMouseMoveActions = 'class="onGraph" OnMouseOver="facets.graphActive(\''.$arr['uid'].'\');"  OnMouseOut="facets.graphDisActive(\''.$arr['uid'].'\');"';
				$legend = '<span class="legendBlock" style="background-color: '.$arr['color'].'">&nbsp;</span>';
				}
			$lp++;
			$lines[] = 
					
					'<tr id="trow_'.$arr['uid'].'" '.$onMouseMoveActions.' >
						<td>'.$legend.'</td>
						<td>
							<a  href="'.$arr['link'].'"
								data-title="'.$arr['label'].'" 
								data-count="'.$arr['count'].'">
								<span class="text">'.$arr['label'].'</span>
							</a>
						</td>
						<td >'.$this->percentBox($arr['count'],$maksV,$arr['color']).'</td>
					</tr>';
				
				
			}
			
		if (!empty($lines)) {
			$max = count($lines);
			$uid = uniqid();
			return '
				<div class="il-panel">
					<div class="il-panel-header">
						<span>'.$this->cms->transEsc('options avaible').': <strong>'.$max.'</strong></span>
					</div>
					<div class="il-panel-graph">'.$this->drawSVGPie($graphData, ['width'=>180, 'height' =>180]).'</div>
					<div class="il-panel-bottom"><table class="list"><tbody>'.implode('',$lines).'</tbody></table></div>
				</div>
				'; 
			}
		}

	
	public function drawSVGPie($pie=[], $options=[]) {
		$width = $options['width'] ?? '200';
		$height = $options['height'] ?? '200';
		
		$cir = [];
		$sum = 0;
		$offs = 0;
		foreach ($pie as $k=>$v) {
			$sum += $v['count'];
			}
		
		foreach ($pie as $k=>$v) {
			$proc = round( (($v['count']/$sum)*100), 1);
			$v['uid'] = $v['uid'] ?? uniqid();
			$cir[] = '<circle id="pie_'.$v['uid'].'" style="cursor:pointer" title="'.$v['label'].'" cx="50%" cy="50%" r="25%" stroke-width="40%" fill="transparent" stroke="'.$v['color'].'88" stroke-dasharray="'.number_format($proc,1,'.','').' 100" stroke-dashoffset ="-'.number_format($offs,1,'.','').'"  OnMouseOver="facets.graphActive(\''.$v['uid'].'\');"  OnMouseOut="facets.graphDisActive(\''.$v['uid'].'\');"/>';
			$offs += $proc;
			}
		
		
		return '
			<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 64 64">'.implode('',$cir).'
			   Sorry, your browser does not support inline SVG.
			</svg> 
			';
		}
		 
	public function drawTimeLineGraph($title, $field='', $arr = array(), $baseConditions='') {
		$view = 200;
		if (count($arr)>0) {
			
			$graphId = uniqid();
			$max = max($arr);
			$min = min($arr);
			
			$max_d = max(array_keys($arr));
			$min_d = min(array_keys($arr));
			
			for ($i = $min_d; $i<= $max_d; $i++)
				if (empty($arr[$i]))
					$arr[$i] = 0;
			ksort($arr);
			
			$return = '';
			$return .= '<div class="text-center" style="padding:20px;">
				<div class="graph-area" style="margin-left:auto; margin-right:auto;">';
			#echo "Max: $max<br><br>";
			foreach ($arr as $k=>$v) {
				$pr = round(($v/$max)*$view);
				#echo "$k: $v -> $pr<Br>";
				$return .="
					<a class='graph-cloud' title='".$this->cms->transEsc('year').": $k, ".$this->cms->transEsc('publication count').": $v' data-lightbox-ignore OnClick=\"snapSlider.noUiSlider.set([$k,$k])\">
						<div class='graph-straw' style='height:{$pr}px;' id='year_bar_$graphId$k' ></div>
					</a>";
				}
			$return .= "</div>";

			$return .="<div style='float:left'>$min_d</div>";
			$return .="<div style='float:right'>$max_d</div>";
			$return .="<div style='display:block; width:10px;'>&nbsp;</div>";
			// daterange[]=year_str_mv&year_str_mvfrom=1544&year_str_mvto=1880
			$clicktitle = $this->cms->transEsc('Click to apply');
			$msg = $this->cms->transEsc('Show results for range');
			$link = $this->cms->buildUrl('results/biblio', []);
			$return .="
				<div id='sliderRound$graphId' style='padding:1px;'></div>
				<div id='range_area_$graphId' style='padding:10px; '><button id='range_link_$graphId' OnClick='facets.timeStatLink(\"$graphId\")' type=\"button\" class=\"btn btn-link\"></button></div>
				<input type='hidden' id='year_str_from_$graphId'/>
				<input type='hidden' id='year_str_to_$graphId' /> 
				<input type='hidden' id='range_field_$graphId' value='$field'/>
				<input type='hidden' id='base_conditions_$graphId' value=\"".base64_encode($baseConditions)."\"/> 
				<script>
					var snapSlider = document.getElementById('sliderRound$graphId');
					noUiSlider.create(snapSlider, {
						start: [ $min_d, $max_d ],
						connect: true,
						step: 1,
						range: {
							'min': [$min_d],
							'max': [$max_d]
						}
					});
					
					snapSlider.noUiSlider.on('update', function( values, handle ) {
						var setmin = parseInt(values[0]);
						var setmax = parseInt(values[1]);
						var str=' $msg <b>' + setmin + '</b> - <b>' + setmax + '</b>';
						$('#range_link_$graphId').html(str);
						$('#year_str_from_$graphId').val(setmin);
						$('#year_str_to_$graphId').val(setmax);
						
						for (let i = $min_d; i <= $max_d; i++) {
							$('#year_bar_$graphId'+i).css('background-color','lightgray');
							}
						for (let i = setmin; i <= setmax; i++) {
							$('#year_bar_$graphId'+i).css('background-color','#5c517b');
							}
						
					});
				</script>
					";
			$return .="</div>";
			
			if (!empty($title))
				return '
					<div class="il-panel">
						<div class="il-panel-header"><h4>'.$title.'</h4></div>
						<div class="il-panel-header">'.$return.'</div>
					</div>
					'; 
				else 
				return '
					<div class="il-panel">
						<div class="il-panel-header">'.$return.'</div>
					</div>
					'; 					
			} 
		}	
	
	
	public function drawTimeGraph($arr = array(), $field='') {
		$view = 200;
		if (count($arr)>0) {
			$max = max($arr);
			$min = min($arr);
			
			$max_d = max(array_keys($arr));
			$min_d = min(array_keys($arr));
			
			$return = '<div class="text-center" style="padding:20px;">
				<div class="graph-area" style="margin-left:auto; margin-right:auto;">';
			#echo "Max: $max<br><br>";
			foreach ($arr as $k=>$v) {
				$pr = round(($v/$max)*$view);
				#echo "$k: $v -> $pr<Br>";
				$return .="
					<a class='graph-cloud' title='rok: $k, liczba publikacji: $v' data-lightbox-ignore OnClick=\"snapSlider.noUiSlider.set([$k,$k])\">
						<div class='graph-straw' style='height:{$pr}px;' id='year_bar_$k' ></div>
					</a>";
				}
			$return .= "</div>";

			$return .="<div style='float:left'>$min_d</div>";
			$return .="<div style='float:right'>$max_d</div>";
			$return .="<div style='display:block; width:10px;'>&nbsp;</div>";
			// daterange[]=year_str_mv&year_str_mvfrom=1544&year_str_mvto=1880
			$return .="
				<div id='slider-round' style='padding:1px;'></div>
				<div id='range_link' style='padding:10px; '></div>
				<script>
					var snapSlider = document.getElementById('slider-round');
					noUiSlider.create(snapSlider, {
						start: [ $min_d, $max_d ],
						connect: true,
						step: 1,
						range: {
							'min': [$min_d],
							'max': [$max_d]
						}
					});
					
					snapSlider.noUiSlider.on('update', function( values, handle ) {
						var setmin = parseInt(values[0]);
						var setmax = parseInt(values[1]);
						var str='<a href=\"?daterange[]=year_str_mv&year_str_mvfrom='+setmin+'&year_str_mvto='+setmax+'\" title=\"Kliknij aby zastosować\" data-lightbox-ignore> Show results for range <b>' + setmin + '</b> - <b>' + setmax + '</b></a>';
						$('#range_link').html(str);
						$('#year_str_mvfrom').val(setmin);
						$('#year_str_mvto').val(setmax);
						
						for (let i = $min_d; i <= $max_d; i++) {
							$('#year_bar_'+i).css('background-color','lightgray');
							}
						for (let i = setmin; i <= setmax; i++) {
							$('#year_bar_'+i).css('background-color','#5c517b');
							}
						
					});
				</script>
					";
			$return .="</div>";
			
			return $return;	
			} 
		}	


	function drawJsonMenu ($json, $prefix='') {
		$content = '<ul class="list-menu">';
		if (!empty($json) && (is_object($json) or is_array($json))) {
			foreach ($json as $key=>$values) {
				if (is_object($values) or is_array($values))
					$subcontent = $this->drawJsonMenu($values);
					else {
					if (is_bool($values))
						$values = $values ? 'true':'false';
					$subcontent = ' = '.(string)$values;
					}
				$content .= '<li class="list-menu-item">'.$key.$subcontent.'</li>';
				} 
			}
		$content .= '</ul>';
		
		
		return $content;
		}

	function countEntries($json) {
		$total = 0;
		$toCount = (array)$json;
		foreach ($toCount as $entry) {
			$total ++;
			if (is_array($entry) or is_object($entry))
				$total += $this->countEntries($entry);
			}
		return $total;	
		}


	function onlyYear($sd) {
		if (!empty($sd))
			if (substr($sd,0,1) == '-')
				return substr($sd,0,5);
				else 
				return substr($sd,0,4);
		return null;
		}

	/*
	function from https://www.hashbangcode.com/article/php-function-turn-integer-roman-numerals
	*/
	public function integerToRoman($inputInteger) {
		// Convert the integer into an integer (just to make sure)
		$integer = abs(intval($inputInteger));
		
		if ($integer <> 0) {
			$result = '';
			// Create a lookup array that contains all of the Roman numerals.
			$lookup = array(
					'M' => 1000,
					'CM' => 900,
					'D' => 500,
					'CD' => 400,
					'C' => 100,
					'XC' => 90,
					'L' => 50,
					'XL' => 40,
					'X' => 10,
					'IX' => 9,
					'V' => 5,
					'IV' => 4,
					'I' => 1
					);
	 
			foreach($lookup as $roman => $value){
				// Determine the number of matches
				$matches = intval($integer/$value);
				// Add the same number of characters to the string
				$result .= str_repeat($roman, $matches);
	 
				// Set the integer to be the remainder of the integer and the value
				$integer = $integer % $value;
				}
			// The Roman numeral should be built, return it
			return $result;
			} else 
			return $inputInteger;
		}	
		
		
	public function drawTimeGraphAjax($arr = array(), $field='') {
		$drawId = uniqid();
		$view = 200;
		if (count($arr)>0) {
			$max = max($arr);
			$min = min($arr);
			
			$max_d = max(array_keys($arr));
			$min_d = min(array_keys($arr));
			if ($max>0) {
				$return = '<div class="text-center" style="padding:20px;">
					<div class="graph-area" style="margin-left:auto; margin-right:auto;">';
				#echo "Max: $max<br><br>";
				foreach ($arr as $k=>$v) {
					$pr = round(($v/$max)*$view);
					#echo "$k: $v -> $pr<Br>";
					$return .="
						<a class='graph-cloud' title='$k: $v' data-lightbox-ignore OnClick=\"snapSlider$drawId.noUiSlider.set([$k,$k])\" >
							<div class='graph-straw' style='height:{$pr}px;' id='year_bar_$k' ></div>
						</a>";
					}
				$return .= "</div>";
				

				$return .="<div style='float:left'>$min_d</div>";
				$return .="<div style='float:right'>$max_d</div>";
				$return .="<div style='display:block; width:10px;'>&nbsp;</div>";
				// daterange[]=year_str_mv&year_str_mvfrom=1544&year_str_mvto=1880
				$return .="
					<div id='slider-round-$drawId' style='padding:1px;'></div>
					<div id='range_link' style='padding:10px; '></div>
					<script>
						var snapSlider$drawId = document.getElementById('slider-round-$drawId');
						noUiSlider.create(snapSlider$drawId, {
							start: [ $min_d, $max_d ],
							connect: true,
							step: 1,
							range: {
								'min': [$min_d],
								'max': [$max_d]
							}
						});
						
						snapSlider$drawId.noUiSlider.on('update', function( values, handle ) {
							var setmin = parseInt(values[0]);
							var setmax = parseInt(values[1]);
							$('#year_str_mvfrom').val(setmin);
							$('#year_str_mvto').val(setmax);
							
							for (let i = $min_d; i <= $max_d; i++) {
								$('#year_bar_'+i).css('background-color','lightgray');
								}
							for (let i = setmin; i <= setmax; i++) {
								$('#year_bar_'+i).css('background-color','#5c517b');
								}
							
						});
					</script>
						";
				$return .="</div>";
				
				return $return;	
				}
			} 
		}	


	
	public function getGraphColor($nr) {
		/*
		$colors[] = '#6d5b97'; 	
		$colors[] = '#844981'; 	
		$colors[] = '#a9729c'; 	
		$colors[] = '#7981a8'; 	
		$colors[] = '#e18bb8'; 	
		$colors[] = '#f39863'; 	
		$colors[] = '#c59169'; 	
		$colors[] = '#e3bda8'; 	
		$colors[] = '#b1ad7e'; 	
		*/
		
		$colors[] = '#66BF87'; 	# Zieleń naturalna
		$colors[] = '#9BD3A2'; 	# Zieleń trawy
		$colors[] = '#C1E0C1'; 	# Zielony
		$colors[] = '#DEEDDA'; 	# Jasny zielony
		$colors[] = '#AFDAED'; 	# Jasny niebieski
		$colors[] = '#5EC0ED'; 	# Błękit
		$colors[] = '#87A6D5'; 	# Lawendowy
		$colors[] = '#DCB9D7'; 	# Lila
		$colors[] = '#FBCEB7'; 	# Łososiowy
		$colors[] = '#F7D80E'; 	# Złoty
		$colors[] = '#FCFCD8'; 	# Kremowy
		$colors[] = '#F8F1D7'; 	# Piaskowy
			
		if (!empty($colors[$nr]))
			return $colors[$nr];
			else {
			$r=dechex (rand(150,240));
			$g=dechex (rand(150,240));
			$b=dechex (rand(150,240));
			
			$kolor="#$r$g$b"; 
			return $kolor;	
			}
		}	 
		
		
	public function drawWorldMap($Tp = array()) {
		$points = '';
		
		
		// $points = "<circle cx='0' cy='0' r='10' stroke='blue' stroke-width='1' fill='rgb(0,0,200)' />"; // min
		// $points .= "<circle cx='600' cy='400' r='10' stroke='blue' stroke-width='1' fill='rgb(0,0,200)' />"; // max
		
		// $points .= "<circle cx='300' cy='180' r='2' stroke='red' stroke-width='1' fill='rgb(0,0,200)' />"; // londyn
		// $points .= "<circle cx='300' cy='280' r='2' stroke='red' stroke-width='1' fill='rgb(0,0,200)' />"; // 0,0 
			
			
		foreach ($Tp as $point) {
			$lat = $point['lat'];
			$lon = $point['lon'];
			
			$cy = ((-$lat*340)/180)+280;
			$cx = (($lon*300)/180)+300;
			
			$cx = 300+$lon*1.65;
			$cy = 280-$lat*1.83;
			
			#echo "$point[name]: $lat, $lon = $cx, $cy<br>";
			
			$points .= "<circle cx='$cx' cy='$cy' r='4' stroke='red' stroke-width='1' fill='rgb(200,0,0)' />";
			}	
			
		$map = file_get_contents("config/world.svg");
		$map = str_replace('{{points}}', $points, $map);
		return $map;
		}	
			
	public function drawEuropeMap($Tp = array()) {
		$points = '';
		$lp = 0;
		foreach ($Tp as $p) {
			$lp++;
			$Tx['p'][$lp] = "$p[lat],$p[lon]";
			$Tx['n'][$lp] = $p['name'];
			}
		$link = http_build_query($Tx);	
		$map = '<div class="europe-map">';
		$map.= '<img src="">';
		$map.= '</div>';
		$map.= "<pre>".print_R($Tp,1)."</pre>";
		return $map;
		}	
	
	public function inArray($k, $arr) {
		if (!empty($arr[$k]))
			return $arr[$k];
		return $k;
		}
	
	public function facetName($core, $facet) {
		return $this->cms->configJson->$core->facets->solrIndexes->$facet->name ?? $facet;
		}
	
	public function formatUserList($value) {
		$tmp = explode('|', $value);
		$list_id = intval($tmp[1]);
		$t = $this->cms->psql->querySelect("SELECT * FROM users_lists WHERE id = '$list_id';");
		if (is_Array($t)) {
			$tmp = current($t);
			$listName = $tmp['list_name'];
			return $listName;
			} else
			return $value;
		}
	
	public function formatWiki($wikiq) {
		$userLang = $this->cms->userLang;
		
		if (!empty($this->wikiLabels->$userLang[$wikiq]))
			return $this->wikiLabels->$userLang[$wikiq];
			
		if (empty($this->wikiLabels))
			$this->wikiLabels = new stdClass;

		$this->cms->wiki->loadRecord($wikiq,false);
		$label = $this->cms->wiki->get('labels',$userLang);
			
		$this->wikiLabels->$userLang[$wikiq] = $label;
		return $label;
		}
		
	public function formatWikiWithRole($string) {
		$tmp = explode('|', $string);
		$userLang = $this->cms->userLang;
		if (count($tmp) == 2) {
			$wikiq = $tmp[0];
			$role = $this->cms->transEsc($tmp[1]);
			
			
			$this->cms->wiki->loadRecord($wikiq,false);
			$label = $this->cms->wiki->get('labels',$userLang);
			return $label .' <span class="label label-info">'.$this->cms->transEsc($tmp[1]).'</span>';
			}
		return $string." ($userLang)";
		}
		
			
	
	public function formatMagazines($value) {
		return $this->formatMultiLang($value);
		}
		
	public function createMagazineStr($value) {
		$value = (array)$value;
		if (!empty($value['bestLabel']))
			return $value['bestLabel'];
		$v['title'] = $value['title'] ?? null;
		$v['issn'] = $value['issn'] ?? null;
		return implode('|', $v);
		}
	
	
	public function json2Object($value) {
		return json_decode($value);	
		}
	
	public function formatCentury($value) {
		$tvalue = $this->integerToRoman($value);
		if ($value < 0) $tvalue .= ' '.$this->cms->transEsc('b.c.');
		return $tvalue;	
		}
	
	public function createPlaceStr($value) {
		$value = (array)$value;
		if (!empty($value['bestLabel']))
			return $value['bestLabel'];
		$v['wikiQ'] = $value['wikiQ'] ?? null;
		if (!empty($value['nameML']))
			return $v['wikiQ'].'|'.$value['nameML'];
			else 
			return $v['wikiQ'].'|'.$value['name'];
		}
	
	
	public function formatPlace($k) {
		/*
		0 - wikiq
		1..n - names
		*/
		return explode('|',$k)[0]; // only if bestlabel is taken 
		return $this->formatMultiLang($k);
		}	
		
	public function formatMultiLangStr($k) {
		/*
		0..n - names
		*/
		$res = explode('|', $k);
		$key = array_search($this->cms->userLang, $this->cms->configJson->settings->multiLanguage->order);
		if (!empty($res[$key]))
			return $res[$key];
		return $res[0]; 
		}

	public function formatMultiLang($k) {
		/*
		0..n - names
		*/
		$res = explode('|', $k);
		$key = array_search($this->cms->userLang, $this->cms->configJson->settings->multiLanguage->order)+1;
		if (!empty($res[$key]))
			return $res[$key];
		return $res[0]; 
		}

	public function formatTakeBestML($k) { 
		/*
		0..n - names
		*/
		$res = explode('|', $k);
		$key = array_search($this->cms->userLang, $this->cms->configJson->settings->multiLanguage->order);
		if (!empty($res[$key]))
			return $res[$key];
		foreach ($res as $val)
			if (!empty($val))
				return $val;
		return $k; 
		}

		
	
	public function formatEvent($k) {
		/*
		0 - name
		1 - year
		2 - place
		3 - edition
		*/
		return $this->formatMultiLang($k);
		
		
		$res = explode('|',$k);
		$name = $date = $place = $id = '';
		$name = $res[0]; 
		if (!empty($res[2])) {
			$place = '<br/><small class="label label-info">'.$res[2].'</small>';
			}
		if (!empty($res[1])) {
			$date = ' <small class="label label-success">'.$res[1].'</small>';
			}
		if (!empty($res[3])) {
			$viaf = $res[2];
			}
		
		return $name.$date; //.$ID 
		}	
	
	public function createEventStr($value) {
		$value = (array)$value;
		if (!empty($value['bestLabel']))
			return $value['bestLabel'];
		return implode('|',[
				'name'		=> $value['name'] ?? null,
				'year'		=> $value['year'] ?? null,
				'wikiQ'		=> $value['wikiQ'] ?? null,
				]);
		}
	
	
	public function formatPerson($k) {
		/*
		0 - name
		1 - year_born
		2 - year_death
		3 - viaf
		4 - wikiq
		5 - date (range)
		*/
		$res = explode('|',$k);
		$name = $date = $viaf = $wikiq = '';
		$name = $res[0]; 
		if (!empty($res[1])) {
			$date = ' <small class="dataView">'.$res[1].'</small>';
			}
		
		return $name.$date; //.$ID 
		}

	public function createPersonStr($value) {
		$value = (array)$value;
		if (!empty($value['bestLabel']))
			return $value['bestLabel'];
		return implode('|',[
				'name'		=> $value['name'] ?? null,
				'year_born'	=> $value['year_born'] ?? null,
				'year_death'=> $value['year_death'] ?? null,
				'viaf'		=> $value['viaf'] ?? null,
				'wikiQ'		=> $value['wikiQ'] ?? null,
				'date'		=> $value['dates'] ?? null,
				]);
		}

	
	public function formatCorporate($k) {
		/*
		0 - name
		1 - wikiq
		3 - roles 
		*/
		return explode('|',$k)[0]; // for best label (not if multilanguage variant)
		return $this->formatMultiLang($k);
		}

		
	public function createCorporateStr($value) {
		$value = (array)$value;
		if (!empty($value['bestLabel']))
			return $value['bestLabel'];
		return implode('|',[
				'name'		=> $value['name'] ?? null,
				'wikiQ'		=> $value['wikiQ'] ?? null,
				]);
		}


	public function formatMajorRole($string) {
		$res = new stdclass;
		$linksParams = [
			'mainAuthor' => [
					'color'=> '#008000', // green
					'ico' => 'ph ph-pen-nib-bold',
					'title'=> 'as main author'
					],
			'coAuthor' => [
					'color'=> '#808000', // oliv
					'ico' => 'ph ph-bold ph-pencil-simple-line',
					'title'=> 'as co-author'
					],
			'subjectPerson' => [
					'color'=> '#008080', // teal
					'ico' => 'ph ph-user-list',
					'title'=> 'as subject'
					],
			'subjectCorporate' => [
					'color'=> '#800000', // maroon
					'ico' => 'ph ph-users-three',
					'title'=> 'as subject'
					],
			'subjectEvent' => [
					'color'=> '#fef200',
					'ico' => 'ph ph-calendar',
					'title'=> 'as subject'
					],
			'subjectMagazine' => [
					'color'=> '',
					'ico' => 'ph ph-article-medium',
					'title'=> 'as subject'
					],
			/* places */
			'publication' => [
					'color'=> '#0000ff', // blue
					'ico' => 'ph ph-bold ph-signpost',
					'title'=> 'as publication place'
					],
			'publicationPlace' => [
					'color'=> '#0000ff', // blue
					'ico' => 'ph ph-bold ph-signpost',
					'title'=> 'as publication place'
					],
			'subject' => [
					'color'=> '#000080', // navy
					'ico' => 'ph ph-map-trifold',
					'title'=> 'as subject place'
					],
			'subjectPlace' => [
					'color'=> '#000080', // navy
					'ico' => 'ph ph-map-trifold',
					'title'=> 'as subject place'
					],
			'event' => [
					'color'=> '#7dfff5', //electric blue
					'ico' => 'ph ph-calendar',
					'title'=> 'as event place'
					],
			'eventPlace' => [
					'color'=> '#7dfff5',
					'ico' => 'ph ph-calendar',
					'title'=> 'as event place'
					],
			'sourceMagazine' => [
					'color'=> '#862adb', 
					'ico' => 'ph ph-article-medium',
					'title' => 'as source magazine'
					],
			'birthPlace' => [
					'color' => '#87d067',
					'ico' => 'ph ph-article-medium',
					'title'=> 'birth place'
					],
			
			
			
			'deathPlace' => [
					'color' => '#87d067',
					'ico' => 'ph ph-user',
					'title'=> 'death place'
					],
			'deathPlace' => [
					'color' => '#87d067',
					'ico' => 'ph ph-user',
					'title'=> 'birth place'
					],
			'residencePlace' => [
					'color' => '#87d067',
					'ico' => 'ph ph-user',
					'title'=> 'residence place'
					],
			
			];
		if (!empty($linksParams[$string])) {
			$res = (object)$linksParams[$string];
			$res->title = $this->cms->transEsc($res->title);
			return $res;
			}	
		return (object) [
				'color' => '#000',
				'ico' => 'ph ph-seal-warning',
				'title' => $string
				];
		}
	
	public function formatBasicRole($string) {
		$res = new stdclass;
		$linksParams = [
			
			'publicationPlace' => [
					'color'=> '#0000ff', // blue
					'ico' => 'ph ph-bold ph-signpost',
					'title'=> 'publication places'
					],
			'subjectPlace' => [
					'color'=> '#800000', // red
					'ico' => 'ph ph-map-trifold',
					'title'=> 'subject places'
					],
			
			'birthPlace' => [
					'color' => '#87d067',
					'ico' => 'ph ph-user',
					'title'=> 'birth place'
					],
			'deathPlace' => [
					'color' => '#87d067',
					'ico' => 'ph ph-user',
					'title'=> 'death place'
					],
			'birthPlacePR' => [
					'color' => '#48d1cc',
					'ico' => 'ph ph-user',
					'title'=> 'birthplace of newcomers'
					],
			'deathPlacePR' => [
					'color' => '#006400',
					'ico' => 'ph ph-user',
					'title'=> 'place of death of &quot;emigrants&quot;'
					],
			'residencePlace' => [
					'color' => '#87d067',
					'ico' => 'ph ph-user',
					'title'=> 'residence places'
					],
			'residencePlace' => [
					'color' => '#87d067',
					'ico' => 'ph ph-user',
					'title'=> 'residence places'
					],
			
			];
		if (!empty($linksParams[$string])) {
			$res = (object)$linksParams[$string];
			$res->title = $this->cms->transEsc($res->title);
			return $res;
			}	
		return (object) [
				'color' => '#000',
				'ico' => 'ph ph-seal-warning',
				'title' => $string
				];
		}
	

	
	public function matchLevelStr($result, $compareTable, $ids = []) {
		if (!empty($result->viaf) && in_array(trim($result->viaf), $ids))
			return 1;
		if (!empty($result->eids) && in_array(trim($result->eids), $ids)) 
			return 1;
			
		
		$orginLabel = $result->name;
		if (!empty($result->dates))
			$orginLabel = ' '.$result->dates;
		
		$Tres = [];
		$onlyCharsNumbers = $this->clearStr($orginLabel);
		if (!empty($compareTable) && is_array($compareTable)) 
			foreach ($compareTable as $strToCheck) {
				similar_text($orginLabel, $strToCheck, $procent);
				$Tres[] = (0.9 * $procent)/100; 
				similar_text($onlyCharsNumbers, $this->clearStr($strToCheck), $procent);
				$Tres[] = (0.9 * $procent)/100; 
				}
		if (!empty($Tres)) {
			arsort($Tres);
			return current($Tres);
			}	
		
		return 0;
		}
	
	public function matchLevel($result, $wikiResult) {
		if (!empty($result->ids) && (count((array)$result->ids)>0) && !empty($result->wikiQ) && ($result->wikiQ !== 'not found') && ($result->wikiQ == $wikiResult->wikiq))
			return 1;
		$orginLabel = $result->name;
		if (substr($wikiResult->labels,0,1) == '{') 
			$toCheck = json_decode($wikiResult->labels, true);
			else 
			$toCheck = (array)$wikiResult->labels;	
		
		if (!empty($wikiResults->aliases))
			$toCheck = array_unique(array_merge($toCheck, json_decode($wikiResult->aliases, true)));
		$Tres = [];
		foreach ($toCheck as $strToCheck) {
			similar_text($orginLabel, $strToCheck, $procent);
			$Tres[] = (0.9 * $procent)/100; 
			}
		if (!empty($Tres)) {
			arsort($Tres);
			return current($Tres);
			}	
		
		return 0;
		}
	
	
	
	public function authorFormatFromString($k) {
		
		$translatedKey = $k;
		if (stristr($k, ')')) {
			$translatedKey = str_replace('(', "<small class='dataView'>(", $k);
			$translatedKey = str_replace(')', ")</small>", $translatedKey);
			} 
		$ID = '';
		$tmp = explode(' ',$k);
		$count = count($tmp)-2;
		if ($count>0) { // maybe there is id or data 
			$lastWord = array_pop($tmp);
			$almostLastWord = $tmp[$count];
			
			if ((preg_match_all( "/[0-9]/", $lastWord)>5)or(stristr($lastWord,'viaf'))) {
				$translatedKey = str_replace($lastWord, "", $translatedKey);
				$ID = " <i class='ph-identification-badge id-tag' title='Id: $lastWord'></i> ";
				}
			}
		
		return $translatedKey; //.$ID 
		}	
	
	function getNeededFacets($facets) {
		foreach ($facets as $facet) {
			if (!empty($facet->solr_index)) 
				$this->facets[] =  $facet->solr_index;
			
			if (!empty($facet->groupList))
				$this->getNeededFacets($facet->groupList);
			}
		return $this->facets;		
		}
	
	function clearStr( $str, $replace = " " ){
		if (!empty($str) && is_string($str)) {
			$oldStr = $str;
			setlocale(LC_ALL, 'pl_PL.UTF8'); // any european non-en local works fine
			$str = @iconv('UTF-8', 'ASCII//TRANSLIT', $str);
			$charsArr = array( '^', "'", '"', '`', '~');
			$str = str_replace( '-', ' ', $str );
			$str = str_replace( $charsArr, '', $str );
			$return = trim(preg_replace('# +#',' ',preg_replace('/[^a-zA-Z0-9\s]/','',strtolower($str))));
			
			return str_replace(' ', $replace, $return);
			}
        }
		
	function clearLatin( $str, $replace = " " ){
		if (!empty($str) && is_string($str)) {
			$oldStr = $str;
			setlocale(LC_ALL, 'pl_PL.UTF8'); // any european non-en local works fine
			$str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
			$charsArr = array( '^', "'", '"', '`', '~');
			$str = str_replace( ['-', '|'], ' ', $str );
			$str = str_replace( $charsArr, '', $str );
			$return = trim(preg_replace('# +#',' ',preg_replace('/[^a-zA-Z0-9\s]/','',strtolower($str))));
			
			return str_replace(' ', $replace, $return);
			}
        }
		
		
	public function clearName($z) {
		if (is_string($z))
			return trim(str_replace(['[',']',':',';','(',')'], '', $z));
		if (is_array($z)) {
			foreach ($z as $v)
				$Tr[] = trim(str_replace(['[',']',':',';','(',')'], '', $v));
			return implode(', ',$Tr);	
			}
		return $z;
		}	
		
		
	public function numberFormat($number) {
		if (!empty($number) && is_numeric($number))
			return number_format($number,0,'','.'); 
		return 0;
		}

	public function badgeFormat($number) {
		$number = intval($number); // just in case 
		if ($number>1000000)
			return floor($number/1000000).'M';
		if (($number>1000)&($number<10000))
			return round($number/1000,1).'K';
		if ($number>1000)
			return floor($number/1000).'K';
		return $number; 
		}

	public function langMenu($that) {
		$langs = $that->lang;
		if (!empty ($langs['available']) && (is_array($langs['available'])))
			$list = $langs['available'];
			else 
			$list = ['en' => 'English'];
		if (!empty ($langs['userLang']))
			$uLang = $langs['userLang'];
			else 
			$uLang = 'en';
		
		$content = '';
		foreach ($list as $langCode=>$langName) {
			$linkParts = $that->linkParts; 
			$linkParts[1] = $langCode;
			if ($langCode == $uLang)
				$active = 'active';
				else 
				$active = '';
			$content .='
				<li class="language '.$active.'">
				<a  href="'.$that->HOST.implode('/',$linkParts).'" 
					style="background-image: url(\''.$that->HOST.'themes/default/images/languages/'.$langCode.'.svg\'); " 
					title="'.$langName.'" >
					<span class="sr-only">'.$langName.'</span>
				</a>
				</li>';
			}	
		return $content;
		}


	function getLicence($conditions=[]):array {
		$licenceTable = [];
		$addConditons = [];
		$addConditonsStr = '';
		
		if (!empty($conditions)) {
			foreach ($conditions as $key=>$value) {
				if (is_numeric($key))
					$addConditons = $value;
					else 
					$addConditons = $key.'='.$this->cms->psql->string($value);
				}
			$addConditonsStr = 'WHERE '.implode(' AND ', $addConditons);
			}
				
		
		$t = $this->cms->psql->querySelect("SELECT * FROM licence_source_db a JOIN licences b ON a.licence_code = b.id $addConditonsStr;");
		if (is_array($t)) 
			foreach ($t as $row) {
				$licenceTable[$row['source_db']] = (object) [
						'code' => $row['licence_code'],
						'description' => $row['description'],
						'link' => $row['link']
						];
				}
		return $licenceTable;
		}

	
	}
?>