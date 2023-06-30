<?php 

$this->JS[] = "page.ajax('line_all_persons', 'persons/count.summary')";

#$this->JS[] = "page.ajax('box_auto_rec', '')";
#$this->JS[] = "page.ajax('box_chcked_rec', '')";

?>

<h3><?= $this->transEsc('Persons') ?></h3>

<div class="row">
	<div class="col-sm-3" Id="col1">
		<div class="side-menu-group" id="line_all_persons">
			<a href="" class="side-menu-item" ><?= $this->transEsc('All persons in Libri')?></a>
			<a href="" class="side-menu-item" ><?= $this->transEsc('Persons with viaf Id')?></a>
		</div>
		<div id='box_vo_viaf'></div>
	</div>	
	<div class="col-sm-9" Id="col2"><?= $this->helper->loader2() ?></div>
</div>
