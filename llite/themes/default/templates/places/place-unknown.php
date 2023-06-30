<?php 





?>
<div class="graybox">
  <div class="infopage">
    <div class="infopage-header">
		<h1 property="name"><?= $this->transEsc('Place unknown') ?> <small></small></h1>
		
	</div>
	<div class="person-record">
		<div style="margin-top:100px; margin-bottom:100px;">
		<?= $this->transEsc('Sorry, we have no information about this place') ?>.
		</div>
		
		<?php 
		$id = $rec->id;
		
		echo '
				<div id="viafBox">'.$this->helper->loader( $this->transEsc('getting information from').' geonames').'</div>
				
				<br/><br/>
				
				<script>
				page.ajax("viafBox","load.from.geonames/'.$id.'");
				</script>
				';
		

		?>
	
	
	 
  </div>
</div>