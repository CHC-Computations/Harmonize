<?php 
if (($_SERVER['SERVER_ADDR'] == '192.168.0.40') or ($this->user->isLoggedIn() && $this->user->hasPower('admin'))) {

	require_once ('./functions/class.lists.php');
	$this->addClass('lists', new lists());

	$colors['system'] = '#555';
	$colors['solr'] = '#080';
	$colors['psql'] = '#008';
	$colors['html'] = '#800';
	$colors['free'] = '#eee';


	$tot = disk_total_space('files/');
	$freeDiskSpace = $diskUsage['free']['total'] = disk_free_space('files/');
	$uds = $tot - $freeDiskSpace;

	$class = 'success';
	$wsp = ($uds/$tot)*100;
	if ($wsp > 70)
		$class = 'warning';
	if ($wsp > 90)
		$class = 'danger';

	$zs = $this->helper->fileSize($tot - $freeDiskSpace);
	$tot_str = $this->helper->fileSize($tot);


	$diskUsage['html'] = $this->helper->getDiscUsage('/var/www/html');
	$diskUsage['solr'] = $this->helper->getDiscUsage('/var/solr/data');


	$res = $this->psql->querySelect("SELECT d.datname as Name,  pg_catalog.pg_get_userbyid(d.datdba) as Owner,
		CASE WHEN pg_catalog.has_database_privilege(d.datname, 'CONNECT')
			THEN pg_catalog.pg_size_pretty(pg_catalog.pg_database_size(d.datname))
			ELSE 'No Access'
		END as Size
	FROM pg_catalog.pg_database d
		order by
		CASE WHEN pg_catalog.has_database_privilege(d.datname, 'CONNECT')
			THEN pg_catalog.pg_database_size(d.datname)
			ELSE NULL
		END desc -- nulls first
		LIMIT 20;");
	if (!empty($res))
		foreach ($res as $row) {
			$diskUsage['psql'][$row['name']] = $lastSize = $this->helper->convertToBytes($row['size']);
			@$diskUsage['psql']['total'] += $lastSize;
			}

	$diskUsageHarmonize = $diskUsage['html']['total'] + $diskUsage['psql']['total'] + $diskUsage['solr']['total'];
	$diskUsage['system']['total'] = $uds - $diskUsageHarmonize;

	$lp = 0;
	foreach ($colors as $area=>$color) {
		$values = $diskUsage[$area];
		$lp++;
		$gloablStat[$area] = [
				'uid' => 'space_'.$area,
				'title' => $area.' '.$this->helper->fileSize($values['total']),
				'count' => $values['total'],
				'color' => $color,
				'index' => $area,
				];
		unset($diskUsage[$area]['total']);		
		arsort($diskUsage[$area]);
		}

		
	$detailsPanels = ['solr'=>'Solr', 'psql'=>'PSQL', 'html'=>'HTML'];	
	$this->helper->useFileSizeFormat = true;
	foreach ($detailsPanels as $area => $panelName) 
		if (!empty($diskUsage[$area])) {
			$nstat = [];
			$lp = 0;
			foreach ($diskUsage[$area] as $folderName=>$folderValue) {
				$lp++;
				$nstat[$folderName] = [
					'uid' => uniqid(),
					'label' => $folderName,
					'label_o' => $folderName,
					'count' => $folderValue,
					'link' 	=> '',
					'color' => $this->helper->getGraphColor($lp),
					'index' => $folderName,
					];
				}
			$detailsGraph[$area] = $this->helper->drawStatBoxAdvaced($nstat, count($nstat), );
			$this->addJS("drawLine('".uniqid()."','progressPart{$area}', 'panel_{$area}', '{$colors[$area]}');");
			}



	$blocks = '
		<p style="height:50px;"  id="ropesBlock"></p>
		<div class="row">
			<div class="col-sm-4">
			'.$this->helper->panelSimple("Solr:".$detailsGraph['solr'], 'default', 'style="border-color:'.$colors['solr'].';" id="panel_solr"').'
			</div>
			<div class="col-sm-4">
			'.$this->helper->panelSimple("PSQL:".$detailsGraph['psql'], 'default', 'style="border-color:'.$colors['psql'].';" id="panel_psql"').'
			</div>
			<div class="col-sm-4">
			'.$this->helper->panelSimple("HTML:".$detailsGraph['html'], 'default', 'style="border-color:'.$colors['html'].';" id="panel_html"').'
			</div>
		</div>
			';


	$TRESC = $this->helper->alert($class, 
				"Used disk space: <b>$zs</b> of <b>$tot_str</b> total disk space. <br>
				Harmonize soft+data use: <b>{$this->helper->fileSize($diskUsageHarmonize)}</b><br>
				<div class='large'>".$this->helper->progressThinMulti($gloablStat,100).'</div>'.
				$blocks
				);

	echo $TRESC;
	
	
	
	$res = $this->psql->querySelect("SELECT 
				tablename as name, 
				pg_size_pretty(pg_relation_size(format('%I.%I', schemaname, tablename))) AS size 
			FROM 
				pg_catalog.pg_tables 
			WHERE 
				schemaname = 'public'
			ORDER BY pg_relation_size(format('%I.%I', schemaname, tablename)) DESC;");
	if (!empty($res))
		foreach ($res as $row) {
			$tablesSize[$row['name']] = $lastSize = $this->helper->convertToBytes($row['size']);
			}
			
			
	arsort($tablesSize);
	#echo $this->helper->pre($res);
	
	$this->lists->dataColumns = array (
			array( 'class' => 'technical right',	'function' => 'counter',	'title' => $this->transEsc('No.') ),
			array( 'class' => 'major',				'field' => 'name', 			'title' => $this->transEsc('Table name') ), 
			array( 'class' => 'technical right',	'field' => 'size', 			'title' => $this->transEsc('Disk space used') ), 
			array( 'class' => 'major',				'field' => 'string', 		'title' => $this->transEsc('Graph') ), 
			array( 'class' => 'actions', 			'field' => 'actions',		'title' => $this->transEsc('Actions') )
			);	 
		 
	$total = count($res);
	
	$max 			= $this->lists -> SetMax(50);
	$subPages 		= $this->lists -> subPages ($total);
	$tableHeaders 	= $this->lists -> headers ();
	$lp				= $this->lists -> startPoint;
	$sort			= $this->lists -> sorting;

	$i = 0;
	if (is_array($res))
		foreach ($res as $k=>$row) {
			$i++;
			$rowOnClick = "page.postInModal('{$this->transEsc('Matching summary')}', 'service/data/matching.results.edit/$k', '$k');";
			$row['actions'] ='
					<button class="table-list-btn" onClick="'.$rowOnClick.'" title="'.$this->transEsc('Create rule').'"><i class="ph ph-pencil"></i></button>
					';
			$res[$k] = $row;
			}
	$this->lists->onmouseover = '';	// we can add for row on mouse over function 
	$this->lists->onclick = ''; 	// we can add for row on mouse click function 
	$tableContent	= $this->lists -> content($res);
	
	
	echo '
		<div class="row">
			<div class="col-sm-6">
				<div class="panel panel-default">
					<div class="panel-body">
						<table class="table table-hover table-lists">
						'.$tableHeaders.'
						'.$tableContent.'
						</table>
					</div>
				</div>
			</div>
		</div>
		';
	
	
	
	}


#echo $this->helper->pre($_SERVER);
?>
<script>
function drawLine(uid, from, to, color = '#ddd') {
    // Dodanie nowego elementu do #ropesBlock
    $('#ropesBlock').append('<div id="rope_' + uid + '" class="rope"></div>');

    // Pobieranie współrzędnych #ropesBlock
    let globalCorrect = {
        x: $('#ropesBlock').offset().left-16,
        y: $('#ropesBlock').offset().top-80
    };

    // Pobieranie elementów
    let element1 = $('#' + from);
    let element2 = $('#' + to);

    // Pozycja środka spodu elementu1
    let element1Pos = {
        x: element1.offset().left + element1.outerWidth() / 2,
        y: element1.offset().top + element1.outerHeight()
    };

    // Pozycja środka góry elementu2
    let element2Pos = {
        x: element2.offset().left + element2.outerWidth() / 2,
        y: element2.offset().top
    };

    // Przesunięcie o globalCorrect
    element1Pos.x -= globalCorrect.x;
    element1Pos.y -= globalCorrect.y;
    element2Pos.x -= globalCorrect.x;
    element2Pos.y -= globalCorrect.y;

    // Obliczanie odległości i kąta między punktami
    let distance = Math.sqrt(Math.pow(element2Pos.x - element1Pos.x, 2) + Math.pow(element2Pos.y - element1Pos.y, 2))+5;
    let angle = Math.atan2(element2Pos.y - element1Pos.y, element2Pos.x - element1Pos.x) * (180 / Math.PI);

    // Ustawianie stylów CSS dla linii
    $('#rope_' + uid).css({
        position: 'absolute',
        transform: 'rotate(' + angle + 'deg)',
        width: distance + 'px',
        background: color,
        top: element1Pos.y + 'px',
        left: element1Pos.x + 'px',
        height: '1px', // Możesz zmienić wysokość linii według potrzeb
        transformOrigin: '0 0' // Punkt początkowy transformacji
    });
}
</script>