<?php 
if (empty($this)) die;
$ids = explode(',',$this->routeParam[0]);

if (empty($_SESSION['results'])) 
	$_SESSION['results']['mylist'] = [];


if (is_Array($ids)) {		
	foreach ($ids as $id) {		
		if (array_key_exists($id, $_SESSION['results']['mylist'])) {
			$myClass = "ph-square-bold";
			$reClass = "ph-check-square-bold";
			unset($_SESSION['results']['mylist'][$id]);
			} else {
			$myClass = "ph-check-square-bold";
			$reClass = "ph-square-bold";
			$_SESSION['results']['mylist'][$id] = $id;
			}
		$checkId = str_replace('.', '_', $id);	
		$JS[] = "
		$('#ch_{$checkId}').removeClass('$reClass');
		$('#ch_{$checkId}').addClass('$myClass');
		";
			
		}

	$myListCount = count($_SESSION['results']['mylist']);
	$jscript =  "
		$('#selectAllIcon').css({'transform':'rotate(160deg)'});
		$('#myListCount').html('{$myListCount}'); 
		page.myInfoCloud('".$this->transEsc('Resoults added/removed from book list')."', 1000); 
		".implode("\n",$JS)."
		var color = $('#userMenu-icon').css('color');
		$('#userMenu-icon').css('text-shadow','0px 0px 20px black');
		
		setTimeout(function(){ 
			$('#userMenu-icon').css('text-shadow','none');
			$('#selectAllIcon').css({'transform':'rotate(360deg)'});
			}, 900);
		
		";

	$this->addJS($jscript);
	#echo "<pre>".$jscript."</pre>";
	}

?>