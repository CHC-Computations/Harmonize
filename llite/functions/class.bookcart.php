<?php
require_once('./functions/class.cms.php');


class bookcart {
	
	private $cms;
	
	public $listContent = [];
	public $listTotal = 0;
	
	function __construct() {
		}

	public function register($name, $var) {
		$this->$name = $var;
		}

	public function addOrRemove($rec_id, $list_id, $user_id) {
		$list_id = intval($list_id);
		if ($list_id == 0) return false;
		
		
		$data = (object) ["id" => $rec_id];
		$t = $this->cms->psql->querySelect("SELECT * FROM users_lists_positions WHERE rec_id = {$this->cms->psql->string($rec_id)} AND list_id = '$list_id';");
		if (is_array($t)) {
			$this->cms->psql->query("DELETE FROM users_lists_positions WHERE rec_id = {$this->cms->psql->string($rec_id)} AND list_id = '$list_id';");
			$data->user_list = 	(object) ["remove" => $user_id.'|'.$list_id];
			
			$action = 'remove';
			} else {
			$this->cms->psql->query("INSERT INTO users_lists_positions (rec_id, list_id) VALUES ({$this->cms->psql->string($rec_id)}, '$list_id');");
			$data->user_list = 	(object) ["add" => $user_id.'|'.$list_id];
			
			$action = 'add';
			}
		$this->cms->solr->curlSaveData('biblio', $data);
		
		#echo $this->cms->helper->pre($this->cms->solr->curlSavePostData);
		#echo $this->cms->helper->pre($this->cms->solr->curlSaveResponse);
		
		$this->cms->solr->curlCommit('biblio');
		return $action;
		}
	
	public function addToSolr($rec_id, $list_id, $user_id) {
		$list_id = intval($list_id);
		if ($list_id == 0) return false;
		
		
		$data = (object) ["id" => $rec_id];
		$data->user_list = 	(object) ["add" => $user_id.'|'.$list_id];
		$this->cms->solr->curlSaveData('biblio', $data);
		$this->cms->solr->curlCommit('biblio');
		return $this->cms->solr->curlSaveResponse;
		}
	
	
	public function addToCart($id) {
		if (empty($_SESSION['results'])) 
			$_SESSION['results']['mylist'] = [];

		$_SESSION['results']['mylist'][$id] = $id;
		
		$this->listContent = $_SESSION['results']['mylist'];
		$this->listTotal = count($_SESSION['results']['mylist']);
		}
	
	public function removeFromCart($id) {
		if (!empty($_SESSION['results'])) 
			if (array_key_exists($id, $_SESSION['results']['mylist'])) 
				unset($_SESSION['results']['mylist'][$id]);
			
		$this->listContent = $_SESSION['results']['mylist'];
		$this->listTotal = count($_SESSION['results']['mylist']);
		}
	
	public function cartTotal() {
		return $this->listTotal ?? 0;
		}
	
	public function getCart() {
		return $this->listContent ?? [];
		}
	
	
	public function isOnMyLists($id) {
		if (!empty($_SESSION['results']) && is_Array($_SESSION['results']))
			foreach ($_SESSION['results'] as $listName=>$arr)
				if (array_key_exists( $id, $arr ))
					$res[$listName] = $listName;
		if (!empty($res))
			return $res;
			else 
			return null;
		}
		
	public function getListDetails($list_id) {
		$t = $this->cms->psql->querySelect($Q = "SELECT * FROM users_lists WHERE id = '".intval($list_id)."'");
		if (is_array($t))
			return current($t);
		return [];
		}
	
	public function getListSQLItems($list_id) {
		$t = $this->cms->psql->querySelect($Q = "SELECT rec_id FROM users_lists_positions WHERE list_id = '".intval($list_id)."';");
		if (is_array($t))
			return $t;
		return [];
		}
	
