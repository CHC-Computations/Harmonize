<?php 
$box_id = str_replace('.','_', $result->id); 

$author = $instr = $published = '';
if (!empty($record->persons->mainAuthor)) 
	$author = '<b>'.$this->transEsc('by').'</b>: '.$this->render('record/author-link.php', ['author'=>current((array)$record->persons->mainAuthor)]).'<br/>'; 
if (!empty($record->corporates->publisher)>0)
	$published = '<b>'.$this->transEsc('In').':</b> '. $this->render('record/publisher-link.php', ['publisher'=>current((array)$record->corporates->publisher), 'publicationYear' => current($record->publicationYear) ?? null ]) .'<br/>';
			
											
echo $this->helper->panelCollapse(
		'result_'.uniqid(), 
		'
		<div class="result-number" id="check_'.$box_id.'">'.$this->bookcart->resultCheckBox($result).'</div>
		<div class="title"><a href="'.$this->basicUri('results/biblio/record/'.$result->id.'.html').'">'.$result->title.'</a></div>
		<div class="add-ons" id="stickyArea'.$box_id.'">'.$this->bookcart->resultStickyNote($result->id, 'ico').'</div>
		',
		
		'<div class="result">
		
			<div class="result-media">
				'.$this->render('record/cover.php', ['result' => $result]).'
			</div>
			<div class="result-body">
				<div class="result-desc">
					'.$author.'
					'.$instr.'
					'.$published.'
					<span class="label label-primary">'.$this->transEsc($record->majorFormat).'</span><br/>
					
				</div>
			</div>
			<div class="result-actions">
				<div id="stickyArea'.$box_id.'">'.$this->bookcart->resultStickyNote($result->id).'</div>
			</div>
		</div>
		',
		'',
		false 
		);

?>




