
<footer class="hidden-print">
  <div class="footer-container">
    <div class="footer-column">
	</div>
    <div class="footer-column">
    </div>
	<div class="footer-column text-right">
		<div class="poweredby" style=" font-size:.9em;" >
			<img class="footer-logo" style="width:70px; padding:3px; padding-top:0px;" src="<?= $this->HOST ?>themes/default/images/libri_logo_white_simple.svg" alt="<?=$this->transEsc('Logo Libri')?>" />
			<?=$this->transEsc('based on')?> <a href="https://vufind.org/vufind/" style="color:#fff">VuFind</a>
		</div>
	</div>
  </div>

  <div class="footer-container">
	<div class="footer-column">
		<div style="">Copyright (c) 2018-<?= date("Y") ?> Ústav pro českou literaturu AV ČR & Instytut Badań Literackich PAN</div>
		<div style=" font-size:.9em;">
			<?= $this->transEsc('Design and development of the website')?> 
			<?= $this->transEsc('Poznań Supercomputing and Networking Center')?>, 
			<?= $this->transEsc('Institute of Czech Literature of the Czech Academy of Sciences')?>, 
			<?= $this->transEsc('Institute of Literary Research of the Polish Academy of Sciences')?></div>
	</div>
	<div class="footer-column text-right">
		<div class="poweredby">
		<img style="width:160px; padding:3px; padding-top:10px;" src="<?= $this->HOST ?>themes/default/images/logos/Wikidata_Stamp_Rec_Light.svg" alt="<?=$this->transEsc('powered by wikidata')?>" />
		</div>
	</div>
  </div>
 
</footer> 

<div id="IsMobile"></div> 
<div id="myInfoCloud"></div>

<?= $this->helper->Modal() ?>



<img src="<?= $this->HOST ?>/themes/default/images/beta_version.svg" alt="This is beta version" class="beta_version">

<?php 
if (!empty($this->linkParts[2]) && ($this->linkParts[2]<>'ajax'))
	echo $this->user->adminMenu();

?>