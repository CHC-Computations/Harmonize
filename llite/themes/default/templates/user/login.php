<?php 

$this->JS[] = "user.LogIn();";
#$this->JS[] = "user.register();";

?>


<?php if (empty($this->user->LoggedIn)): ?>
<div class="main">
<div class="container">
	<div class="pause" style="margin-top:100px;"></div>
	<div class="row">
		<div class="col-sm-6" id="logInBox">
			<?= $this->helper->loader($this->transEsc('please wait')) ?>
			
		</div>
		
		<div class="col-sm-1 text-center">
			<?= $this->transEsc('or') ?>
		</div>
		<div class="col-sm-4" id="registerBox">
			
			<div id="g_id_onload"
				data-client_id="155693132718-7atp3qoftr060fofm15166anq140mcl8.apps.googleusercontent.com"
				data-login_uri="<?=$this->selfUrl()?>"
				data-auto_prompt="false">
			</div>
			<div class="g_id_signin"
				data-type="standard"
				data-size="large"
				data-theme="outline"
				data-text="sign_in_with"
				data-shape="rectangular"
				data-logo_alignment="left">
			</div>
			<script>
					function handleCredentialResponse(response) {
					  console.log("Encoded JWT ID token: " + response.credential);
					}
					window.onload = function () {
					  google.accounts.id.initialize({
						client_id: "155693132718-7atp3qoftr060fofm15166anq140mcl8.apps.googleusercontent.com",
						callback: handleCredentialResponse
					  });
					  google.accounts.id.renderButton(
						document.getElementById("buttonDiv"),
						{ theme: "outline", size: "large" }  // customization attributes
					  );
					  google.accounts.id.prompt(); // also display the One Tap dialog
					}
				</script>
				<div id="buttonDiv"></div>
			
			
		</div>
	</div>
</div>
</div>
<?php else: ?>

<?= $this->render('user/userAccount.php', ['user' => $this->user->LoggedIn] ) ?>	
	
<?php endif; ?>

<?php 
/*
155693132718-7atp3qoftr060fofm15166anq140mcl8.apps.googleusercontent.com
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
*/
?>