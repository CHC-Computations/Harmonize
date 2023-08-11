<?php 
	
	if (!empty($value))
		echo '
				<dl class="detailsview-item">
				  <dt class="dv-label">'.$label.':</dt>
				  <dd class="dv-value"><a href="https://isni.org/isni/'.str_replace(' ','',$value).'" target=_blank title="'.$this->transEsc('Go to ISNI web page (in new tab)').'">'.$value.'</a></dd>
				</dl>
			';
		
?>