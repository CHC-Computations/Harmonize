
<footer class="hidden-print">
  <div class="footer-container">
    <div class="footer-column">
		<a href="https://clb.ucl.cas.cz/en/"><img class="footer-logo-bg" src="<?= $this->HOST ?>themes/default/images/logos/Libri_logotypy_CLB-1-768x271.png" alt="<?= $this->transEsc('Czech Literary Bibliography')?>" /></a>
		<a href="https://ucl.cas.cz/en/"><img class="footer-logo-bg" src="<?= $this->HOST ?>themes/default/images/logos/Libri_logotypy_UPCL-1-768x271.png" alt="Ústav pro českou literaturu" /></a>
	</div>
    <div class="footer-column">
		<a href="https://ibl.waw.pl/"><img class="footer-logo-bg" src="<?= $this->HOST ?>themes/default/images/logos/Libri_logotypy_IBL-1-768x269.png" alt="Instytut Badań Literackich" /></a>
		<a href="http://pbl.ibl.poznan.pl/index.php"><img class="footer-logo-bg" src="<?= $this->HOST ?>themes/default/images/logos/Libri_logotypy_PBL-1-768x270.png" alt="Polska Bibliografia Literacka" /></a>
		<img class="footer-logo-bg" src="<?= $this->HOST ?>themes/default/images/logos/Libri_logotypy_DHC-1-768x269.png" alt="Digital Humanities Centre" />
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

<!-- Matomo -->
<script>
  var _paq = window._paq = window._paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//testlibri.ucl.cas.cz/matomo/";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '1']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Matomo Code -->

<img src="<?= $this->HOST ?>/themes/default/images/beta_version.svg" alt="This is beta version" class="beta_version">

<?php 
if (!empty($this->linkParts[2]) && ($this->linkParts[2]<>'ajax'))
	echo $this->user->adminMenu();

?>