<?php 

if ($this->user->isLoggedIn()) {
	require_once('./functions/class.forms.php');
	$this->addClass('forms', new forms);

	#echo $this->helper->pre($this->POST);

	$currentRecord['list_ico'] = 'ph-check-square';
	$iconList = [
		'ph ph-check-square',
		'ph ph-book-bookmark',
		'ph ph-bookmark',
		'ph ph-bookmarks',
		'ph ph-heart',
		'ph ph-push-pin',
		'ph ph-sparkle',
		'ph ph-warning',
		];

	$HF = '';
	$userLogin = $this->user->full()->email;
	$id = intval($this->POST['pdata']);
	if ($id>0) {
		$rec = $this->psql->querySelect("SELECT * FROM users_lists WHERE id = '$id';");	
		if (is_array($rec)) {
			$currentRecord = current($rec);
			$this->forms->values($currentRecord);
			$HF = '<input type="hidden" id="field_id" name="id" value="'.$id.'">';
			} 
		}
	$this->forms->setGrid(3,8);


		
	echo '<form class="elb-forms" id="userListsAdd" action="'.$this->buildUrl('ajax/user/lists/save').'" method="post" target="saveArea">';
	echo $HF;
	echo $this->forms->row(
		$rowId = 'list_name',
		$this->transEsc('Name'),
		$this->forms->input('text', $rowId, ['required'=>'required'])
		);
	echo '<div class="row">
		<div class="col-sm-3 text-right"><label>'.$this->transEsc('Select list icon').':</label></div>
		<div class="col-sm-8">';
	foreach ($iconList as $ico) {
		$icoId = str_replace('-','_', $ico);
		if ($ico == $currentRecord['list_ico'])
			$class = ' active';
			else
			$class = '';
		$onClickAction = "
			$('.elb-form-icon-select').removeClass('active');
			$('#btn_{$icoId}').addClass('active');
			$('#field_list_ico').val('$ico');
			";
		echo '<button type="button" onClick="'.$onClickAction.'" class="elb-form-icon-select'.$class.'" id="btn_'.$icoId.'"><i class="ph '.$ico.'"></i></button>';
		}
	echo '<input class="" type="hidden" name="list_ico" id="field_list_ico" value="'.$currentRecord['list_ico'].'">';
	echo '</div></div>';	
	
	
	
	$rowId = 'list_description';
	echo '<div class="row">
		<div class="col-sm-3 text-right"><label>'.$this->transEsc('Description').':</label></div>
		</div>';
		
	echo $this->forms->text($rowId, ['ckeditor'=>true, 'more'=>'style="min-height:420px"']);
	$this->addJS("CKEDITOR.replace('field_{$rowId}');");
	
	echo '<div class="hidden" id="field_tracking"></div>';
	$this->addJS("timer = setInterval(updateDiv,100);
					function updateDiv(){
						var editorText = CKEDITOR.instances.field_{$rowId}.getData();
						$('#field_tracking').html(editorText);
					}");
	echo '
		<div class="row">
			<div class="col-sm-8" id="saveArea"></div>
			<div class="col-sm-4 text-right" id="buttonArea">
				<button type="button" OnClick="page.post(\'user_lists_editor\', \'user/lists/editor\', \'new\');" class="btn btn-info" id="userListClearBtn"><i class="ph ph-x"></i> '.$this->transEsc('Clear').'</button>
				<button type="button" OnClick="bookcart.saveList();" class="btn btn-success" id="userListSaveBtn"><i class="ph ph-check"></i> '.$this->transEsc('Save').'</button>
			</div>
		</div>
		';
	echo '</form>';	
	#echo $this->helper->pre($currentRecord);
		
	 

	}

?>