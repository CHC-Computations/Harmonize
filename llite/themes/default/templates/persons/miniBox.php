<?php 
require_once('functions/klasa.persons.php');
$tmp = explode(' ', $activePerson);

$last = end($tmp);
if (stristr($last, 'viaf')) {
	$id = str_replace('http://viaf.org/viaf/', '', str_replace('viaf_id','', str_replace('"', '', $last) ) );
	$person = $this->buffer->loadPerson($id);
	$this->addClass('person', new person($person));
	}
	
?>

<?php if (!empty($person->id)): ?>

<div class="person-info">
	
	<div class="pi-Body">
		<div class="pi-Image">
			<?= $this->person->getImage() ?>
		</div>
		<div class="pi-Desc">
			<div style='float:right'><?= $this->person->getLinkPanel() ?></div>
			<h4><a href="<?= $this->person->createLibriLink() ?>"><?= $this->person->getName() ?> <small><?= $this->person->getDateRange() ?></small></a></h4>
			<p><?= $this->person->getDescription() ?></p>

		</div>
	</div>
</div>
<?php endif; ?>