<?php
if (empty($this)) die();
if ($this->user->isLoggedIn()) {
	require_once('./functions/class.bookcart.php');
	$this->addClass('bookcart', new bookcart);

	$error = false;
		
	foreach ($this->POST as $k=>$v)
		$$k = $v;

	if (empty($stickynote)) {
		echo $this->helper->alert('danger', $this->transEsc('Sticky note is empty. Nothing to save.'));
		$error = true;
		}
	$user_id = $this->user->full()->email;
	
	if (!$error) {
		$t = $this->psql->querySelect("SELECT * FROM users_sticky_notes WHERE user_id = {$this->psql->string($user_id)} AND rec_id = {$this->psql->string($rec_id)};");
		if (!is_array($t)) {
			$Q = "INSERT INTO users_sticky_notes (rec_id, user_id, stickynote, time) 
				VALUES ({$this->psql->string($rec_id)}, {$this->psql->string($user_id)}, {$this->psql->string($stickynote)}, now());";
			$this->psql->query($Q);
			echo $this->helper->alert('success', $this->transEsc('Sticky saved').'.');

			
			} else {
			$Q = "UPDATE users_sticky_notes SET stickynote={$this->psql->string($stickynote)}, time=now() WHERE user_id = {$this->psql->string($user_id)} AND rec_id = {$this->psql->string($rec_id)};";
			$this->psql->query($Q);
			echo $this->helper->alert('success', $this->transEsc('Sticky updated').'.');
			}
		
		$result = (object)['id'=>$rec_id];
		echo '<div class="hidden" id="toSwitch">'.$this->bookcart->resultStickyNote($rec_id).'</div>'; 
		$record_field = str_replace('.','_', $rec_id);
		$this->addJS("
			var toSwitch = $('#toSwitch').html();
			$('#stickyArea{$record_field}').html(toSwitch);
			");
		
		}
		
	$saveBtnBase64 = base64_encode($this->POST['saveBtn']);
	$this->addJS("$('#buttonArea').html(window.atob('".$saveBtnBase64."'));");

	}


?>