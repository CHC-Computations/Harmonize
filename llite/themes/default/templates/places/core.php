<?php 

$barMenu = "";

$t = explode('.',$rec->id);
$id = $t[0];


?>
<div class="graybox">
  <div class="infopage">
    <div class="infopage-header">
	   <h1 property="name"><?= $this->record->fullName ?> <small><?= $id ?></small></h1>
	</div>
	<div class="record">
	
		
		<div class="record-left-panel">
			<div class="thumbnail">
			<img src="<?= $this->HOST ?>themes/default/images/no_cover.png" alt="no cover found" class="img img-responsive">
			</div>
		</div>
		<div class="record-main-panel">
			
			<?= $this->record->getDescription() ?>
			
			<ul class="detailsview">
				<?php foreach ($coreFields as $func=>$current): ?>
					<dl class="detailsview-item">
					  <dt class="dv-label"><?=$this->transEsc($current['label'])?>:</dt>
					  <dd class="dv-value"><?=$current['content']?></dd>
					</dl>
				<?php endforeach; ?>
			</ul>
			
			
		</div>
		<div class="record-right-panel">
			<?= $barMenu ?>
		</div>
		
	</div>
	
	<div id="apiBox"><div class="text-right"><button class="btn btn-primary" OnClick="page.ajax('apiBox','load.from.geonames/<?= $id?>');">Upgrade</button></div></div>
				
	<br/><br/>
				
				
	
	<div class="tabs-panel">
		<?= 
		$this->helper->tabsCarousel([
			'details' 	=> ['label'=>'Marc view', 'content'=>$this->record->drawMarc()],
			'jsonview' 	=> ['label'=>'Json view', 'content'=>"<pre style='background-color:transparent; border:0px;'>".print_r($this->record->fullRecord, 1)."</pre>" ],
			'map' 		=> ['label'=>'Map', 'content'=>'<img src="'.$this->HOST.'themes/default/images/extras/word2.svg" class="img img-responsive">' ],
			], 'details');
		?>
	  
    </div>
	  
	 
  </div>
</div>