<h1><?= $this->transEsc('System monitor') ?></h1>
<?php 



$tot = disk_total_space('files/');
$fsb = disk_free_space('files/');
$uds = $tot - $fsb;

$class = 'success';
$wsp = ($uds/$tot)*100;
if ($wsp > 70)
	$class = 'warning';
if ($wsp > 90)
	$class = 'danger';

$zs = $this->helper->fileSize($tot - $fsb);
$tot_str = $this->helper->fileSize($tot);

$TRESC = $this->helper->alert($class,$this->helper->percent($uds,$tot,$class)." Used disk space: <b>$zs</b> of <b>$tot_str</b> total disk space. <br>");






echo $TRESC;
echo "<div id='recInSQL'></div>";
# $this->JS[] = "service.recCounter('recInSQL');";
#$load = sys_getloadavg();
#echo "<pre>".print_r($load,1).'</pre>';
?>