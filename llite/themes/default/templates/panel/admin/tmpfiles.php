<h1><?= $this->transEsc('Cleaning temporary files') ?></h1>
<?php 
$folders = [
		'jsonFiles' => 'files/json',
		'marcFiles' => 'files/marc',
		'markFiles' => 'files/mrk',
		'solrUpdate' => 'files/solr',
		'GroupExports' => 'files/exports',
		'dViaf' => 'files/downloaded/viaf',
		'dWikiHtml' => 'files/downloaded/wikidata/html',
		'dWikiJson' => 'files/downloaded/wikidata/json',
		'covers' => 'files/covers/',
		
		];

unset($_SESSION['jsonFolders']);

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

$checkClick = '';

echo $TRESC;

$foldStr = "<ul class='list-group'>";
$foldStr.= "<li class='list-group-item active'>There are folders with temporary files:</li>";
foreach ($folders as $k=>$v) {
	$foldStr.= "<li class='list-group-item' id='ajaxBox_$k'>$k => $v</li>";
	#$this->addJS("page.ajax('ajaxBox_$k', 'service/checkfolder/$k/$v');");
	$checkClick .="page.ajax('ajaxBox_$k', 'service/checkfolder/$k/$v');";
	}
$foldStr.= "</ul>";
echo "<button class='btn btn-default' OnClick=\"$checkClick\">Check folders</button><br/><br/>";
echo $foldStr;

echo "<div id='recInSQL'></div>";
# $this->JS[] = "service.recCounter('recInSQL');";

?>