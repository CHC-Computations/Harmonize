<?php 

$coreFields = $this->record->getCoreFields();


$barMenu ='<div class="toolbar">
		  <ul class="record-nav nav nav-pills hidden-print"> 
      <li><a class="cite-record" data-lightbox href="/vufind/Record/001556484/Cite" rel="nofollow" title="Cite this" data-toggle="tooltip"><i class="fa fa-asterisk" aria-hidden="true"></i><span class="sr-only"> Cite this</span></a></li>
      <li><a class="mail-record" data-lightbox href="/vufind/Record/001556484/Email" rel="nofollow" title="Email this" data-toggle="tooltip"><i class="fa fa-envelope" aria-hidden="true"></i><span class="sr-only"> Email this</a></span></li>

        <li class="dropdown">
      <a class="export-toggle dropdown-toggle" data-toggle="dropdown" href="/vufind/Record/001556484/Export" rel="nofollow"><i class="fa fa-download" aria-hidden="true" title="Export Record" data-toggle="tooltip"></i><span class="sr-only"> Export Record</span></a>
      <ul class="dropdown-menu dropdown-menu-right" role="menu">
                  <li><a href="/vufind/Record/001556484/Export?style=MARC" rel="nofollow">Export to  MARC</a></li>
                  <li><a href="/vufind/Record/001556484/Export?style=MARCXML" rel="nofollow">Export to  MARCXML</a></li>
                  <li><a href="/vufind/Record/001556484/Export?style=RDF" rel="nofollow">Export to  RDF</a></li>
                  <li><a href="/vufind/Record/001556484/Export?style=BibTeX" rel="nofollow">Export to  BibTeX</a></li>
                  <li><a href="/vufind/Record/001556484/Export?style=RIS" rel="nofollow">Export to  RIS</a></li>
              </ul>
    </li>
  
      <li>
              <a class="save-record" data-lightbox href="/vufind/Record/001556484/Save" rel="nofollow" title="Save to List" data-toggle="tooltip"><i class="fa fa-star" aria-hidden="true"></i><span class="sr-only"> Save to List</span></a>
          </li>
          <span class="btn-bookbag-toggle" data-cart-id="001556484" data-cart-source="Solr">
    <a href="#" class="cart-add hidden correct">
      <span class="cart-link-label btn-type-add" title="Add to Book Bag" data-toggle="tooltip"><span class="sr-only"> Add to Book Bag</span></span>
    </a>
    <a href="#" class="cart-remove hidden">
      <span class="cart-link-label btn-type-minus" title="Remove from Book Bag" data-toggle="tooltip"><span class="sr-only">Remove from Book Bag</span></span>
    </a>
    <noscript>
      <form method="post" name="addForm" action="/vufind/Cart/Processor">
        <input type="hidden" name="ids[]" value="Solr&#x7C;001556484" />
                  <input class="btn btn-default" type="submit" name="add" value="Add&#x20;to&#x20;Book&#x20;Bag"/>
              </form>
    </noscript>
  </span>
</ul>
		</div>';


?>
<div class="graybox">
  <div class="infopage">
    <div class="infopage-header">
	   <h1 property="name"><?= $this->record->getTitle() ?></h1>
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