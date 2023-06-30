<?php

$import_db = $this->params[3];
$this->setTitle($this->transEsc('Importing data from ... '.$import_db));


?>

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>

<div class='main'>
	<div class='container'>
		<h1><?= $import_db ?></h1>
		
		<div id='import_area'>
			<button class="btn btn-success" OnClick="importer.All(0,100);">Start importing</button>
			<div class=loader></div>
		</div>
	</div>
</div>

<?= $this->render('core/footer.php') ?>




