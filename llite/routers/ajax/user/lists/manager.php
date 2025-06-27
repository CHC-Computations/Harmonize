<?php



$this->addJS("page.post('user_lists_editor', 'user/lists/editor', 'new')");
$this->addJS("page.post('user_lists_table', 'user/lists/table')");

echo $this->helper->panelSimple('
			<h4>'.$this->transEsc('Create new list').'</h4>
			<div id="user_lists_editor"></div>
		');
echo $this->helper->panelSimple('
			<h4>'.$this->transEsc('Existing lists').'</h4>
			<div id="user_lists_table"></div>
		');


?>


<div class="modal-footer">
	<button type="button" class="btn btn-warning" data-dismiss="modal"><i class="ph ph-x"></i> <?= $this->transEsc('Close') ?></button>
</div>