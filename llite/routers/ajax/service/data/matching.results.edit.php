<?php 
require_once('./functions/class.forms.php');
$this->addClass('forms', new forms);
$this->addClass('solr', new solr($this));

$this->addJS("$('[data-toggle=\"tooltip\"]').tooltip();");

$id = intval($this->routeParam[0]);
$baseQUERY = "
		FROM matching_results a 
		LEFT JOIN matching_strings s ON a.string_id = s.id
		LEFT JOIN dic_rec_types rt ON a.rectype_id = rt.id
		";
$rec = $this->psql->querySelect("SELECT *, a.id $baseQUERY WHERE a.id = '$id';");	
if (is_array($rec)) {
	$currentRecord = current($rec);
	$this->forms->setGrid(4,6);
	$this->forms->values($currentRecord);
	# echo $this->helper->pre($currentRecord);
	
	
	$query = [];
	$query['q'] = [
		'field' => 'q',
		'value' => '*:*'
		];
	$query['fq'] = [
		'field' => 'fq',
		'value' => 'orgin_labels:"'.$currentRecord['string'].'"'
		];
	$results = $this->solr->getQuery('biblio',$query); 
	$results = $this->solr->resultsList();
	$totalResults = $this->solr->totalResults();
	
	
	
	
	echo '<h4>'.$this->transEsc('Strings details').'</h4>';
	echo '<ul class="detailsview tri-col">';
	echo '<dl class="detailsview-item">
			<dt class="dv-label">'.$this->transEsc('Record Type').':</dt>
			<dd class="dv-value">'.$currentRecord['rec_type_name'].'</dd>
			<dd class="dv-desc"></dd>
		</dl>';
	echo '<dl class="detailsview-item">
			<dt class="dv-label">'.$this->transEsc('String').':</dt>
			<dd class="dv-value">'.$currentRecord['string'].'</dd>
			<dd class="dv-desc"><a href="" title="'.$this->transEsc('Records with label').'" data-toggle="tooltip"><i class="ph ph-books"></i> <b>'.$this->helper->numberFormat($totalResults).'</b></a></dd>
		</dl>';
		
	$t = $this->psql->querySelect("SELECT * $baseQUERY WHERE clearString={$this->psql->string($currentRecord['clearstring'])}");
	if (is_array($t)) {
		$count['otherStrings'] = count($t)-1;
		if ($count['otherStrings'] == 0) 
			$otherStringsMsg = '<a title="'.$this->transEsc('No other strings produce the same clearstring').'" data-toggle="tooltip"><i class="ph ph-check"></i></a>';
			else
			$otherStringsMsg = '<a title="'.$this->transEsc('Other strings produce the same clearstring').'" data-toggle="tooltip"><i class="ph ph-chat-centered-text"></i> '.$count['otherStrings'].'</a>';
		}
		
	echo '<dl class="detailsview-item">
			<dt class="dv-label">'.$this->transEsc('clearString').':</dt>
			<dd class="dv-value">'.$currentRecord['clearstring'].'</dd>
			<dd class="dv-desc">'.$otherStringsMsg.'</dd>
		</dl>';
	echo '</ul>';			
	echo '<h4>'.$this->transEsc('Last matching').'</h4>';
	echo '<ul class="detailsview tri-col">';
	echo '<dl class="detailsview-item">
			<dt class="dv-label">'.$this->transEsc('Match method').':</dt>
			<dd class="dv-value">'.$currentRecord['match_type'].'</dd>
		</dl>';
	echo '<dl class="detailsview-item">
			<dt class="dv-label">'.$this->transEsc('Source').':</dt>
			<dd class="dv-value">'.$currentRecord['match_source'].'</dd>
		</dl>';
	echo '<dl class="detailsview-item">
			<dt class="dv-label">'.$this->transEsc('Match to').':</dt>
			<dd class="dv-value">'.$currentRecord['match_result'].'<div id="checkTarget"></div></dd>
		</dl>';
		
	if (!empty($currentRecord['match_result']) && (substr($currentRecord['match_result'],0,1) == 'Q')) {
		$this->addJS('page.post("checkTarget", "wiki/record.box/'.$currentRecord['rec_type_name'].'/'.$currentRecord['match_result'].'", {"wikiQ":"'.$currentRecord['match_result'].'", "recType":"'.$currentRecord['rec_type_name'].'"});');
		}
		
	echo '<dl class="detailsview-item">
			<dt class="dv-label">'.$this->transEsc('Similarity level').':</dt>
			<dd class="dv-value">'.$currentRecord['match_level'].'%</dd>
		</dl>';
	echo '</ul>';			
				
	 
	echo '<br/><br/>';	
	

	#echo $this->helper->pre($this->forms->values);
		
	} else {
		
	echo $this->transEsc('Record with id __recID__ not exists', ['recID'=>$id]);	
	}




?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" ><i class="ph ph-check"></i> <?= $this->transEsc('Save') ?></button>
    <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="ph ph-x"></i> <?= $this->transEsc('Close') ?></button>
</div>