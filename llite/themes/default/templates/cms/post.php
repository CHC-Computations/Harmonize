<h1><?= $post['title'] ?></h1>

<?php if (!empty($post['content'])): ?>
	<div class="cms-post">
	<?= $post['content'] ?>
	</div>
<?php endif; ?>
<?php 
if (!empty($post['script'])) {
	echo '<div id="post_ajax_area">'.$this->helper->loader2().'</div>';
	$this->addJS($post['script']);
	}
?>
		