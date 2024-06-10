<?php 
if (empty($this)) die;
$this->addClass('buffer', 	new buffer()); 
$this->setTitle($this->transEsc('The page You are looking for, does not exists'));

?>

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<div class="container" id="content">
<h1><?= $this->transEsc('The page you are looking for does not exist on our server.') ?></h1>
</div>
<?= $this->render('core/footer.php') ?>