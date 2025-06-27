<?php 
	if (!empty($value)) {
		if (is_string($value))
			$strValue = $value;
		if (is_array($value)) {
			$return = [];
			foreach ($value as $claim) {
				if (is_string($claim))
					$return[] = $value;
				if (is_object($claim))
					$return[] = $claim->text.' <small class="label label-info">'.$claim->language.'</small>';
				}
			$strValue = implode('<br/>', (array)$return);
			}
		
		if (!empty($strValue))
			echo '
				<dl class="detailsview-item">
				  <dt class="dv-label">'.$label.':</dt>
				  <dd class="dv-value">'.$strValue.'</dd>
				</dl>
				';
		}
?>