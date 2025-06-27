<?php 
$this->user->newGoogleLogin();


#$this->JS[] = "user.LogIn();";
#$this->JS[] = "user.register();";

/*
$datagiven example content: 
stdClass Object
(
    [iss] => https://accounts.google.com
    [azp] => 155693132718-7atp3qoftr060fofm15166anq140mcl8.apps.googleusercontent.com
    [aud] => 155693132718-7atp3qoftr060fofm15166anq140mcl8.apps.googleusercontent.com
    [sub] => 108481942056956393175
    [email] => giersz.marcin@gmail.com
    [email_verified] => 1
    [nbf] => 1718092861
    [name] => Marcin Giersz
    [picture] => https://lh3.googleusercontent.com/a/ACg8ocLDzTyICQ6L3aropeoZYJiYbbRhWgYIJWW5EjliA1_H8birw6s=s96-c
    [given_name] => Marcin
    [family_name] => Giersz
    [iat] => 1718093161
    [exp] => 1718096761
    [jti] => bd2da72c24d8a1cef7519c82214020048e0d3523
)


'155693132718-7atp3qoftr060fofm15166anq140mcl8.apps.googleusercontent.com'
*/
$loggedIn = $this->user->isLoggedIn();

?>

<div class="main">
<div class="container">
<?php if (!$loggedIn): ?>

	<div class="pause" style="margin-top:100px;"></div>
	
	
	<div class="row">
		<div class="col-sm-4">
			<h3><?= $this->transEsc('Login or register using')?>:</h3>
	
			
			
			<div id="g_id_onload" id="buttonDiv"
				data-client_id="<?= GOOGLE_ID ?>"
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
					  location.reload(); 
					}
					window.onload = function () {
					  google.accounts.id.initialize({
						client_id: "<?= GOOGLE_ID ?>",
						callback: handleCredentialResponse
					  });
					  google.accounts.id.renderButton(
						document.getElementById("buttonDiv"),
						{ theme: "outline", size: "large" }  // customization attributes
					  );
					  //google.accounts.id.prompt(); // also display the One Tap dialog
					}
			</script>
			<br/>
			<button class="btn btn-white disable" style="width:100%;"><img src="https://login.e-science.pl/cas/images/logo.png"> Login with e-science</button><br/><br/>
			
			
			<br/><br/>
			
			<h3>Personal Data Protection</h3>
			<a href="https://clb.ucl.cas.cz/ochrana-osobnich-udaju/">In Czech</a><br/>
			<a href="https://clb.ucl.cas.cz/en/personal-data-protection/">In English</a>
			
			<p>We need some content here :-) </p>
			
			
		</div>
		<div class="col-sm-2" ></div>
		<div class="col-sm-6">
			<iframe src="https://docs.google.com/forms/d/e/1FAIpQLSfd_WplypLkKcwc_sM4hBClQACMc8GkRrPCPaPuaTM1Q7jucw/viewform?embedded=true" width="520px" height="680px" frameborder="0" marginheight="0" marginwidth="0">Loading…</iframe>
			
		</div>
		
	</div>
<?php else: ?>

<?= $this->render('user/userAccount.php', [] ) ?>	
	
<?php endif; ?>
</div>
</div>

