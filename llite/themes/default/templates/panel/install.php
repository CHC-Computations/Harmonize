<?php 

$memory_limit = ini_get ('memory_limit');


function convert($size) {
    $unit=array('B','K','M','G','T','P');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),0).' '.$unit[$i];
	}
	
	
$tot = disk_total_space('files/');
	$freeDiskSpace = $diskUsage['free']['total'] = disk_free_space('files/');
	$uds = $tot - $freeDiskSpace;
	

?>


	<h3>Environment</h3>
	<div class="panel panel-default">
		<div class="panel-body">
			<div class="row">
				<div class="col-sm-6">
					<table class="table table-hover">
						<thead>
							<tr> 
								<td class="text-right">PHP version:</td><td><b><?= $status->php['version'] ?></b></td><td class="text-success"><i class="ph ph-check"></i></td>
							</tr>	
						</thead>
						<tbody>
							<tr> 
								<td class="text-right">Memory limit:</td><td><b><?= $memory_limit ?></b></td><td class="text-success"><i class="ph ph-check"></i></td>
							</tr>	
							<tr> 
								<td class="text-right">Memory usage:</td><td><b><?= convert(memory_get_usage()) ?></b></td><td class="text-success"><i class="ph ph-check"></i></td>
							</tr>	
							<tr> 
								<td class="text-right">Disc space avaible:</td><td><b><?= convert($uds) ?></b></td><td class="text-success"><i class="ph ph-check"></i></td>
							</tr>	
							</tbody>
					</table>	
			
				</div>
			</div>
		</div>
	</div>

	<h3>SQL</h3>
	<div class="panel panel-default">
		<div class="panel-body">
			<td class="text-right">SQL:</td><td><b><?= $status->SQL['version'] ?></b></td><td class="text-success"><i class="ph ph-check"></i></td>
		</div>
	</div>

	<h3>Solr</h3>
	<div class="panel panel-default">
		<div class="panel-body">
		</div>
	</div>
