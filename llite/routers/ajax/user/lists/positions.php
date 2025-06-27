<?php 
if (empty($this)) die;
if ($this->user->isLoggedIn()) {
	require_once('./functions/class.bookcart.php');
	require_once('./functions/class.solr.php');
	require_once('./functions/class.buffer.php');
	$this->addClass('solr', new solr($this));
	$this->addClass('buffer', new buffer($this));
	$this->addClass('bookcart', new bookcart);

	$user_id = $this->user->full()->email;
			

	if (count($this->routeParam)>1) {
		#echo $this->helper->pre($this->routeParam);
		$record_id = $id = $this->routeParam[0];
		$record_field = str_replace('.','_',$record_id);
		$list_id = $this->routeParam[1];
		if ($id !== 'undefined') {
			$resultAction = $this->bookcart->addOrRemove($record_id, $list_id, $user_id);
			if ($resultAction == 'add')
				$this->addJS("page.myInfoCloud('".$this->transEsc('Result added to book list')."', 1000);");
			if ($resultAction == 'remove')
				$this->addJS("page.myInfoCloud('".$this->transEsc('Result removed from book list')."', 1000);");
			
			$result = (object)['id' => $record_id];
			echo '<div class="hidden" id="toSwitch">'.$this->bookcart->resultCheckBox($result).'</div>'; 
			$this->addJS("
				var toSwitch = $('#toSwitch').html();
				$('#check_{$record_field}').html(toSwitch);
				");
			}
		}

	$t = $this->psql->querySelect("SELECT b.list_name, b.list_ico, a.list_id, count(*) FROM users_lists_positions a 
				JOIN users_lists b ON a.list_id = b.id
				WHERE b.user_id = {$this->psql->string($user_id)}
				GROUP BY b.list_name, b.list_ico, a.list_id
				ORDER BY b.list_name;
				");
	if (is_Array($t)) {
		foreach ($t as $row) { 
			$key = '';
			$key = $this->buffer->createFacetsCode(["user_list:\"{$user_id}|{$row['list_id']}\""]);
			$position[] = '<a href="'.$this->buildUri('results', array_merge($this->GET, ['core'=>'biblio', 'facetsCode'=>$key])).'" id="facetBase'.$key.'" class="facet js-facet-item" data-title="'.$row['list_name'].'" >
							<span class="text"> <i class="ph '.$row['list_ico'].'"></i> '.$row['list_name'].'</span>
							<span class="badge">'.$this->helper->numberFormat($row['count']).'</span>
						  </a>';
			}
		$positions = implode('', $position);		
		
		/*
		echo '<div class="panel panel-userlist">
				<div class="panel-heading">
					'. $this->transEsc('Your lists') .'
					<a class="facet-btn btn-white" rel="nofollow" title="'. $this->transEsc('Manage lists') .'" OnClick="page.postInModal(\''. $this->transEsc('User lists') .'\', \'user/lists/manager\');">
						<i class="ph ph-faders"></i>
					</a>
				</div>
				<div class="panel-body"> 
					'. $positions .'
					
				</div>
			</div>
			';
		*/
		$panelId = 'userListPanel';
		echo $this->helper->PanelCollapse(
			$panelId,
			$this->transEsc('Your lists').'
					<a class="facet-btn btn-white" rel="nofollow" title="'. $this->transEsc('Manage lists') .'" OnClick="page.postInModal(\''. $this->transEsc('User lists') .'\', \'user/lists/manager\');">
						<i class="ph ph-faders"></i>
					</a>
					',
			$positions ,
			'', true, 'userlist'
			);
		}

}



?>

