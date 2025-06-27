<?php 
require_once('functions/class.helper.php');
require_once('functions/class.buffer.php');
require_once('functions/class.wikidata.php');
require_once('functions/class.wikidata.libri.php');
require_once('functions/class.solr.php');

$this->addClass('helper', 	new helper()); 
$this->addClass('buffer',	new buffer()); 
$this->addClass('solr',		new solr($this)); 
$this->addClass('wiki',		new wikidata($this)); 


#echo $this->helper->pre($this->POST);
if (!empty($this->routeParam[0])) 
	$wantedRecType = $this->routeParam[0];
if (!empty($this->routeParam[1])) 
	$wikiQ = $this->routeParam[1];
if (!empty($this->POST['pdata'])) {
	$wikiQ = $this->POST['pdata']['wikiQ'];
	$wantedRecType = $this->POST['pdata']['recType'];
	}

$this->wiki->loadRecord($wikiQ);

$recType = $this->wiki->recType();
if ($recType == $wantedRecType)
	$recTypeStr = '<span class="label label-success">'.$recType.'</span>';
	else 
	$recTypeStr = '<span class="label label-danger">'.$recType.' ?? '.$wantedRecType.'</span>';

if ($recType == 'person') 
	$dateRange = $this->wiki->getPersonYearsRange('-');
	else 
	$dateRange = '';
?>

<div class="row">
	<div class="col-sm-3 thumbnail">
		<?= $this->render('helpers/wiki.photo.php') ?>
	</div>
	<div class="col-sm-8">
		<?= $recTypeStr ?>
		<h4 property="name">
			<a href="<?= $this->buildURL('wiki/record/'.$wikiQ) ?>">
				<?= $this->wiki->get('labels') ?> <small><?= $this->wiki->get('aliases') ?></small>
			</a>
		</h4>
		<?= $dateRange ?>
		<p><?= $this->wiki->get('descriptions') ?></p>
	</div>
</div>