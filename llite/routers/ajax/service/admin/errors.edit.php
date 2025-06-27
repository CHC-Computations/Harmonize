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
$rec = $this->psql->querySelect("SELECT * FROM cms_posts WHERE id = '$id';");	
if (is_array($rec)) {
	$currentRecord = current($rec);
	$this->forms->setGrid(2,8);
	$this->forms->values($currentRecord);
	#echo $this->helper->pre($this->routeParam);
	
	echo '<form class="elb-forms">';
	echo '<div class="row">
		<div class="col-sm-6">';
	echo $this->forms->row(
		$rowId = 'parent_id',
		$this->transEsc('Parent'),
		$this->forms->input('text', $rowId)
		);
	echo '</div>
		<div class="col-sm-6">';
	echo $this->forms->row(
			$rowId = 'p_order',
			$this->transEsc('Order'),
			$this->forms->input('text', $rowId)
			);
	echo '</div>
		</div>';
	echo $this->forms->row(
		$rowId = 'title',
		$this->transEsc('Title'),
		$this->forms->input('text', $rowId)
		);
	echo $this->forms->row(
		$rowId = 'url',
		$this->transEsc('url'),
		$this->forms->input('text', $rowId)
		);
	
	$rowId = 'content';
	echo $this->transEsc('Content');
	echo $this->forms->text($rowId, ['ckeditor'=>true, 'more'=>'style="min-height:420px"']);
	$this->addJS("CKEDITOR.replace( 'field_{$rowId}');");
	
	
	
	$rowId = 'script';
	echo $this->transEsc('Script');
	echo $this->forms->text($rowId);
	
	echo $this->forms->row(
		$rowId = 'author',
		$this->transEsc('author'),
		$this->forms->input('text', $rowId)
		);
		
	
	echo '<br/><br/>';	
	echo '<div class="row">	
		<div class="col-sm-4">
		'.$this->transEsc('Created').': <strong>'.$currentRecord['date_c'].'</strong>
		</div>
		<div class="col-sm-4">
		'.$this->transEsc('Last edit').': <strong>'.$currentRecord['date_m'].'</strong>
		</div>
		<div class="col-sm-4">
		'.$this->transEsc('by').': <strong>'.$currentRecord['operator'].'</strong>
		</div>
		</div>
		';

	echo '</form>';	
	#echo $this->helper->pre($currentRecord);
		
	} else {
		
	echo $this->transEsc('Record with id __recID__ not exists', ['recID'=>$id]);	
	}




?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" ><i class="ph ph-check"></i> <?= $this->transEsc('Save') ?></button>
    <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="ph ph-x"></i> <?= $this->transEsc('Close') ?></button>
</div>