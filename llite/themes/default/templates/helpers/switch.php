<label class="switch">
	<input type="checkbox" <?= $checked ?> id="<?= $id ?>" OnChange="<?= $onChange ?>"> 
	<span class="slider slider-<?= $color ?> round"></span>
</label> 
<?= $label ?> <span class="badge"><?= $this->helper->badgeFormat($badge) ?></span>
<br/>