	public function isOnLists($id, $user_id = '') {
		if ($this->cms->user->isLoggedIn()) {
			if (empty($user_id))
				$user_id = $this->cms->user->full()->email;
			$t = $this->cms->psql->querySelect("SELECT * FROM users_lists_positions a
					JOIN users_lists b ON a.list_id = b.id
					WHERE a.rec_id = {$this->cms->psql->string($id)} AND b.user_id = {$this->cms->psql->string($user_id)};");
			$return = [];
			if (is_array($t))
				foreach ($t as $row)
					$return[] = $row['list_id'];
			return $return;
			}
		}
	
	public function getMyStickyNotes() {
		if ($this->cms->user->isLoggedIn()) {
		
			$user_id = $this->cms->user->full()->email;
			$return = [];	
			$t = $this->cms->psql->querySelect($Q = "SELECT * FROM users_sticky_notes WHERE user_id = {$this->cms->psql->string($user_id)} ORDER BY time DESC;");	
			if (is_Array($t)) 
				foreach ($t as $row) {
					$result_id = $row['rec_id'];
					$box_id = str_replace('.','_', $result_id);
					$skickyOnClick = "page.postInModal('{$this->cms->transEsc('Sticky note')}', 'user/lists/sticky.note/{$result_id}');";	
					$skickyDeleteOnClick = "page.post('stickynote{$box_id}', 'user/lists/sticky.note.delete/{$result_id}');";	
			
					$return[$result_id] =  '
							<div class="sticky-note" id="stickynote'.$box_id.'">
							   <div class="bulkActionButtons">
								<div class="btn-group">
								<button class="dropdown-toggle" data-toggle="dropdown"><i class="ph ph-dots-three-vertical"></i></button>
									<ul class="dropdown-menu dropdown-menu-right">
										<li><a OnClick="'.$skickyOnClick.'"><i class="ph ph-pen"></i> '.$this->cms->transEsc('Edit').'</a></li>
										<li><a OnClick="'.$skickyDeleteOnClick.'"><i class="ph ph-trash"></i> '.$this->cms->transEsc('Delete').'</a></li>
									</ul>
								</div>
								<button><i class="ph ph-x" OnClick="$(\'#stickynote'.$box_id.'\').hide();" data-toggle="tooltip" title="'.$this->cms->transEsc('Close').'"></i></button>
							</div>
							<div class="sticky-note-header"><a href="'.$this->cms->basicUri('results/biblio/record/'.$result_id.'.html').'">'.$result_id.'</a></div>
							'.$row['stickynote'].'</div>';
					}
			return $return;
			}
		}
	
	
	public function resultStickyNote($result_id, $format = 'full') {
		if ($this->cms->user->isLoggedIn()) {
			$box_id = str_replace('.','_',$result_id);
			$user_id = $this->cms->user->full()->email;
			$skickyOnClick = "page.postInModal('{$this->cms->transEsc('Sticky note')}', 'user/lists/sticky.note/{$result_id}');";	
			$skickyDeleteOnClick = "page.post('stickynote{$box_id}', 'user/lists/sticky.note.delete/{$result_id}');";	
				
			$t = $this->cms->psql->querySelect($Q = "SELECT * FROM users_sticky_notes WHERE rec_id = {$this->cms->psql->string($result_id)} AND user_id = {$this->cms->psql->string($user_id)};");	
			if (is_Array($t)) {
				$sticky = current($t);
				if ($format == 'ico')
					return '<i class="ph ph-note" title="sticky"></i>';
				return '<div class="sticky-note" id="stickynote'.$box_id.'">'
						.'<div class="bulkActionButtons">
							<div class="btn-group">
							<button class="dropdown-toggle" data-toggle="dropdown"><i class="ph ph-dots-three-vertical"></i></button>
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a OnClick="'.$skickyOnClick.'"><i class="ph ph-pen"></i> '.$this->cms->transEsc('Edit').'</a></li>
									<li><a OnClick="'.$skickyDeleteOnClick.'"><i class="ph ph-trash"></i> '.$this->cms->transEsc('Delete').'</a></li>
								</ul>
							</div>
							<button><i class="ph ph-x" OnClick="$(\'#stickynote'.$box_id.'\').hide();" data-toggle="tooltip" title="'.$this->cms->transEsc('Close').'"></i></button>
						</div>'
						.$sticky['stickynote']
						.'</div>';
				}
			}
		}
	
	public function resultCheckBox($result) {
		if (is_string($result))
			$result = (object)['id' => $result];
		if (empty($_SESSION['results']) or !is_Array($_SESSION['results']))
			$_SESSION['results']['mylist'] = [];
		
		if (is_array($this->isOnMyLists($result->id))) {
			$myClass = "ph-check-square-bold";
			} else {
			$myClass = "ph-square-bold";
			}
		$box_id = str_replace('.','_',$result->id);
				
		if ($this->cms->user->isLoggedIn()) {
			$user_id = $this->cms->user->full()->email;
			$isOnLists = $this->isOnLists($result->id, $user_id);
			
			$manageOnClick = "page.postInModal('{$this->cms->transEsc('User lists')}', 'user/lists/manager');";	
			$skickyOnClick = "page.postInModal('{$this->cms->transEsc('Sticky note')}', 'user/lists/sticky.note/{$result->id}');";	
			$tagsOnClick = "page.postInModal('{$this->cms->transEsc('Tags')}', 'user/lists/tags/{$result->id}');";	
			$t = $this->cms->psql->querySelect($Q = "SELECT * FROM users_lists WHERE user_id = '{$user_id}'");
			$icon = '';
			if (is_Array($t)) {
				$rows = [];
				foreach ($t as $row) {
					$rowOnClick = "results.myList('{$result->id}', '$row[id]');";	
					$rows[$row['id']] = '<li><a OnClick="'.$rowOnClick.'"><i class="ph '.$row['list_ico'].'"></i> '.$row['list_name'].'</a></li>';
					$icons[$row['id']] = $row['list_ico'];
					$names[$row['id']] = $row['list_name'];
					}
				
				
				$icon = '';
				if (empty($isOnLists))
					$icon = '<i class="ph ph-square-bold"></i>';
					else foreach ($isOnLists as $list_id) {
					$rowsActive[$list_id] = $rows[$list_id];
					unset($rows[$list_id]);
					$icon.= '<i class="ph '.$icons[$list_id].'" title="'.$names[$list_id].'" data-toggle="tooltip"></i> ';
					}
				


				$options = '<ul class="dropdown-menu">';
				if (!empty($rows))
					$options.='	<li><span class="dropdown-header">'.$this->cms->transEsc("Add to list").':</span></li>'.implode('', $rows);
				if (!empty($rowsActive))
					$options.='	<li><span class="dropdown-header">'.$this->cms->transEsc("Remove from list").':</span></li>'.implode('', $rowsActive);
				$options .= '			
							<li class="divider"></li>
							<li><a OnClick="'.$manageOnClick.'">'.$this->cms->transEsc('Manage lists').'</a></li>
							<li class="divider"></li>
							<li><a OnClick="'.$skickyOnClick.'">'.$this->cms->transEsc('Add a sticky note').'</a></li>
							<li><a OnClick="'.$tagsOnClick.'">'.$this->cms->transEsc('Add tags').'</a></li>
						</ul>
						';
				
				} else {
				$options = '
						<ul class="dropdown-menu">
							<li><span class="dropdown-header">'.$this->cms->transEsc("You don't have your lists yet. Do you want to create your first list?").'</span></li>
							<li><a OnClick="'.$manageOnClick.'">'.$this->cms->transEsc('Create list').'</a></li>
							<li class="divider"></li>
							<li><a OnClick="'.$skickyOnClick.'">'.$this->cms->transEsc('Add a sticky note').'</a></li>
							<li><a OnClick="'.$tagsOnClick.'">'.$this->cms->transEsc('Add tags').'</a></li>
						</ul>
						';
				}
			
			
			
			
			$checkBox = '
				<div class="btn-group">
					<button class="toolbar-btn dropdown-toggle" data-toggle="dropdown" data-original-title="'.$this->cms->transEsc('Add to my list').'" aria-expanded="false">
						'.$icon.'
						<span class="hidden">'.$this->cms->transEsc('Add to my list').'</span>
					</button>
						'.$options.'	
				</div>	
				';	
			
			
			} else {
			$checkBox = '
				<div class="btn-group">
					<button class="toolbar-btn dropdown-toggle" data-toggle="dropdown" data-original-title="'.$this->cms->transEsc('Add to my list').'" aria-expanded="false">
						<i class="ph ph-square-bold"></i>
						<span class="hidden">'.$this->cms->transEsc('Add to my list').'</span>
					</button>
						<ul class="dropdown-menu">
						<li><span class="dropdown-header">'.$this->cms->transEsc('You must be logged in to create personal lists').'</span></li>
						<li><a href="'.$this->cms->buildUrl('user/login').'">'.$this->cms->transEsc('Login/register').'</a></li>
						</ul>			
				</div>	
				';	
			}
			
		return $checkBox;
		}
	
	
	}
	
?>