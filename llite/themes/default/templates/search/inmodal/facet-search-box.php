<form class="form-horizontal">

  <div class="form-group has-feedback">
    <div class="col-sm-6">
	  <input type="hidden" id="hf_facet" name="hf_facet" value="<?= $currFacet ?>">
      <input type="text" class="form-control" id="ajaxSearchInput" name="search" placeholder="<?= $this->transEsc('Search') ?>" onkeyup="facets.Search();">
      <span class="glyphicon glyphicon-search form-control-feedback"></span>
    </div>
	<div class="col-sm-6">
	  <?= $this->transEsc('Sort') ?>:
	  <label><input type=radio name="facetsort" id="facetsort_c" value="count" checked OnChange="facets.Search();"> <?= $this->transEsc('Result count') ?></label>
	  <label><input type=radio name="facetsort" id="facetsort_i" value="index" OnChange="facets.Search();"> <?= $this->transEsc('Alphabetical') ?></label>
	  
	</div>
  </div>

</form> 

<div id="ajaxSearchBox">
	<div class="loader"></div>
</div>
<div id="ajaxSearchChosen"></div>
<script>
	facets.Search();
	facets.AddRemove('state');
</script>  

			