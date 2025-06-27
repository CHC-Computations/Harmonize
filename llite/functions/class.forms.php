<?php 

class forms {
	
	var $values = [];
	var $grid;
	
	public function values($Tv) {
		$this->values = $Tv;
		#echo "forms values<pre>".print_r($this->values,1)."</pre>";
		}
	
	public function setGrid($labels, $values, $feedBack = '') {
		$grid = new stdClass;
		$grid->labels = $labels;
		$grid->values = $values;
		$grid->feedBack = $feedBack;
		$this->grid = $grid;
		}
		
	public function row($id, $label, $input, $options = []) {
		if (!empty($this->grid->feedBack)) 
			$feedBackStr = '<div class="col-sm-'.$this->grid->feedBack.'" id="feedBack'.$id.'"></div>';
			else 
			$feedBackStr = '';
		return '<div class="row">
			<div class="col-sm-'.$this->grid->labels.' text-right">
				<label for="field_'.$id.'">'.$label.':</label>
			</div>
			<div class="col-sm-'.$this->grid->values.'">
				'.$input.'
			</div>
			'.$feedBackStr.'
			</div>';
		}	
	
	
	public function table2Values($table, $keyName = 'key', $valueName = 'name') {
		$res = [];
		if (!empty($table) && is_array($table))
			foreach ($table as $row) 
				if (!empty($row[$keyName]) & !empty($row[$valueName]))
					$res[$row[$keyName]] = $row[$valueName];
		return $res;		
		}
	
	public function select($id, $values = [], $o = [], $addOns = '') {
		
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
	
	public function radio($id, $values = [], $o = [], $addOns = '') {
		
		$name = $id;
		if (!empty($o['class']))
			$class = $o['class'];
			else 
			$class = '';
		
		if (!empty($o['id']))
			$id = $o['id'].'_'.$id;
		
		if (!empty($o['onChange']))
			$addOns.=" onChange=\"$o[onChange]\";";
		
		$i = 0;
		foreach ($values as $k=>$v) {
			$i++;
			if (!empty($this->values[$id]) && ($this->values[$id] == $k) )
				$checked = 'checked="checked"';
				else 
				$checked = '';	
			$options[] = '<label class="radio" for="field_'.$id.$i.'"><input type="radio" id="field_'.$id.$i.'" name="field_'.$id.'" value="'.$k.'" '.$checked.'>'.$v.'</label>';
			}
		return implode('', $options);
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
		
		return '<input type="'.$type.'" id="field_'.$id.'" class="'.$class.'" name="'.$name.'" '.$required.''.$placeholder.''.$more.'>';
		}
	
	public function text($id, $o = []) {
		
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
		 
		if (!empty($o['id']))
			$id = $o['id'].'_'.$id;
		
		$content = $this->values[$id] ?? '';
		
		return '<textarea id="field_'.$id.'" class="'.$class.'" name="'.$name.'" '.$required.''.$placeholder.''.$more.'>'.$content.'</textarea><br/>';
		}
	
	
	
	}

?>