<?php 

function drawChartMonth($lines, $counters) {
	$barHeight = 200;
	$max = max($lines);
	$result = '<table class="chartBar">
		<tr>';
	$oldYear=0;	
	foreach ($lines as $date=>$line) {
		
		$height = round(($line/$max)*$barHeight);
		$t = explode('-',$date);
		$year = $t[0];
		if ($oldYear == 0) 
			$oldYear = $year;
		if ($year != $oldYear)
			$strong = ' strongRight';
			else
			$strong = '';
		$month = $t[1];
		$cellId = $year.$month;
		$i = $counters[$date];
		$action = "onMouseOver=\"$('.chartBarArea').css('background','transparent'); $('.cell_{$cellId}').css('background','#eee');\" 
				onMouseOut=\"$('.chartBarArea').css('background','transparent');$('.chartBarLegend').css('background','transparent');\" 
				onClick=\"$('#myCarousel_details').carousel($i);\"
				";
		$result.='
			<td class="chartBarArea cell_'.$cellId.$strong.'"  '.$action.' >
			<div class="chartBarItem" style="height:'.$height.'px" title="'.$line.'" data-toggle="tooltip"></div>
			</td>
			';
		
		$legend[] = '
			<td class="chartBarLegend cell_'.$cellId.$strong.' '.$action.'" >
				'.$month.'
			</td>
			';
		@$yearsTable[$year]++;
		$oldYear = $year;
		}
	$result .= '</tr><tr>';
	$result .= implode('', $legend);	
	$result .= '</tr><tr>';
	foreach ($yearsTable as $year=>$count)
		$result .= '<td class="chartBarLegend year" colspan="'.$count.'">'.$year.'</td>';
	$result .='</tr>
			</table>';
	return $result;
	}

function drawChart($that, $prefix, $lines) {
	$barHeight = 200;
	$max = max($lines);
	if ($max == 0) $max = 1;
	$result = '<table class="chartBar">
		<tr>';
	foreach ($lines as $date=>$line) {
		$height = round(($line/$max)*$barHeight);
		$cellId = $prefix.$date;
		$action = "onMouseOver=\"$('.chartBarArea').css('background','transparent'); $('.cell_{$cellId}').css('background','#eee');\" onMouseOut=\"$('.chartBarArea').css('background','transparent');$('.chartBarLegend').css('background','transparent');\" ";
		$result.='
			<td class="chartBarArea cell_'.$cellId.'" title="'.$line.'" '.$action.'>
			<div class="chartBarItem" style="height:'.$height.'px; background-color:#008000;"></div>
			</td>
			';
		
		$legend[] = '
			<td class="chartBarLegend cell_'.$cellId.' '.$action.'">
				<small>'.$date.'</small>
			</td>
			';
		}
	$result .= '</tr><tr>';
	$result .= implode('', $legend);	
	$result .='</tr>
			</table>';
	return $result;
	}
	
function drawChartSessions($that, $prefix, $lines) {
	$max = max($lines);
	$sec = 1;
	arsort($lines);
	$i = 0;
	foreach ($lines as $v) {
		$i++;
		if (($i==2)&($sec>0))
			$sec = $v;
		}
	
	$orderTable = [
		'0s-30s', 
		'30s-2mn',
		'2mn-5mn', 
		'5mn-15mn', 
		'15mn-30mn', 
		'30mn-1h',
		'1h+'
		];
	#$result = '<pre>'.print_r($lines,1).'</pre>';
	$result = '<table class="table table-hover" style="width:100%">
			<thead>
				<td>time<br/>frame</td>
				<td>visitors</td>
				<td></td>
			</thead>
			<tbody>
			';
	foreach ($orderTable as $date) {
		$line = $lines[$date] ?? 0;
		if ($line == $max) {
			$width = round(($line/$max)*300);
			$color = '#800000';
			$title = 'Value stands out strongly from the others. This bar in reality should be much larger.';
			} else {
			$width = round(($line/$sec)*200);
			$color = '#008000';
			$title = '';
			}
		$cellId = $prefix.$date;
		$result.='
			<tr>
				<td>'.$date.'</td>
				<td class="text-right" style="font-size:0.9em">'.$that->helper->numberFormat($line).'</td>
				<td class="chartBarHorizontalArea cell_'.$cellId.'" title="'.$title.'">
					<div class="chartBarHorizontalItem" style="width:'.$width.'px; background-color:'.$color.'; height:15px;"></div>
				</td>
			</tr>	
			';
		}
	$result .='</tbody>
		</table>';
	return $result;
	}


