<?php

$import_db = $this->params[3];
$this->setTitle($this->transEsc('Importing data from ... '.$import_db));


?>

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>

<div class='main'>
	<div class='container'>
		<h1><?= $import_db ?></h1>
		<p>This will be create an index for search autocomplete suggestions list. </p>
		
		
		<div id='import_area'>
			<button class="btn btn-success" OnClick="importer.acIndeks(0,100);">Start importing</button>
		</div>
		<div id="import_area"></div>
		

	</div>
</div>


<?= $this->render('core/footer.php') ?>




<?php 
// http://147.231.80.162:8983/solr/biblio/select?fl=title%2C%20author%2C%20genre%2C%20topic%2C%20info_resource_str_mv%2C%20article_resource_txt_mv%2C%20year_str_mv&q.op=OR&q=*%3A*&rows=1000

?>
