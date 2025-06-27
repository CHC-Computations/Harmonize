<?php 
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.converter.php');

$this->addClass('solr', 	new solr($this));
$this->addClass('convert', 	new converter($this));

$basicId = $this->POST['pdata']['id'];
$ddkey = $this->POST['pdata']['ddkey'];


	
	$query['q']=[ 
				'field' => 'q',
				'value' => 'ddkey_str:"'.$ddkey.'" AND !id:"'.$basicId.'"'
				];
	$query['facet']=[ 
				'field' => 'facet',
				'value' => 'false'
				];
			
	$query['rows']=[ 
			'field' => 'rows',
			'value' => 2
			];
	
	$query['q.op']=[ 
			'field' => 'q.op',
			'value' => 'AND'
			];
	
	
	$results = $this->solr->getQuery('biblio',$query); 
	$results = $this->solr->resultsList();
	$result = $results[0];

	$this->addClass('record', new bibliographicRecord($result, $this->convert->mrk2json($result->fullrecord)));
	$record = json_decode($result->relations);
?>						

<div class="row">
	<div class="col-sm-2">
		<a href="https://testlibri.ucl.cas.cz/en/results/compare/<?= $basicId ?>/<?= $result->id ?>" target=_blank class="btn btn-success" type="button">compare<br/>records</a>
	</div>
	<div class="col-sm-10">
		<div class="result-body">
			<h4 class="title"><a href="<?= $this->basicUri('results/biblio/record/'.$result->id.'.html') ?>"><?= $this->helper->setLength($title = $record->title ,200) ?></a></h4>
			<div class="result-desc">
				<?php if (!empty($record->persons->mainAuthor)) 
					echo '<b>'.$this->transEsc('by').'</b>: '.$this->render('record/author-link.php', ['author'=>current((array)$record->persons->mainAuthor)]).'<br/>'; 
				?>
				<?php if (!empty($record->corporates->publisher) && ($record->majorFormat!=='Book') && empty($record->publishedIn)): ?>
					<b><?= $this->transEsc('In')?>:</b> <?= $this->render('record/publisher-link.php', ['publisher'=>current((array)$record->corporates->publisher), 'publicationYear' => current($record->publicationYear) ?? null ]) ?><br/>
				<?php endif; ?>	
				<?php if (!empty($record->sourceDocument)): ?>
					<b><?= $this->transEsc('In')?>:</b> <?= $this->render('record/source-link.php', ['source'=>current((array)$record->sourceDocument) ]) ?><br/>
				<?php endif; ?>	
				<?php if (!empty($record->magazines->sourceMagazine)): ?>
					<b><?= $this->transEsc('In')?>:</b> <?= $this->render('record/source-link.php', ['source'=>current((array)$record->magazines->sourceMagazine) ]) ?><br/>
				<?php endif; ?>	
				<?php 
					if (!empty($record->publishedIn)) {
						echo '<b>'.$this->transEsc('Published').'</b>: ';
						foreach ($record->publishedIn as $in)
							echo $in.'<br/>';
						}
				?>	
				<span class="label label-primary"><?= $this->transEsc($record->majorFormat) ?></span><br/>
				
			</div>
		</div>
	</div>
</div>
  
<?php
if ($this->solr->totalResults()>1)
	echo "there is more similar: ". $this->solr->totalResults()
?>