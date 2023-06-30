<?php 
if (empty($this)) die;
require_once('functions/klasa.helper.php');

$rec_id=str_replace('.html', '', $this->routeParam[1]);

$this->addClass('solr', new solr($this->config));
$this->addClass('buffer', new marcBuffer()); 
$this->addClass('helper', 	new helper()); 

$this->buffer->setSql($this->sql);
$marcJson = $this->buffer->getRecord('places', $rec_id);

if (!empty($marcJson)) {
	$this->addClass('record', new marc21($marcJson));
	$rec = $marcJson;

	$coreFields = $this->record->getPlaceFields();

	
	$this->setTitle( $this->transEsc("Place").": ".$this->record->fullName );
	
	} else {
	
	$marcJson = new stdclass;
	$marcJson->LEADER = null;
	$marcJson->id = $rec_id;
	$this->setTitle( $this->transEsc("Place unknown") );
	
	}


?>

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>

<div class='main'>
	<?php 
	if (!empty($marcJson->LEADER)) 
		echo $this->render('places/core.php', ['rec'=>$marcJson, 'coreFields'=>$coreFields]);
		else 
		echo $this->render('places/place-unknown.php', ['rec'=>$marcJson]);	
	?>
	
</div>

<?= $this->render('core/footer.php') ?>


