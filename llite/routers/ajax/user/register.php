<?php
if (empty($this)) die;
require_once ('functions/klasa.forms.php');
$this->addClass('forms', new forms());


echo $this->helper->alertIco("info", "ph-smiley-sad-bold", $this->transEsc("New user registration is temporarily (we hope) unavailable")."!");

/*
if (empty($this->user->cmsKey)) {
	
	echo $this->alert("danger", $this->transEsc("Your browser does not allow cookies to be saved. Logging in will not be possible."));
	
	} else {
	

	$options = [ 
		'id' => 'account',
		'class' => 'libri-forms',
		'required' => 'required'
		];
		
		
	$fields = [
		'username' => 	['label'=>$this->transEsc('User name'), 	'type'=>'text'],
		'email' => 		['label'=>$this->transEsc('e-Mail'), 		'type'=>'email'],
		'password' =>	['label'=>$this->transEsc('Password'), 		'type'=>'password'],
		'repassword' =>	['label'=>$this->transEsc('Confirm password'), 'type'=>'password'],
		];
	
	if (!empty($this->POST) && is_array($this->POST)) {
		$this->forms->values($this->POST);

		foreach ($fields as $k=>$f) 
			if (empty($this->POST[$k]))
				$alert[] = "The $f[label] field is required.";
			
		if (!empty($this->POST['password']) && ($this->POST['password']<>$this->POST['repassword']) ) 
			$alert[] = $this->transEsc("Password and confirmed password seem not to be the same!");


		if (empty($alert) && ($this->user->checkUserName($this->POST['username'], $alert)) && ($this->user->checkEmail($this->POST['email'], $alert)) ) {
			
			$this->user->saveUser($this->POST);
			echo $this->helper->alert('success', $this->transEsc("Saving").'...' );
			
			
			}

		if (!empty($alert))
			echo $this->helper->alert('danger', implode('<br/>', $alert));
			
		
		}	
		
		
		
		
	echo $this->render('user/register.php', [
			'fields' => $fields,
			'options' => $options
			]);
	}
*/

?>