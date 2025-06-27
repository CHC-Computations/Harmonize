<?php
if (empty($this)) die();
if ($this->user->isLoggedIn()) {
	$error = false;
	$this->addJS("$('#field_list_name').css('outline','none');");
		
	# echo $this->helper->pre($this->POST);

	foreach ($this->POST as $k=>$v)
		$$k = $v;

	if (empty($list_name)) {
		echo $this->transEsc('The list must have a name');
		$this->addJS("$('#field_list_name').css('outline','solid 3px red');");
		$error = true;
		}

	if (!$error) 
		if (empty($id)) {
			$id = $this->psql->nextVal('users_lists_id_seq');
			$Q = "INSERT INTO users_lists (id, user_id, list_name, list_ico, list_description, is_public) 
				VALUES ('$id', {$this->psql->string($this->user->full()->email)}, {$this->psql->string($list_name)}, {$this->psql->string($list_ico)}, {$this->psql->string($list_description)}, false);";
			
			$this->psql->query($Q);
			echo $this->transEsc('Creating new list').'.';
			
			
			} else {
			$id = intval($id);
			$Q = "UPDATE users_lists SET list_name={$this->psql->string($list_name)}, list_ico={$this->psql->string($list_ico)}, list_description={$this->psql->string($list_description)}
					WHERE id = '$id';";
			$this->psql->query($Q);
			echo $this->transEsc('List updated').'.';
			}

		
	$saveBtnBase64 = base64_encode($this->POST['saveBtn']);
	$this->addJS("page.clearForm('userListsAdd');"); // TODO! something is wrong here!
	$this->addJS("page.post('user_lists_table', 'user/lists/table');");
	$this->addJS("$('#buttonArea').html(window.atob('".$saveBtnBase64."'));");

	$closeBtnBase64 = base64_encode('<a href="'.$this->POST['reLoadLink'].'" class="btn btn-warning">'.$this->transEsc('Close & reload').'</a>');
	$this->addJS("$('.modal-footer').html(window.atob('".$closeBtnBase64."'));");

	}


?>