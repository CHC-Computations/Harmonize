<?php 


?>



<div class="panel panel-info">
	<div class="panel-heading"><?= $this->transEsc('Create account')?></div>
	<div class="panel-body">
				
	  <form method="post" action="<?=$this->basicUri('iframeforms/checkregisterform/')?>" name="accountForm" id="registerForm" class="form-user-create" data-toggle="validator" role="form" target="registerArea">

	  <?php foreach ($fields as $key=>$f): ?>
		<div class="form-group labels-slide">
			<label  for="account_<?= $key ?>"><?= $f['label']?>:</label>
			<?= $this->forms->input($f['type'], $key, array_merge($options, ['placeholder' => $f['label'] ])) ?>
		</div>
		  
	  <?php endforeach; ?>
	  
	  <div >
		<input type="checkbox" id="account_rules" required>	
		<label for="account_rules" >
			<?= $this->transEsc('I accept the rules and bla bla bla ') ?>
		</label>
		
	  </div>
	  <div class="form-group">
		<div class="row">
			<div class="col-sm-6"><iframe name='registerArea' id='registerArea' src="<?=$this->basicUri('none')?>" style="width:100%; height:30px; border:0px;"></iframe></div>
			<div class="col-sm-6 text-center"><button class="btn btn-primary" type="submit" name="send" ><i class="glyphicon glyphicon-ok"></i> <?= $this->transEsc('Send') ?></button></div>
		</div>
	  </div>
	  </form>
	</div>
</div>
