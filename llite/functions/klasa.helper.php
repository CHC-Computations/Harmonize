<?PHP


class helper {
	
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
			$result .='</ul><br/>'.$car;
			
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
		$wlen = strlen($str);
		
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
			$str = '<span title="'.$wstr.'">'.$str.'</span>';
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
			  <ul class="dropdown-menu">
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
				$idBox = uniqid();
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
			
			
			$res.='<a class="list-group-item '.$class.'" '.$link.'rel="nofollow" title="'.$row['title'].'">'.$ico.$row['title'].'</a>'.$submenu;
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
			return "
				<div class='procent-box'>
					<span class=overlaygrow style='width:{$proc}%; background-color: {$color};'></span>
					<span class=overlay><span class=liczba>".number_format($liczba,0,'','.')."</span></span>
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
		}
	
	public function multiPercent($dane=array(), $max=1) {
		foreach ($dane as $row) {
			$procent=round(($row[count]/$max)*100,2);
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
			$size = number_Format( ($size/1073741824), 1, '.', ' ').' Gb';
			return $size;
			}
		if ($size>1048576) {
			$size = number_Format( ($size/1048576), 1, '.', ' ').' Mb';
			return $size;
			}
		if ($size>1024) {
			$size = number_Format( ($size/1024), 1, '.', ' ').' Kb';
			return $size;
			}
		
		return $size.' b';
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
	
	public function convert($facet, $value) {
		$label = $value;
		if (!empty($this->cms->settings->facets->solrIndexes->$facet->translated) && ($this->cms->settings->facets->solrIndexes->$facet->translated)) {
			$label = $this->cms->transEsc($label);
			}
		if (!empty($this->cms->settings->facets->solrIndexes->$facet->formatter)) {
			$formatter = $this->cms->settings->facets->solrIndexes->$facet->formatter;
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
			$cir[] = '<circle id="pie_'.$k.'" style="cursor:pointer" title="'.$v['label'].'" cx="50%" cy="50%" r="25%" stroke-width="40%" fill="transparent" stroke="'.$v['color'].'88" stroke-dasharray="'.number_format($proc,1,'.','').' 100" stroke-dashoffset ="-'.number_format($offs,1,'.','').'"  OnMouseOver="facets.graphActive(\''.$k.'\');"  OnMouseOut="facets.graphDisActive(\''.$k.'\');"/>';
			$offs += $proc;
			}
		
		
		return '
			<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 64 64">'.implode('',$cir).'
			   Sorry, your browser does not support inline SVG.
			</svg> 
			';
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

	function onlyYear($sd) {
		if (substr($sd,0,1) == '-')
			return substr($sd,0,5);
			else 
			return substr($sd,0,4);
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
	
	public function formatWiki($wikiq) {
		$userLang = $this->cms->userLang;
		if (is_numeric($wikiq)) {
			if (!empty($this->wikiLabels->$userLang[$wikiq]))
				return $this->wikiLabels->$userLang[$wikiq];
			
			if (empty($this->wikiLabels))
				$this->wikiLabels = new stdClass;

			$t = $this->cms->psql->querySelect("SELECT value FROM wiki_labels WHERE wikiq = '$wikiq' AND lang='$userLang';");
			if (is_array($t)) {
				$res = current($t)['value'];
				$this->wikiLabels->$userLang[$wikiq] = $res;
				return $res;
				}
			
			require_once('./functions/klasa.wikidata.php');
			$wiki = new wikidata('Q'.$wikiq);
			$wiki->setUserLang($userLang);
			$label = $wiki->get('labels');
			$this->cms->psql->query("INSERT INTO wiki_labels (wikiq, lang, value) VALUES ('$wikiq', '$userLang', {$this->cms->psql->isNull($label)});");
			$this->wikiLabels->$userLang[$wikiq] = $label;
			return $label;
			}
		return 'Q'.$wikiq." ($userLang)";
		}
	
	public function formatMagazines($value) {
		$value = str_replace(['{','}','name='], '', $value);
		$t = explode(', issn=', $value);
		
		return $t[0];
		}
	
	public function formatCentury($value) {
		$tvalue = $this->integerToRoman($value);
		if ($value < 0) $tvalue .= ' '.$this->cms->transEsc('b.c.');
		return $tvalue;	
		}
	
	
	public function formatPlace($k) {
		/*
		0 - wikiq
		1..n - names
		*/
		
		$res = explode('|',$k);
		if ($res[0] == 'not found')
			return $res[1];
		
		$languages = $this->cms->settings->multiLanguage->order;
		$lp = 0;
		foreach ($languages as $lang) {
			$lp++;
			if (($lang == $this->cms->user->lang['userLang']) && !empty($res[$lp]))
				return $res[$lp];
			}
		
		return $res[1]; 
		}	
	
	public function formatEvent($k) {
		/*
		0 - name
		1 - year
		2 - place
		3 - edition
		*/
		
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
		
		return $name.$place.$date; //.$ID 
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
		if (!empty($res[5])) {
			$date = ' <small class="dataView">'.$res[5].'</small>';
			}
		if (!empty($res[3])) {
			$viaf = $res[2];
			}
		
		return $name.$date; //.$ID 
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
	
		
	public function numberFormat($number) {
		return number_format($number,0,'','.'); 
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




	
	}
?>