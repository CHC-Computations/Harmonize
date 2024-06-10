<?php 
if (!empty($value)) 
	echo '
		<dl class="detailsview-item">
		  <dt class="dv-label">'.$label.':</dt>
		  <dd class="dv-value">'.$this->helper->pre($value).'</dd>
		</dl>
		';

/*			
if (!empty($value)) {
	if (is_array($value))
		if (count($value)>1)
			$value = "<ol><li>".implode('</li><li>', $value).'</li></ol>';
			else 
			$value = current($value);
		
	if (is_string($value))	
		echo '
				<dl class="detailsview-item">
				  <dt class="dv-label">'.$label.':</dt>
				  <dd class="dv-value">'.$value.'</dd>
				</dl>
			';
		else 
		echo $this->helper->pre($value);	
	}
	*/
?>