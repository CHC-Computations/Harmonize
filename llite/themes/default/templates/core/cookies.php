<?php 

if (!empty($msg[$this->userLang]))
	$displaymsg = $msg[$this->userLang];
	else 
	$displaymsg = current($msg);

#echo "<pre>".print_r($_COOKIE,1)."</pre>";

?>
<?php if (empty($_COOKIE['CookieAccepted'])): ?>
	<div class="cookies" id="cookiesBox">
		<div class="cookies-box">
			<div class="cookie-image">
				<img src="<?= $this->HOST ?>/themes/default/images/cookie.svg" alt="Some cookie" class="img img-responsive">
			</div>
			<div class="cookie-message">
				<?= $displaymsg ?>
			</div>
			<div class="cookie-btn">
				<button class="btn btn-primary" OnClick="user.eatsCookie();"><i class="glyphicon glyphicon-ok"></i> OK</button>
			</div>
		</div>
	</div>
<?php endif; ?>
	
	