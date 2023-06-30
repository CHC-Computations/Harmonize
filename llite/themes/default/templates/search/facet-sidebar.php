
<div class="facets-header">
<span type="button" class="ico-btn" id="slideoutbtn" onclick="facets.SlideOut(); " title="<?= $this->transEsc('Hide facet panel') ?>"><i class="fa fa-angle-left"></i></span>

<span type="button" class="ico-btn" id="collapse_all_button" onclick="$('.collapse'+'.sidefl').collapse('hide'); " title="<?= $this->transEsc('Collapse all') ?>"><i class="fa fa-angle-double-up"></i></span>
<span type="button" class="ico-btn" id="uncollapse_all_button" onclick="$('.collapse'+'.sidefl').collapse('show'); " title="<?= $this->transEsc('Show all') ?>"><i class="fa fa-angle-double-down"></i></span>

<h4><?= $this->transEsc('Narrow Search') ?></h4>
</div>
<div id="loadbox_all_facets" class="facets-body">
	
		<div style="filter: blur(5px);">	
			<?= str_repeat('	
			<div class="panel panel-default" id="62a1892449a43_panel">
				<div class="panel-heading" role="tab">
					<button type="button" class="close"><span class="ph-caret-up-bold"></span></button> Facet title
					<a class="facet-btn" >
						<i class="ph-chart-pie-slice-bold" title="more options"></i>
					</a>
					
				</div>
				<div id="loader_body" role="tabpanel" class="sidefl collapse in" aria-expanded="true" style="">
					<div class="panel-body">
						<a href="#" class="facet js-facet-item" data-title="Czeska Bibliografia Literacka" data-count="2246786">
						<span class="text">loading option...</span>
						<span class="badge">0.000.000</span>
					</a><a href="#" class="facet js-facet-item" data-title="Polska Bibliografia Literacka" data-count="1118636">
						<span class="text">loading option...</span>
						<span class="badge">0.000.000</span>
					</a><a href="#" class="facet js-facet-item" data-title="Biblioteka Narodowa Finlandii" data-count="503921">
						<span class="text">loading option...</span>
						<span class="badge">0.000.000</span>
					</a>	
					</div>
					
				</div>
			</div>
			',10) ?>
			
		</div>
	<?= $this->helper->loader2(); ?>
</div>
  
<script>
	facets.Load('all_facets','<?= $this->facetsCode ?>');
</script>

 