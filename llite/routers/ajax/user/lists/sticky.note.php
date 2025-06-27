<?php 

if ($this->user->isLoggedIn()) {
	$rec_id = $this->routeParam[0];
	require_once('./functions/class.forms.php');
	$this->addClass('forms', new forms);

	
	$userLogin = $this->user->full()->email;
	$rec = $this->psql->querySelect("SELECT * FROM users_sticky_notes WHERE rec_id = {$this->psql->string($rec_id)} AND user_id = {$this->psql->string($userLogin)};");	
	if (is_array($rec)) {
		$currentRecord = current($rec);
		$this->forms->values($currentRecord);
		} 
	
	echo '<form class="elb-forms" id="userStickiNote" method="POST">';
	echo '<input type="hidden" id="field_rec_id" name="rec_id" value="'.$rec_id.'">';
	$rowId = 'stickynote';
	echo $this->forms->text($rowId, ['ckeditor'=>true, 'more'=>'style="min-height:420px"']);
	$this->addJS("CKEDITOR.replace( 'field_{$rowId}');");
	echo '<div class="hidden" id="field_tracking"></div>';
	$this->addJS("timer = setInterval(updateDiv,100);
					function updateDiv(){
						var editorText = CKEDITOR.instances.field_{$rowId}.getData();
						$('#field_tracking').html(editorText);
					}");
	
	
	$saveOnClick = 'bookcart.saveStickyNote();';
	echo '
		<div class="row">
			<div class="col-sm-6" id="saveArea"></div>
			<div class="col-sm-6 text-right" id="buttonArea">
				<button type="button" class="btn btn-warning" data-dismiss="modal"><i class="ph ph-x"></i> '. $this->transEsc('Close') .'</button>			
				<button type="button" class="btn btn-success" id="userListSaveBtn" onClick="bookcart.saveStickyNote();"><i class="ph ph-check"></i> '.$this->transEsc('Save').'</button>
			</div>
		</div>
		';
	echo '</form>';	
	#echo $this->helper->pre($currentRecord);
		
	 

	}

?>