$startpoint = 86400*365*2;
$startKey = date("Y-m", time()-$startpoint);
$total = count($data);
$i = 0;
$visibleCount = -1;

foreach ($data as $block) {
	$i++;
	$b = (object)$block;
	$month = str_pad($b->month, 2, "0", STR_PAD_LEFT );
	
	$key = "{$b->year}-{$month}";
	if ($key >= $startKey) {
		$visibleCount++;
		$counters[$key] = $visibleCount;
		$lines['visitors'][$key] = $b->visitors;
		$lines['pages'][$key] = $b->sider;
		if ($b->visitors > 0)
			$pagesPerUser = round($b->hits/$b->visitors, 2);
			else 
			$pagesPerUser = 0;	
		$lines['pagesPerUser'][$key] = $pagesPerUser;
		$lines['hits'][$key] = $b->hits;
		
		$b->day = json_decode($b->day);
		$b->time = json_decode($b->time);
		$b->session = json_decode($b->session);
		
		foreach ($b->time as $dayLine) {
			$t = explode(' ', $dayLine);
			$hour = $t[0];
			$visitors = $t[1];
			$click = $t[2];
			$lines['hours'][$key][$hour] = $click;
			}
		foreach ($b->session as $sessionLine) {
			$t = explode(' ', $sessionLine);
			$period = $t[0];
			@$allPeriods[$period]++;
			$click = $t[1];
			$lines['session'][$key][$period] = $click;
			}
		foreach ($b->day as $dayLine) {
			$t = explode(' ', $dayLine);
			$day = substr($t[0],6,2);
			$visitors = $t[1];
			$click = $t[2];
			$lines['days'][$key][$day] = $click;
			}
		
		$groups = [
			'hours' =>  (object)['function'=>'drawChart', 'legend' => 'Traffic per hour'], 
			'days' => 	(object)['function'=>'drawChart', 'legend' => 'Traffic by day of the month'], 
			'session' => (object)['function'=>'drawChartSessions', 'legend' => 'How long visitors stayed on the website']
			];
		$aclass = '';
		if ($i == $total)
			$aclass='active';
		$singleDetails = '
			<div class="item '.$aclass.'">
			<h3>Overview of the <b>'.$key.'</b> </h3>
			<p><i class="ph ph-arrow-fat-left"></i> Select month to change</p>
			<div class="panel panel-default" style="margin-right:5px;">
			<div class="panel-body">
				Visitors: <b>'.$this->helper->numberFormat($b->visitors).'</b><br/>
				Unique pages: <b>'.$this->helper->numberFormat($b->sider).'</b><br/>
				Subpages per visitor: <b>'.$this->helper->numberFormat($pagesPerUser).'</b><br/>
				Total numbers of page hits: <b>'.$this->helper->numberFormat($b->hits).'</b><br/>
				
			</div>
			</div>
			';
		foreach ($groups as $group=>$item) 
			if (!empty($lines[$group][$key])) {
				$function = $item->function;
				$singleDetails .= '<div class="panel panel-default" style="margin-right:5px;">
						<div class="panel-body">
							<h4>'.$item->legend.'</h4>
							'.$function($this, $group, $lines[$group][$key]).'
						</div>
					</div>';
				}
		$singleDetails .= '</div>';
		$details[$key] = $singleDetails;
		
		if (!empty($this->routeParam[0]) & !empty($this->routeParam[1]) && ($key == $this->routeParam[0].'-'.$this->routeParam[1])) {
			$toMap[$key] = $b->countries;
			}
		
		
		}
	}
	
	

