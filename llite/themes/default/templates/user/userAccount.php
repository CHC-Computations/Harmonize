<?php 
require_once('./functions/class.bookcart.php');
require_once('./functions/class.solr.php');
require_once('./functions/class.buffer.php');
$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer($this));
$this->addClass('bookcart', new bookcart);

$this->addJS('results.myList();');

#$t = $this->psql->querySelect("");
$stickyNotes = $this->bookcart->getMyStickyNotes();




?>

<br/><br/>
<div class="userAccount">
	<div class="row">
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-body">
					<div class="row userData">
						<div class="col-sm-3 text-center">
							<img src="<?= $this->user->getPicture()?> ">
						</div>
						<div class="col-sm-8">
							<h3><b><?= $this->user->full()->name ?></b></h3>
							<i class="ph ph-envelope"></i> <?= $this->user->full()->email ?><br/>
							<i><?= $this->user->full()->accountType ?> <?= $this->transEsc('account') ?></i>
						</div>
					</div>
				</div>
			</div>
			
		</div>
		<div class="col-sm-6">
			
		</div>
	</div>

	<div class="row">
		<div class="col-sm-3 sidebar">
			<div id="myListsArea" class="facets-body"></div>
		</div>
		<div class="col-sm-9">
			<?= 
			$this->helper->panelSimple('<h4>'.$this->transEsc('Yours sticky notes').':</h4>'.implode('', $stickyNotes)) 
			?> 
		</div>
	</div>
</div>
