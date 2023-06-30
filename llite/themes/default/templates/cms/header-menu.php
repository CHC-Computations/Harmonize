<?php 
if (!empty($this->params[2]))
	$currentSE = $this->params[2];
	else
	$currentSE = '';

if (!empty($this->params[3]))
	$currentPage = $this->params[3];
	else
	$currentPage = '';


?>


<button class="close" OnClick="coreMenu.Hide()" type="button"><i class="ph-x-bold"></i></button>
<ul>
<li class="core-menu-header"><?= $this->transEsc('Search in') ?>:</li>
<?= $this->render('cms/menu-search-cores.php', ['menu' => $this->getIniParam('search','searchCores'), 'currPage' => $currentSE ]) ?>
<li class="core-menu-header"><?= $this->transEsc('Menu') ?>:</li>
<?= $this->render('cms/menu-in-header.php', ['menu' => $this->getMenu(1), 'currPage' => $currentPage ]) ?>
</ul>