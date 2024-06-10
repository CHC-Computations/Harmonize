<?php 
if (empty($this)) die;
$this->addClass('buffer', 	new buffer($this)); 
$currPage = $this->getCurrentPost();
if (!empty($currPage['title'])) $this->setTitle($currPage['title']);

$export = $this->getConfig('export');
$facets = $this->getConfig('search');
$facets = $this->getConfig('facets');
$this->saveUserParam('biblio:sort', $this->configJson->biblio->pagination->default ?? null);



echo $this->render('head.php');
echo $this->render('core/header.php');

if ($currPage['url']=='home') {
	
	$this->addJS("page.ajax('post_ajax_area', 'libri.summary');");
	echo '
		<div class="cms_box_home">
			<div class="container" id="content">
				<div class="main">
		';
	if (!empty($currPage['script'])) {
		echo '<div id="post_ajax_area"></div>';
		$this->addJS($currPage['script']);
		}
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
	if (is_array($currPage) && !empty($currPage['title'])) {
		echo $this->render('cms/post.php', ['post' => $currPage ]);
		} else if ($this->templatesExists('cms/errors/'.$this->userLang.'-no-post.php'))
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






