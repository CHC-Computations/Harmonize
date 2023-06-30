<?php 
if (empty($this)) die;
$this->setTitle($this->transEsc('Libri searcher home page'));

		

?>

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>

	<div class='container' id='content'>
		<div class="main">
			<h1>About LiBRI</h1>
		
		
			<p>Literary Bibliography Research Infrastructure (LiBRI) is a joint project of Czech Literary Bibliography (Institute of Czech Literature, Czech Academy of Sciences, Prague) and Polish LIterary Bibliography (Institute of Literary Research, Polish Academy of Sciences, Warsaw).</p>
			<p>The main aim of LiBRI is to collect, merge and provide from one shared interface to present bibliographical databases for literary studies </p>
			<p>At the moment, bibliographical collections of the founding partners are available. VuFind discovery system has been chosen as a software platform and is carefully adapted for the needs of the project. We are working on data cleaning and unification, in particular with regard to the subject description and persistent identifiers system.</p>

		</div>
		
	</div>

<?= $this->render('core/footer.php') ?>


