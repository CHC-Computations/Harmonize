<?php
require_once('functions/class.bookcart.php');

$this->addClass('solr', 	new solr($this)); 
$this->addClass('bookcart',	new bookcart());

#echo $this->helper->pre($this->POST);
if (empty($this->routeParam[1])) {

	echo '<hr/>';
	foreach ($this->POST['pdata'] as $collactionNumber => $collationParams) {
		$sstring = $collationParams['value'];
		$collationParams = explode('|', str_replace('"','', $sstring));
		#echo $this->transEsc('working with').'<br/> user_id: <b>'.$collationParams[0].'</b>,<br/> id: <b>'.$collationParams[1].'</b><br/><br/>';
		
		$listDetails = $this->bookcart->getListDetails($collationParams[1]);
		if (!empty($listDetails['list_name']))
			echo '<h4>'.$listDetails['list_name'].'</h4>'.$listDetails['list_description']??'';
		
		echo '<div id="progressArea"></div>';
		$listItems = $this->bookcart->getListSQLItems($collationParams[1]);
		if (is_array($listItems)) {
			foreach ($listItems as $rec) {
				$items[] = $rec['rec_id'];
				}
			}
		$this->addJS("page.post('progressArea', 'user/lists/restore.collection/{$collationParams[0]}/{$collationParams[1]}/0', ".json_encode($items).");");
		}
	} else {
		
	# echo $this->helper->pre($this->routeParam);	
	# echo $this->helper->pre($this->POST);	
	$user_id = $this->routeParam[0];
	$list_id = $this->routeParam[1];
	$key = $this->routeParam[2];
	
	$items = $this->POST['pdata'];
	
	$this->bookcart->addToSolr($items[$key], $list_id, $user_id);
	$key++;
	echo $this->helper->progress($key, count($items), $klasa='primary');	
	if ($key + 1 >= count($items)) {
		$this->addJS('location.reload();');
		} else {
		$this->addJS("page.post('progressArea', 'user/lists/restore.collection/{$user_id}/{$list_id}/{$key}', ".json_encode($items).");");
		}
	}

?>