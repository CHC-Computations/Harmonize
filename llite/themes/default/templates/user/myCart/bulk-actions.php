<?php 
	
	$exports = $this->getIniParam('export', 'ExportList');
	$id_list_str = implode(',',$this->buffer->myListResults()); 
	
	
	#echo "<pre>".print_r($exports,1)."</pre>";
?>

<nav class="bulkActionButtons">
   
	<div class="bulk-actions">
	  <ul class="action-toolbar">
		<li>
			<button id="selectAll" OnClick="results.selectAll('<?=$id_list_str?>', 'reload');" class="toolbar-btn" type="button" data-toggle="tooltip" data-original-title="<?= $this->transEsc('Remove all') ?>">
				<i class="ph-trash-bold" id=selectAllIcon></i>
				<span > <?=$this->transEsc('Remove all')?></span>
				<span id="SelectAllResponse"></span>
			</button>
		</li>
       

        <?php if (!empty($exports)) : ?>
			<li>
				<button id="ribbon-export" OnClick="results.Export('<?= $this->transEsc('Export')?>','<?=$this->facetsCode?>')" class="toolbar-btn" data-toggle="tooltip" data-original-title="<?= $this->transEsc('Export')?>">
					<i class="ph-arrow-square-out-bold"></i>
					<span> <?= $this->transEsc('Export')?></span>
				</button>
			</li>
		<?php endif; ?>
        <li>
			<button id="ribbon-print" OnClick="results.Print();" class="toolbar-btn" type="button" data-toggle="tooltip" data-original-title="<?= $this->transEsc('Print') ?>">
				<i class="ph-printer-bold"></i>
				<span > <?=$this->transEsc('Print')?></span>
			</button>
		</li>
        <!--
		<li><button id="ribbon-save" class="toolbar-btn btn-type-save" type="submit" name="saveCart" value="1" title="" data-toggle="tooltip" data-original-title="Zapisz zaznaczone książki"><span class="sr-only"> Zapisz</span></button></li>
        <li><button id="updateCart" type="submit" class="toolbar-btn btn-type-add" name="add" value="1" title="" data-toggle="tooltip" data-original-title="Dodaj do listy podręcznej"><span class="sr-only"> Dodaj do listy podręcznej</span></button></li>
		-->
	  </ul>
	</div>
</nav> 