echo '
	<div class="container">
		<h1><small>Data refers to</small> literarybibliography.eu</h1>
		<p>We present data from the last 2 years. The current month will be available after the end of the month. </p>
				
		<div class="row">
			<div class="col-sm-7">
				<div class="panel panel-default">
					<div class="panel-body">
						<h4 data-toggle="collapse" data-target="#visitorsNote">Unique visitors <i class="ph ph-info" title="more info" data-toggle="tooltip"></i></h4>
						<div class="collapse" id="visitorsNote">
							<p>The numbers refer to the unique IP addresses from which the website was accessed. </p>
							<p>In this diagram, all visits from the same address are counted only once. There can be multiple users behind the same address. It can also happen that the same user has a different IP address for subsequent visits (this was common with dial-up modems, but rarely happens nowadays).</p>
						</div>
						'.drawChartMonth($lines['visitors'], $counters).'
						<h4>How many unique pages did they open</h4>
						'.drawChartMonth($lines['pages'], $counters).'
						<h4>Subpages per visitor</h4>
						'.drawChartMonth($lines['pagesPerUser'], $counters).'
						<h4>Total numbers of page hits</h4>
						'.drawChartMonth($lines['hits'], $counters).'
					</div>
				</div>
			</div>
			<div class="col-sm-5">
				<div id="myCarousel_details" class="carousel slide" data-ride="carousel">
					<div class="carousel-inner">
					
						'.implode('', $details).'
					</div>
				</div>	
			</div>
		</div>
		<br/><br/>
		
	</div>
	';
$this->addJS('$("#myCarousel_details").carousel({interval: false});');	



	
if (!empty($b->countries)) {
	if (!empty($toMap)) {
		$key = key($toMap);
		$b->countries = current($toMap);
		}
		
	
	$countries = $c = json_decode($b->countries);	
	$total = $c->sumRecognized+$c->sumUnRecognized;
	$percent = round(($c->sumRecognized/$total)*100,1);
	echo '
		<div class="container">
			<div class="panel panel-default">
				<div class="panel-body">
				<p>Percentage of visits that have been located: <b>'.$percent.'</b>%. Maps show page hits for '.$key.'</p>
		';
	echo $this->helper->percent($c->sumRecognized, $total);	
	
	
	$mapLines = [];
	$printLines = [];
	$forMap = (array)$c->hits;
	arsort($forMap);
	
	$i = 0;
	foreach ($forMap as $region=>$count) {
		$region = str_replace("'", "`", $region);
		$mapLines[] = "['$region', $count]";
		$printLines[] = '<li class="list-group-item">'.$region.' <span class="badge">'.$this->helper->numberFormat($count).'</span></li>';
		$i++;
		}
	echo '
		<div class="row">
			<div class="col-sm-10">
				<div id="regions_div" style="width: 100%; height: 500px; "></div>
			</div>
			<div class="col-sm-2">
				<div style="max-height:500px; overflow:scroll">
					<div class="list-group">
					'.implode('',$printLines).'
					</div>
				</div>
			</div>
		</div>	
		';			
	
		
	#echo $this->helper->pre($countries);	
	echo '
				</div>
			</div>
		</div>
	';
	
	
	echo "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
		<script type='text/javascript'>
		 google.charts.load('current', {
				'packages':['geochart'],
			  });
			  google.charts.setOnLoadCallback(drawRegionsMap);

			  function drawRegionsMap() {
				var data = google.visualization.arrayToDataTable([
				  ['Country', 'Visitors'],
				  ".implode(",\n", $mapLines)."
				]);

				var options = {};

				var chart = new google.visualization.GeoChart(document.getElementById('regions_div'));

				chart.draw(data, options);
			  }

		 </script>";
	}

// https://developers.google.com/chart/interactive/docs/gallery/geochart?hl=pl  mapy 


?>

