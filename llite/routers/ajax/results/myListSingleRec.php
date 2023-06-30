<?php 
if (empty($this)) die;
$id = $this->routeParam[0];

if (!is_Array($_SESSION['results'])) 
	$_SESSION['results']['mylist'] = [];
		
if (array_key_exists($id, $_SESSION['results']['mylist'])) {
	$inScript = "$('#ch_{$id}').removeClass('active');";
	$inScript.= "page.myInfoCloud('".$this->transEsc('Resoult removed from book list')."', 1000); ";
	unset($_SESSION['results']['mylist'][$id]);
	echo "<i class='fa fa-plus'></i> ".$this->transEsc('Add to Book Bag');
	} else {
	$inScript = "$('#ch_{$id}').addClass('active');";
	$inScript.= "page.myInfoCloud('".$this->transEsc('Resoult added to book list')."', 1000); ";
	$_SESSION['results']['mylist'][$id] = $id;
	echo "<i class='fa fa-minus'></i> ".$this->transEsc('Remove from Book Bag');
	}
	
$myListCount = count($_SESSION['results']['mylist']);
	
#echo "$id";



$jscript =  "
	$('#myListCount').html('{$myListCount}'); 
	{$inScript}
	
	$('#userMenu-icon').css('text-shadow','0px 0px 20px black');
	
	setTimeout(function(){ 
		$('#userMenu-icon').css('text-shadow','none');
		}, 900);
	
	";

$this->addJS($jscript);



?>