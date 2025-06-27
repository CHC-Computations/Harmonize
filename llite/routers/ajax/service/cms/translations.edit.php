<?php 
require_once('./functions/class.forms.php');
$this->addClass('forms', new forms);


 	

#echo $this->helper->pre($this->routeParam);
#echo $this->helper->pre($this->POST);

$originalString = base64_decode($this->POST['pdata']);
echo '<div style="color:#222">';
$t = $this->psql->querySelect("SELECT * FROM translate WHERE string = {$this->psql->string($originalString)}");
if (is_array($t)) {
	#echo $this->helper->pre($t);
	
	$msg = $this->transEsc('See an example of use in the context of a website.').':<ul>';
	foreach ($t as $row) {
		$msg .= '<li><small>'.$row['lang'].'</small> <a href="'.$this->HOST.substr($row['context'], 1).'" target=_blank title="last seen: '.$row['added'].'">'.$row['context'].'</a></li>';
		}
	$msg .= '</ul>';	
	} else {
	$msg = '<p>No record of this translation being used in the context of the website.<br/>This may mean that this translator`s entry is no longer needed 
		or that no one has visited the subpage where this translation appears in the last few days. </p>';	
	}
echo $this->helper->alert('info', $msg);
		


$t = $this->psql->querySelect("SELECT * FROM dic_translations WHERE original = {$this->psql->string($originalString)}");
if (is_Array($t)) {
	#echo $this->helper->pre($t);
	$currentRecord = current($t);
	} else {
	$currentRecord = [
		'original' => $originalString,
		'en' => '', 
		'cs' => '', 
		'pl' => ''
		];	
	}

$this->forms->setGrid(1,11);
$this->forms->values($currentRecord);
echo '<form class="elb-forms">';

echo '<b>Original text</b><br/>';
echo $this->helper->panelSimple($originalString);
echo $this->forms->row('plrow', 'EN*', $this->forms->text('en'));
echo $this->forms->row('plrow', 'CS', $this->forms->text('cs'));
echo $this->forms->row('plrow', 'PL', $this->forms->text('pl'));

echo '<br/><b>*EN</b> Use only if you want to overwrite the original text.';
	
echo '</form>';

echo '<div id="saveArea"></div>';
$rowId = md5($originalString);

$saveAction = "page.post('saveArea', 'service/cms/translations.save', {'string':'".base64_encode($originalString)."', 'en':$('#field_en').val(), 'pl':$('#field_pl').val(), 'cs':$('#field_cs').val()} );";
$deleteAction = "page.post('$rowId', 'service/cms/translations.delete/', '".base64_encode($originalString)."');";
echo '<hr/>
	<div class="text-center">
		<div class="row">
			<div class="col-sm-4 text-left">
				<button class="btn btn-danger" type="button" onClick="'.$deleteAction.'" title="'.$this->transEsc('Delete translation').'"><i class="ph ph-trash"></i></button>
			</div>	
			<div class="col-sm-4 text-center">
				<button class="btn btn-success" type="button" onClick="'.$saveAction.'" ><i class="ph ph-check"></i> '.$this->transEsc('Save').'</button>
			</div>	
			<div class="col-sm-4 text-center">
				<button class="btn btn-warring" type="button" data-dismiss="modal"><i class="ph ph-x"></i> '.$this->transEsc('Close').'</button>
			</div>
		</div>
	</div>
	';
echo '</div>';


# echo $this->helper->pre($this->ajaxparent->params);
?>