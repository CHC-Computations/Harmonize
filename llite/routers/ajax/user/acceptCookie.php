<?php 
if (empty($this)) die;
setCookie('CookieAccepted', 'yes', time() + (86400 * 30), "/");
#echo "<pre>".print_r($_POST,1)."</pre>";


?>
	<div class="cookies-box">
		<div class="cookie-image">
			<i class="glyphicon glyphicon-floppy-save"></i>
		</div>
		<div class="cookie-message">
			<?= $this->transEsc('Saving setting') ?>
		</div>
	</div>
	
	<script>
		$('#cookiesBox').animate({bottom: "-300px"}, 1000);
	</script>