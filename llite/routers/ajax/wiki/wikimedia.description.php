<?php 

#echo $this->helper->pre($this->POST);

if (!empty($this->POST['pdata']['API'])) {
	$apiLink = $this->POST['pdata']['API'];
	$wikiLink = $this->POST['pdata']['wikipedia'];
	$content = @file_get_contents($apiLink);
	$content = json_decode($content);
	#echo $this->helper->pre($content);
	$tmp = explode('.wikipedia.org', $this->POST['pdata']['wikipedia'])[0];
	$lang_code = str_replace('https://', '', $tmp);
	$wikiq = $this->POST['pdata']['wikiq'];
	#echo "<strong>$lang_code</strong> ";
	if (!empty($content->query->pages)) {
		foreach ($content->query->pages as $key=>$page) {
			if (!empty($page->extract)) {
				$content = $page->extract;
				echo $content;
				echo '<div class="text-right"><a href="'. $this->POST['pdata']['wikipedia'].'">'. $this->transEsc('More information on Wikipedia').'</a></div>';
				
				$this->psql->querySelect("
					INSERT INTO wikipedia_descriptions (wikiq, lang_code, wikilink, description, last_edit) 
						VALUES ('$wikiq', '$lang_code', {$this->psql->string($wikiLink)}, {$this->psql->string($content)}, now())
						ON CONFLICT (wikiq, lang_code)
						DO UPDATE SET description = {$this->psql->string($page->extract)}, last_edit = now()
					RETURNING wikiq;
					");
				}
			}
		}
	}


?>