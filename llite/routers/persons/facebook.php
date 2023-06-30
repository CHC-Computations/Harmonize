<?php 
if (empty($this)) die;
$this->setTitle('LiBRI faces');
?>

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>

<div class='main'>


<?php 

	$this->setTitle('LiBRI faces');
	$res = $this->sql->query("SELECT * FROM libri_persons_media LIMIT 100");
	$res = $this->sql->query("SELECT m.*,p.viafid FROM libri_persons_media m JOIN libri_persons p ON m.id_person=p.id ORDER BY p.viafid LIMIT 256");
	if (!empty($res->num_rows) && ($res->num_rows>0)) {
		while ($row = mysqli_fetch_assoc($res)) {
			echo "<a href='http://testlibri.ucl.cas.cz/lite/pl/persons/record/formImage/viaf_id{$row['viafid']}/'><img src='$row[url]' style='width:100px; height:100px; display:inline-block;'></a>";
			}
		}

?>



	
</div>

<?= $this->render('core/footer.php') ?>
