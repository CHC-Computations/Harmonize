<?php 
require_once('./functions/class.forms.php');
$this->addClass('forms', new forms);


$id = intval($this->routeParam[0]);
$baseQUERY = "
		FROM matching_manual a 
		LEFT JOIN matching_fields f ON a.field = f.id
		LEFT JOIN dic_rec_types rt ON a.rectype = rt.id
		LEFT JOIN dic_values_types v ON a.valuetype = v.id
		";
$rec = $this->psql->querySelect("SELECT *, a.id $baseQUERY WHERE a.id = '$id';");	
if (is_array($rec)) {
	$currentRecord = current($rec);
	$this->forms->setGrid(4,6);
	$this->forms->values($currentRecord);
	#echo $this->helper->pre($this->routeParam);
	
	echo '<form class="elb-forms">';
	
	echo $this->forms->row(
		$rowId = 'rec_type_name',
		$this->transEsc('Record type'),
		$this->forms->select(
				$rowId, 
				$this->forms->table2Values($this->psql->querySelect("SELECT * FROM dic_rec_types;"), 'rec_type_name', 'rec_type_name')
				)
		);
	echo $this->forms->row(
		$rowId = 'fieldname',
		$this->transEsc('Value field'),
		$this->forms->select(
				$rowId,
				$this->forms->table2Values($this->psql->querySelect("SELECT * FROM matching_fields;"), 'fieldname', 'fieldname')
				)
		);
	echo $this->forms->row(
		$rowId = 'value_type',
		$this->transEsc('Value type'),
		$this->forms->select(
				$rowId, 
				$this->forms->table2Values($this->psql->querySelect("SELECT * FROM dic_values_types;"), 'value_type', 'value_type')
				)
		);
	echo $this->forms->row(
		$rowId = 'value',
		$this->transEsc('Value'),
		$this->forms->input('text', $rowId)
		);
		
		
	if ($currentRecord['target'] == 0) {
		$currentRecord['target'] = '';
		$this->forms->values['target_empty'] = 1;
		$checked1 = 'checked="checked"';
		$checked2 = '';
		} else {
		$this->forms->values['target_empty'] = 2;	
		$checked1 = '';
		$checked2 = 'checked="checked"';
		}
		
	echo '<div class="row">
		<div class="col-sm-4 text-right">
			<label>'.$this->transEsc('Target').':</label>
		</div>
		<div class="col-sm-8">
			<div class="elb-forms-radio-row">
			<input type="radio" id="field_target_empty1" name="field_target_empty" value="1" '.$checked1.'>
			<label class="radio" for="field_target_empty1">
				<span>'.$this->transEsc('do not match').'</span>
			</label>
			</div>
			<div class="elb-forms-radio-row">
			<input type="radio" id="field_target_empty2" name="field_target_empty" value="2" '.$checked2.'>
			<label class="radio" for="field_target_empty2">
				<span>'.$this->transEsc('match to wikidata ID').':</span>
				<input 	type="text" 
						id="field_target" class="" name="target" value="'.$currentRecord['target'].'" placeHolder="Q123..." 
						OnFocus=\'$("#field_target_empty2").prop("checked", true);\'
						onKeyUp=\'$("#checkTarget").html(" ");\'
						/>
				<button class="btn btn-checker" type="button" onClick=\' page.post("checkTarget", "wiki/record.box/"+$("#rec_type_name").val()+"/"+$("#field_target").val(), {"wikiQ":$("#field_target").val(), "recType":$("#rec_type_name").val()}); \'>
					<i class="ph ph-cloud-check"></i> '.$this->transEsc('check').'</button>		
			</label>
			</div>
			<div id="checkTarget"></div>
		</div>
		</div>
		';
	 
	echo '</form>';
	echo '<br/><br/>';	
	

	#echo $this->helper->pre($this->forms->values);
		
	} else {
		
	echo $this->transEsc('Record with id __recID__ not exists', ['recID'=>$id]);	
	}




?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" ><i class="ph ph-check"></i> <?= $this->transEsc('Save') ?></button>
    <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="ph ph-x"></i> <?= $this->transEsc('Close') ?></button>
</div>