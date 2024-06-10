<?php if (!empty($audio = $this->buffer->loadWikiMediaUrl($this->wiki->getStrVal('P443')))): ?>
	<br/><audio controls style="width:100%">
		  <source src="<?= $audio ?>" type="audio/ogg">
			<?= $this->transEsc('Your browser does not support the audio element') ?>.
		  </source>
		</audio> 
<?php endif; ?>