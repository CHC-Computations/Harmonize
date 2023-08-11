<?php 
require_once('functions/klasa.forms.php');
$this->addClass('forms', 	new forms()); 
$this->forms->values($this->GET);
$facets = $this->getConfig('search');
$cookies = $this->getConfig('cookies');

if (!empty($this->params[2]))
	$currentSE = $this->params[2];
	else
	$currentSE = 'home';



foreach ($this->getIniParam('search', 'basicSearches') as $k=>$v) {
	$opt[$k] = $this->transEsc( $v );
	}
$cleanLink = '';
if (!empty($this->GET['lookfor']))
	$cleanLink = '<div class="searchRemoveBtn"><a href="'.$this->selfUrl($_SERVER['QUERY_STRING'], '').'" title="'.$this->transEsc('Clean up').'"><i class="glyphicon glyphicon-remove"></i></a></div>';


$myListCount = $this->buffer->myListCount();

?>

	<?= $this->render('core/cookies.php', ['msg' =>$cookies]) ?>
	<header class="hidden-print">
		<a class="sr-only" href="#content"><?= $this->transEsc('Skip to content') ?></a>
		<div class="navbar header">
	
			<div class="header-left">
				<div class="core-menu">
					<div class="core-menu-items">
						<?= $this->render('cms/header-menu.php') ?>
					</div>
					<a class="core-menu-brand" href="<?= $this->basicUri('') ?>" title="<?= $this->transEsc('Home page') ?>"><img src="<?= $this->HOST ?>/themes/default/images/<?= $this->settings->www->logo?>" alt="<?= $this->settings->www->title ?>"><span class="sr-only"><?= $this->transEsc('Literary Bibliography Research Infrastructure') ?></span></a>
				</div>
			</div>
  
			<div class="header-right" id="header-collapse">
				<nav>
				
					<?php if (empty($this->user->LoggedIn)): ?>
						
						<div id="loginOptions" class="userBox">
							<div class="userBox-toggle">
								<a OnClick="coreMenu.Show();$('.userBox-menu').hide();" ><i class="ph-list-bold"></i><span class="sr-only">menu</span></a>
								<a onClick="$('.userBox-menu').toggle();" title="<?= $this->transEsc('User menu')?>" ><i class="ph-user-bold" id="userMenu-icon"></i><span class="sr-only"><?= $this->transEsc('User menu')?></span></a>
							</div>
							<div class="userBox-menu">
								<div class="userBox-body">
									<ul class="userBox-options">
										<li id="cartSummary">
											<a id="cartItems" data-lightbox title="<?= $this->transEsc('Go to handy list')?>" href="<?= $this->baseUrl('user/cart')?>" data-toggle="tooltip" data-placement="bottom" >
												<i class="ph-shopping-cart-bold"></i> <strong id="myListCount"><?= $myListCount ?></strong> 
												<span ><?= $this->transEsc('on handy list')?></span>
											</a>
										</li>
									</ul>
									<ul class="userBox-options">										
										<li><a href="<?= $this->baseUrl('user/login')?>"><i class="ph-power-bold"></i> <?= $this->transEsc('Login / register')?></a></li>
									</ul>
									<ul class="userBox-languages">
										<?php foreach($this->lang['available'] as $langCode => $langName): ?>
											<?php $linkParts=$this->linkParts; $linkParts[1]=$langCode; ?>
											<li <?=$this->userLang == $langCode ? ' class="active"' : ''?>>
												<a href="<?= $this->HOST.implode('/',$linkParts); ?>?<?= http_build_query($this->GET) ?>" >
													<?= $langName ?>
													<span style="background-image: url('<?= $this->HOST?>themes/default/images/languages/<?=$langCode?>.svg'); float:right; background-size:cover; background-position:center; display:inline-block; height:16px; width:16px; padding:4px;" alt="flag of <?=$langName?>" >
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
								
							</div>
						</div>
						
						
					<?php else: ?>	
						<div id="loginOptions" class="userBox">
							<div class="userBox-toggle">
								<a OnClick="coreMenu.Show();$('.userBox-menu').hide();" ><i class="ph-list-bold"></i><span class="sr-only">menu</span></a>
								<a onClick="$('.userBox-menu').toggle();" title="<?= $this->transEsc('User menu')?>" ><i class="ph-user-circle-bold" id="userMenu-icon"></i><span class="sr-only"><?= $this->transEsc('User menu')?></span></a>
								
							</div>
							<div class="userBox-menu">
								<div class="userBox-header">
									<h3><?=$this->user->getUserName()?></h3>
								</div>
								<div class="userBox-body">
									<ul class="userBox-options">
										<li id="cartSummary">
											<a id="cartItems" data-lightbox title="<?= $this->transEsc('Go to handy list')?>" href="<?= $this->baseUrl('user/cart')?>" data-toggle="tooltip" data-placement="bottom" >
												<i class="ph-shopping-cart-bold"></i> <strong id="myListCount"><?= $myListCount ?></strong> 
												<span><?= $this->transEsc('on handy list')?></span>
											</a>
										</li>
									</ul>
									<ul class="userBox-options">										
										<li><a href="<?= $this->baseUrl('user/login')?>"><i class="ph-faders-bold"></i> <?= $this->transEsc('User account')?></a></li>
										<li><a href="<?= $this->baseUrl('user/logout')?>"><i class="ph-power-bold"></i> <?= $this->transEsc('Logout')?></a></li>
									</ul>
									<ul class="userBox-languages">
										<?php foreach($this->lang['available'] as $langCode => $langName): ?>
											<?php $linkParts=$this->linkParts; $linkParts[1]=$langCode; ?>
											<li <?=$this->userLang == $langCode ? ' class="active"' : ''?>>
												<a href="<?= $this->HOST.implode('/',$linkParts); ?>" >
													<?= $langName ?>
													<span style="background-image: url('<?= $this->HOST?>themes/default/images/languages/<?=$langCode?>.svg'); float:right; background-size:cover; background-position:center; display:inline-block; height:16px; width:16px; padding:4px;" alt="flag of <?=$langName?>" >
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
								
							</div>
						</div>
					<?php endif; ?>
						
	
				</nav>
				
				
			</div>
  
			<div class="header-body">
				<?php 
				if ($this->templatesExists("searchBoxes/$currentSE-searchbox.php"))
					echo $this->render("searchBoxes/$currentSE-searchbox.php") 
				?>
			</div> 

  

		</div>
	</header>
	<div class="breadcrumbs"></div>
	<div class="bg-off" OnClick="coreMenu.Hide(); $('.userBox-menu').hide();"></div>
					
	
	<input type="hidden" name="hiddenFieldURL" id="hiddenFieldURL" value="<?= $this->HOST ?><?= $this->userLang ?>/">
	<input type="hidden" name="hf_base_url" id="hf_base_url" value="<?= $this->HOST ?>">
	<input type="hidden" name="hf_user_language" id="hf_user_language" value="<?= $this->userLang ?>">
	<input type="hidden" name="hf_get" id="hf_get" value="<?= $_SERVER['QUERY_STRING'] ?>">
	<input type="hidden" name="hf_request_uri" id="hf_request_uri" value="<?= $_SERVER['REQUEST_URI'] ?>">
	

