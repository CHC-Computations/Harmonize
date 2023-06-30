<?php if (!empty($audio)): ?>
	<br/><audio controls style="width:100%">
		  <source src="<?= $audio ?>" type="audio/ogg">
			<?= $this->transEsc('Your browser does not support the audio element') ?>.
		  </source>
		</audio> 
<?php endif; ?>