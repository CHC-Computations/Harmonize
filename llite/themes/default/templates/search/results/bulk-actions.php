<?php 
	$currentCore = 'biblio';
	$exports2str = '';
	if ($this->user->isLoggedIn() && $this->user->hasPower('admin')) {
		$exports2 = $this->configJson->$currentCore->exports->formats ?? new stdclass; 
		if (!empty($exports2)) {
			$titleStr = $this->transEsc('Download as');
			$exports2str = '<li>
					<div class="btn-group">
					<button class="toolbar-btn dropdown-toggle" data-toggle="dropdown" data-original-title="'.$titleStr.'">
						<i class="ph-cloud-arrow-down-bold"></i>
						<span> '.$titleStr.'</span>
					</button>
					<ul class="dropdown-menu">
					';
			foreach ($exports2 as $exportKey=>$values) {
				$exports2str .= '<li><a OnClick="results.export2(\''. $titleStr.' '.$values->title.'\',\''.$exportKey.'\',\''.$this->facetsCode.'\');">'.$values->title.'</a></li>';
				}
			$exports2str .= '
				</ul>
				</div>
				</li>';
			}
		} 
	
	
	$exports = $this->getIniParam('export', 'ExportList');
	$id_list_str = implode(',',$this->solr->idList()); 
	
	$exportList = '<ul class="dropdown-menu">';
	foreach ($exports as $exportKey=>$exportName) {
		$exportList .= '<li><a OnClick="results.Export(\''. $this->transEsc('Download').' '.$exportName.'\',\''.$exportKey.'\',\''.$this->facetsCode.'\');">'.$exportName.'</a></li>';
		}
	$exportList .= '</ul>';
	#echo "<pre>".print_r($exports,1)."</pre>";
?>

<nav class="bulkActionButtons">
   
	<div class="bulk-actions">
	  <ul class="action-toolbar">
		<?php if ($this->user->isLoggedIn()): ?>
			<li>
				<button id="selectAll" OnClick="results.selectAll('<?=$id_list_str?>');" class="toolbar-btn" type="button" data-toggle="tooltip" data-original-title="<?= $this->transEsc('Actions on multiple results') ?>">
					<i class="ph-check-square-offset-bold" id=selectAllIcon></i>
					<span > <?=$this->transEsc('Actions on multiple results')?></span>
					<span id="SelectAllResponse"></span>
				</button>
			</li>
		<?php endif; ?>
        
       
		<!--li>
			<button id="ribbon-email" OnClick="results.Email()" class="toolbar-btn btn-type-email" type="submit" name="email" value="1" title="" data-toggle="tooltip" data-original-title="<?= $this->transEsc('Send selected results by e-mail')?>">
				<span class="sr-only"> e-mail</span>
			</button>
		</li-->
		<?= $exports2str ?>
        <?php if (!empty($exports)) : ?>
			<li>
				<div class="btn-group">
				<button class="toolbar-btn dropdown-toggle" data-toggle="dropdown" data-original-title="<?= $this->transEsc('Export')?>">
					<i class="ph-cloud-arrow-down-bold"></i>
					<span> <?= $this->transEsc('Download')?></span>
				</button>
				<?= $exportList ?>
				</div>
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




