<?php
require_once('./functions/class.forms.php');
$this->addClass('forms', new forms);


$errorReportRecord = [
	'name'=>'',
	'mail'=>'',
	];
	
if ($this->user->isLoggedIn()) {
	$errorReportRecord = [
		'name' => $this->user->full()->name,
		'mail' => $this->user->full()->email,
		];
	}		
if (!empty($_SESSION['errorReportRecord']))
	$errorReportRecord = $_SESSION['errorReportRecord'];
	
	
	
$this->forms->setGrid(3,9);
$this->forms->values($errorReportRecord);

echo '<div class="report.error" name="error">
	<div class="container">
		<p style="padding-top:16px; padding-bottom:8px;">'.$this->transEsc('Have you noticed an error on a page or have additional information that could enrich the content presented').'?
			<button type="button" class="btn btn-link" data-toggle="collapse" data-target="#error_report_form">
				<i class="ph ph-envelope"></i> '.$this->transEsc('Let us know').'.
			</button>	
		</p>
		<div class="collapse" id="error_report_form">
			<div class="panel panel-default">
				<div class="panel-body">
				<div id="error_report_feedback"></div>
	';


echo '<form class="elb-forms" method="POST" action="'.$this->selfUrl().'">';
echo '<p>';
echo $this->transEsc('The personal data provided will be used solely for the purpose of contacting you about this report').'.<br/>';	
echo $this->transEsc('More about personal data protection').': <a href="https://clb.ucl.cas.cz/ochrana-osobnich-udaju/">v češtině</a> <a href="https://clb.ucl.cas.cz/en/personal-data-protection/">In English</a>';
echo '</p>';

echo $this->forms->row(
	$rowId = 'name',
	$this->transEsc('Your name'),
	$this->forms->input('text', $rowId, ['required'=>'required'])
	);
echo $this->forms->row(
		$rowId = 'mail',
		$this->transEsc('Your e-mail'),
		$this->forms->input('text', $rowId, ['required'=>'required'])
		);

$rowId = 'message';
echo $this->transEsc('Your message').':';
echo $this->forms->text($rowId, ['ckeditor'=>true, 'more'=>'style="min-height:220px"', 'required'=>'required']);
#$this->addJS("CKEDITOR.replace( 'field_{$rowId}');");
echo '<input type="hidden" id="field_tracking" name="field_tracking">';
$this->addJS("timer = setInterval(updateDiv,100);
				function updateDiv(){
					var editorText = $('#field_{$rowId}').val();
					$('#field_tracking').val(editorText);
				}");
	

echo '<p>'.$this->transEsc('Link to current page will be attached').'.</p>';
echo '<input type="hidden" name="from_name" value="error.report">';
echo '<input type="hidden" name="selfUrl" value="'.$this->selfUrl().'">';
echo '<input type="hidden" name="cmsKey" value="'.$this->user->cmsKey.'">';
echo '<div class="text-center">	<button class="btn btn-success">'.$this->transEsc('Send').'</button></div>';
echo '</form>';	

	
echo '	
				</div>
			</div>
		</div>
	</div>
	</div>
	';


?>