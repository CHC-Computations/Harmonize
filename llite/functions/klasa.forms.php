<?php 

class forms {
	
	var $values = [];
	
	public function values($Tv) {
		$this->values = $Tv;
		#echo "forms values<pre>".print_r($this->values,1)."</pre>";
		}
	
	public function select($id, $values = [], $o = []) {
		$addOns = '';
		$name = $id;
		if (!empty($o['class']))
			$class = $o['class'];
			else 
			$class = '';
		
		if (!empty($o['id']))
			$id = $o['id'].'_'.$id;
		
		if (!empty($o['onChange']))
			$addOns.=" onChange=\"$o[onChange]\";";
		
		$options = '';
		foreach ($values as $k=>$v) {
			if (!empty($this->values[$name]) && ($this->values[$name] == $k) )
				$options .= '<option value="'.$k.'" selected="selected">'.$v.'</option>';
				else 
				$options .= '<option value="'.$k.'">'.$v.'</option>';	
			}
		return '<select id="'.$id.'" class="'.$class.'" name="'.$name.'" data-native-menu="false" aria-label="Search type" '.$addOns.'>
					'.$options.'
				</select>';
		}
	
	
	public function input($type, $id, $o = []) {
		
		$name = $id;
		if (!empty($o['class']))
			$class = $o['class'];
			else 
			$class = '';
		
		if (!empty($o['required']))
			$required = ' '.$o['required'];
			else 
			$required = '';
		if (!empty($o['placeholder']))
			$placeholder = ' placeholder="'.$o['placeholder'].'"';
			else 
			$placeholder = '';
		if (!empty($o['more']))
			$more = ' '.$o['more'];
			else 
			$more = '';
		if (!empty($this->values[$id]))
			$more.= ' value="'.$this->values[$id].'"'; 
		
		if (!empty($o['id']))
			$id = $o['id'].'_'.$id;
		
		return '<input type="'.$type.'" id="'.$id.'" class="'.$class.'" name="'.$name.'" '.$required.''.$placeholder.''.$more.'>';
		}
	
	
	
	}

?>