<?php
if (file_exists('./import/outputfiles/counter.txt')) {
	echo '<div class="footerMsg">
			<div class="container">
				<div class="row">
					<div class="col-sm-2 text-center" >
						<img src="'. $this->HOST .'themes/default/images/svg/network-x.svg"/>
					</div>
					<div class="col-sm-10">
						'.$this->transEsc('Data reindexing is in progress. Some relations and values (e.g., the number of publications associated with the viewed item) may be outdated and their value will change in a few hours.').'
					</div>
				</div>	
			</div>
			</div>';
	}


$chcLink = 'https://chc.ibl.waw.pl/en/';
if ($this->userLang == 'pl')
	$chcLink = 'https://chc.ibl.waw.pl/pl/';


?>

<footer class="hidden-print">
  	<div class="row">
		<div class="col-sm-1 text-center hidden-sm hidden-xs" style="vertical-align:middle;">
			<img style="opacity:0.7; width:50px; padding-top:22px;" src="<?= $this->HOST ?>themes/default/images/libri_logo_white_b.svg" alt="<?=$this->transEsc('Logo Libri')?>" />
		</div>	
		<div class="col-sm-9">
			<div class="text-center">
			<a href="https://clb.ucl.cas.cz/en/"><img class="footer-logo-bg" src="<?= $this->HOST ?>themes/default/images/logos/Libri_logotypy_CLB-1-768x271.png" alt="<?= $this->transEsc('Czech Literary Bibliography')?>" /></a>
			<a href="https://ucl.cas.cz/en/"><img class="footer-logo-bg" src="<?= $this->HOST ?>themes/default/images/logos/Libri_logotypy_UPCL-1-768x271.png" alt="Ústav pro českou literaturu" /></a>
			<a href="https://ibl.waw.pl/"><img class="footer-logo-bg" src="<?= $this->HOST ?>themes/default/images/logos/Libri_logotypy_IBL-1-768x269.png" alt="Instytut Badań Literackich" /></a>
			<a href="http://pbl.ibl.poznan.pl/index.php"><img class="footer-logo-bg" src="<?= $this->HOST ?>themes/default/images/logos/Libri_logotypy_PBL-1-768x270.png" alt="Polska Bibliografia Literacka" /></a>
			<a href="<?= $chcLink ?>"><img class="footer-logo-bg" src="<?= $this->HOST ?>themes/default/images/logos/Libri_logotypy_DHC-1-768x269.png" alt="Digital Humanities Centre" /></a>
			</div>
			<br/>
			<br/>
			<div style="">Copyright (c) 2018-<?= date("Y") ?> Ústav pro českou literaturu AV ČR & Instytut Badań Literackich PAN</div>
			<div style="font-size:.9em;">
				<?= $this->transEsc('Design and development of the website')?>: 
				<?= $this->transEsc('Poznań Supercomputing and Networking Center')?>, 
				<?= $this->transEsc('Institute of Czech Literature of the Czech Academy of Sciences')?>,<br/> 
				<?= $this->transEsc('Institute of Literary Research of the Polish Academy of Sciences')?>
			</div>
		</div>
		<div class="col-sm-2 text-center">
			<img style="width:160px; padding:8px;" src="<?= $this->HOST ?>themes/default/images/logos/harm.svg" alt="Harmonize software logo" /><br/>
			<a href="https://vufind.org/vufind/" class="footer-logo-bg"  title="Harmonize is based on VuFind - visit VuFind web page">
			<small style="display:block;">Based on</small>
				<img style="width:120px; margin:8px; margin-top:0;" src="<?= $this->HOST ?>themes/default/images/logos/vufind-logo.svg" alt="<?=$this->transEsc('VuFind logo')?>" />
			</a><br/>
			<a href="https://wikidata.org/" class="footer-logo-bg"  title="Harmonize data is powered by wikidata - visit Wikidata web page">
				<img style="width:160px; padding:8px;" src="<?= $this->HOST ?>themes/default/images/logos/Wikidata_Stamp_Rec_Light.svg" alt="<?=$this->transEsc('powered by wikidata')?>" />
			</a>	
		</div>
		
	</div>
    <?= $this->renderLang('core/sponsor.php') ?>
    <div style="height:50px; display:block;"></div>

<div id="IsMobile"></div> 
<div id="myInfoCloud"></div>


<?= $this->helper->Modal() ?>



<a href="<?= $this->basicUri('/') ?>"><img src="<?= $this->HOST ?>/themes/default/images/beta_version.svg" alt="This is beta version" class="beta_version"></a>
<!-- Matomo Image Tracker-->
<img referrerpolicy="no-referrer-when-downgrade" src="https://literarybibliography.eu/matomo/matomo.php?idsite=1&amp;rec=1" style="border:0" alt="" />
<!-- End Matomo -->
<?php 
if ($this->user->isLoggedIn() && $this->user->hasPower('admin'))
	echo $this->user->adminMenu();

if (file_exists('./config/analytics.js')) 
	$this->JS[] = file_get_contents('./config/analytics.js');

$this->psql->querySelect("INSERT INTO elbstat_counter (pages) 
VALUES (1) 
ON CONFLICT (date) 
DO UPDATE SET pages = elbstat_counter.pages+1 
RETURNING pages;");

?>
</footer> 
</body></html>