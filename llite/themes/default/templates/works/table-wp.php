<?php 
$box_id = str_replace('.','_', $result->id); 

$author = $instr = $published = '';
if (!empty($record->persons->mainAuthor)) 
	$author = $this->render('record/author-link.php', ['author'=>current((array)$record->persons->mainAuthor)]).'<br/>'; 
if (!empty($record->corporates->publisher)>0)
	$published = $this->render('record/publisher-link.php', ['publisher'=>current((array)$record->corporates->publisher), 'publicationYear' => current($record->publicationYear) ?? null ]) .'<br/>';

if ($this->solr->firstResultNo() == $result->lp)
	echo '<table class="table table-hover">
			<thead>
				<td>'.$this->transEsc('n.d.').'</td>
				<td> </td>
				<td>'.$this->transEsc('Format').'</td>
				<td>'.$this->transEsc('Title').'</td>
				<td>'.$this->transEsc('Author').'</td>
				<td>'.$this->transEsc('Published in').'</td>
				<td> </td>
			</thead>
			<tbody>';
			
echo '<tr id="result_'.uniqid().'">
		<td class="result-number" id="check_'.$box_id.'">'.$result->lp.'. '.$this->bookcart->resultCheckBox($result).'</td>
		<td class="media" style="cursor:pointer" title="'.$this->transEsc('preview this record').'" OnClick="results.preViewCopy(\''.$record->title.'\', \''.$box_id .'\');">'.$this->render('works/cover.php', ['result' => $result]).'</td>
		<td class="format">'.$this->transEsc($record->majorFormat).'</td>
		<td class="title"><a href="'.$this->basicUri('results/biblio/record/'.$result->id.'.html').'">'.$result->title.'</a></td>
		<td class="result">'.$author.'</td>
		<td>'.$instr.' '.$published.'</td>
		<td class="add-ons" id="stickyArea'.$box_id.'">'.$this->bookcart->resultStickyNote($result->id, 'ico').'</td>
		';
if ($this->solr->lastResultNo() == $result->lp)
	echo '
		</tbody>
		</table>';

?>

<div class="hidden" id="previewbox_<?= $box_id ?>">
	<?= $this->render('record/inmodal/core.php', ['record' => $this->record ]) ?>
</div>


