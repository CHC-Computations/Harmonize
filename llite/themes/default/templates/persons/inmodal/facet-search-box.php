<form class="form-horizontal">

  <div class="form-group has-feedback">
    <div class="col-sm-6">
	  <input type="hidden" id="hf_facet" name="hf_facet" value="<?= $currFacet ?>">
      <input type="text" class="form-control" id="ajaxSearchInput" name="search" placeholder="<?= $this->transEsc('Search') ?>" onkeyup="facets.Search();">
      <span class="glyphicon glyphicon-search form-control-feedback"></span>
    </div>
	<div class="col-sm-6">
	  
	</div>
  </div>

</form> 

<div id="ajaxSearchBox">
	<div class="loader"></div>
</div>
<div id="ajaxSearchChosen"></div>
<script>
	facets.Search('<?= $core ?>');
	facets.AddRemove('state', '<?= $core ?>');
</script>  

			