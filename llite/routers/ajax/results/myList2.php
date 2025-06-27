<?php 
if (empty($this)) die;
$id = $this->routeParam[0];


echo $this->helper->pre($this->routeParam);

if (empty($_SESSION['results'])) 
	$_SESSION['results']['mylist'] = [];
		
if (array_key_exists($id, $_SESSION['results']['mylist'])) {
	$myClass = "ph-square-bold";
	$reClass = "ph-check-square-bold";
	unset($_SESSION['results']['mylist'][$id]);
	$JS = "page.myInfoCloud('".$this->transEsc('Resoult removed from book list')."', 1000); ";
	} else {
	$myClass = "ph-check-square-bold";
	$reClass = "ph-square-bold";
	$_SESSION['results']['mylist'][$id] = $id;
	$JS = "page.myInfoCloud('".$this->transEsc('Resoult added to book list')."', 1000); ";
	}
	
$myListCount = count($_SESSION['results']['mylist']);
	
#echo "$id";

$checkID = str_replace('.','_', $id);

$jscript =  "
	$('#myListCount').html('{$myListCount}'); 
	$('#ch_{$checkID}').removeClass('$reClass');
	$('#ch_{$checkID}').addClass('$myClass');
	$JS 
	
	var color = $('#userMenu-icon').css('color');
	$('#userMenu-icon').css('text-shadow','0px 0px 20px black');
	
	setTimeout(function(){ 
		$('#userMenu-icon').css('text-shadow','none');
		}, 900);
	
	";

$this->addJS($jscript);

$active = 'will be here, soon';


?>

<div class="panel panel-active">
	<div class="panel-heading"><?= $this->transEsc('Your lists') ?></div>
	<div class="panel-body">
		<?= $active ?>
	</div>
</div>
