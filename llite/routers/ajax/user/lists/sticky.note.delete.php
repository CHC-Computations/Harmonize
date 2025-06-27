<?php
if (empty($this)) die();
if ($this->user->isLoggedIn()) {
	
	$user_id = $this->user->full()->email;
	$rec_id = $this->routeParam[0];
	$record_field = str_replace('.','_', $rec_id);
	
	$Q = "DELETE FROM users_sticky_notes WHERE user_id = {$this->psql->string($user_id)} AND rec_id = {$this->psql->string($rec_id)};";
	$this->psql->query($Q);
	$this->addJS("$('#stickyArea{$record_field}').html('');");
		
		
	}


?>