<?php

$id = $this->routeParam[0];
$fixedLink = $this->HOST.'id/'.$id;

?>

<div class="row">
  <div class="col-sm-4 text-center">
	<img src="https://quickchart.io/qr?size=400&text=<?=$fixedLink?>&choe=UTF-8"  title="<?= $this->transEsc('Fixed link to this record') ?>" style="width:100%; padding:20px;"/>
  </div>
  <div class="col-sm-8 ">
	<br/><br/>
	 <form>
	  
		<input type="text" class="form-control input-lg" id="fixedLink" value="<?= $fixedLink?>">
		<br/>
		<div class="text-center">
		  <button class="btn btn-default" type="button" OnClick="page.clipboard('fixedLink')">
			<i class="ph-bold ph-copy"></i> <?= $this->transEsc('copy') ?>
		  </button>
		</div>
		<br/>
		<p><?= $this->transEsc('This link is better than the link in the address bar because') ?>:</p>
		<ul>
		<li><?= $this->transEsc('it is shorter') ?></li>
		<li><?= $this->transEsc('it does not contain a language code (the recipient will see the content in their own language if available)') ?></li>
		<li><?= $this->transEsc('we promise that this link will not change as long as the service exists') ?></li>
		</ul>
		<br/>
		<br/>
	</form> 
  </div>
</div>