<?php 
	
	if (!empty($value))
		echo '
				<dl class="detailsview-item">
				  <dt class="dv-label">'.$label.':</dt>
				  <dd class="dv-value"><a href="https://viaf.org/viaf/'.$value.'/" title="'.$this->transEsc('Go to Viaf web page').'">'.$value.'</a></dd>
				</dl>
			';
		
?>