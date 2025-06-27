<?php 

if (!empty($this->POST['from_name'])) {
	switch ($this->POST['from_name']) {
		case 'error.report' : 
				if ($this->POST['message'] == $this->POST['field_tracking']) {
					$ip = ip2long($_SERVER['REMOTE_ADDR']);
					$name = $this->POST['name'] ?? '';
					$mail = $this->POST['mail'] ?? '';
					$message = $this->POST['message'] ?? '';
					$url = $this->POST['selfUrl'] ?? '';
					$user_agent_id = $this->user->getUserAgentId();
					$user_lang = $this->userLang;
					$user_key = $this->user->cmsKey ?? '';
					$id = $this->psql->nextVal('error_report_id_seq');
					
					$this->psql->query("
							INSERT INTO error_report 
								(id, time, ip, name, mail, message, status, type, user_lang, user_key, url, user_agent_id) 
								VALUES 
								('$id', 
								now(), 
								'$ip', 
								{$this->psql->string($name)}, 
								{$this->psql->string($mail)}, 
								{$this->psql->string($message)}, 
								NULL,
								NULL,
								{$this->psql->string($user_lang)}, 
								{$this->psql->string($user_key)}, 
								{$this->psql->string($url)}, 
								'$user_agent_id');
							");
					$this->addSuccess($this->transEsc('note_2'));
					} else 
					$this->addWarning('Message contains forbidden notations');
				#$this->addSuccess($this->helper->pre($this->POST).$this->user->cmsKey);
				
				break;
		}
	}
	
?>