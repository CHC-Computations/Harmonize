<?php 
if (empty($this)) die;
$this->addClass('buffer', 	new marcBuffer()); 
$currPage = $this->getCurrentPost();
$this->setTitle($currPage['title']);

$export = $this->getConfig('export');
$facets = $this->getConfig('search');
$facets = $this->getConfig('facets');
$this->saveUserParam('sort', $this->config['search']['pagination']['default_sort']);



echo $this->render('head.php');
echo $this->render('core/header.php');

if ($currPage['url']=='home') {
	$currPage['script'].="<div id='ajax_libri_summary'></div>";
	$this->addJS("page.ajax('ajax_libri_summary', 'libri.summary');");
	echo '
		<div class="cms_box_home">
			<div class="container" id="content">
				<div class="main">
		';
	echo '<h1 class="ubuntu-title">'.$this->transEsc($this->settings->www->title).'</h1>';
	echo $currPage['script'];
	echo '<div class="home-page-menu">';
	$icons = $this->getIniParam('search','searchCoresIcons');
	foreach ($this->getIniParam('search','searchCores') as $url=>$coreName) 
		echo '<a class="core-menu-item" href="'.$this->baseUrl($url.'/results/').'"><i style="font-size:1.8em;" class="'.$icons[$url].'"></i><br/>'.$this->transESC($coreName).'</a>';
	echo '</div>';
	echo '			
				</div>
			</div>
		</div>
		';
	} else {
	echo '
		<div class="cms_box">
			<div class="container" id="content" style="background-color:rgba(255,255,255,0.8);">
				<div class="main">
		';
	if (is_array($currPage))
		echo $this->render('cms/post.php', ['post' => $currPage ]);
		else if ($this->templatesExists('cms/errors/'.$this->userLang.'-no-post.php'))
			echo $this->render('cms/errors/'.$this->userLang.'-no-post.php', ['post' => $currPage ]);	
			else 
			echo $this->render('cms/errors/'.$this->defaultLanguage.'-no-post.php', ['post' => $currPage ]);	
	echo '			
				</div>
			</div>
		</div>
		';
	}

echo $this->render('core/footer.php');






