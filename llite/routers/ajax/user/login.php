<?php
if (empty($this)) die;
if (empty($this->user->cmsKey)) {
	
	echo $this->alert("danger", $this->transEsc("Your browser does not allow cookies to be saved. Logging in will not be possible."));
	
	} else {
	
	if (!empty($this->POST['code']) && $this->user->checkLogIn($this->POST, $alert)) {
		echo $this->helper->loader2();
		echo "<script>location.reload();</script>";
		} else {
		if (!empty($alert) && is_array($alert)) {
			foreach ($alert as $k=>$v)
				$alert[$k] = $this->transEsc($v);
			echo $this->helper->alert('danger', implode('<Br>', $alert));
			}
		}
	
	
	echo '<form method="post" class="form-login" action="'.$this->selfUrl().'">
				<div class="panel panel-success">
					<div class="panel-heading">'.$this->transEsc('Login').'</div>
					<div class="panel-body">
						<input type="hidden" id="vcode" name="vcode" value="'.$this->user->cmsKey.'">
						<label class="sr-only" for="LogInLogin">'.$this->transEsc('Type your e-mail').'</label>
						<input type="text" class="libri-forms" id="LogInLogin" placeholder="'.$this->transEsc('Type your e-mail').'" value="'.$this->postParam("login").'">
						<label class="sr-only" for="LogInLogin">'.$this->transEsc('Password').'</label>
						<input type="password" class="libri-forms" id="LogInPass" placeholder="'.$this->transEsc('Password').'" value="'.$this->postParam("pass").'">

						<div class="btn-group">
							<button class="btn btn-success" type="button" id="LogInButton" OnClick="user.LogIn($(\'#LogInLogin\').val(), $(\'#LogInPass\').val() );"><i class="fa fa-sign-in" aria-hidden="true"></i> '.$this->transEsc('Login').'</button>
						</div>
					</div>
					
				</div>
			</form>';
	}

	# echo $this->helper->panelCollapse(uniqid(),"POST","<prE>".print_R($this->POST,1)."</pre>",'',false);
	# echo $this->helper->panelCollapse(uniqid(),"_SERVER","<prE>".print_R($_SERVER,1)."</pre>",'',false);

	
	
?>