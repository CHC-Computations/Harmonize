<?php 


if (!empty($auth)) 
	$author = '<b>'.$this->transEsc('by').'</b>: '.$this->render('record/author-link-simple.php', ['author'=>$auth]).'<br/>'; 
	else 
	$author = '';

if (!empty($in = $this->marc->getIn())>0)
	$instr = '<b>'.$this->transEsc('In').':</b> '.$in.'<br/>';
	else 
	$instr = '';
									
$in = $this->marc->getPublished();
if (count($in)>0)
	$published = '<b>'.$this->transEsc('Published').':</b> '.implode('<br/>', $in).'<br/>';
	else 
	$published = '';
											
/*											

// <button OnClick="results.InModal('<?= $result->id ?>', '<?= base64_encode('<pre>'.print_r($result,1).'</pre>') ?>');">full</button>

*/


echo $this->helper->panelCollapse(
						'result_'.$result->id, 
						$this->buffer->resultCheckBox($result).'
						<div class="title"><a href="'.$this->basicUri('search/record/'.$result->id.'.html').'">'.$result->title.'</a></div>',
						'
						<div class="result">
							
							<div class="result-media">
								'.$this->render('record/cover.php', ['rec' => $this->marc]).'
							</div>
							<div class="result-body">
								<div class="result-desc">
									'.$author.'
									'.$instr.'
									'.$published.'
									<span class="label label-primary">'.$this->transEsc($this->marc->getFormat()).'</span><br/>
									
								</div>
							</div>
							<div class="result-actions">
								
							</div>
						</div>
						',
						'',
						false 
						);
?>




