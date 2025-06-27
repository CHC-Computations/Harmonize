<?php 
$time = time();
?>

<!DOCTYPE html>
<html lang="<?= $this->userLang ?>" class="light-mode">
	<head>
	<title><?= $this->title ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8 X-Content-Type-Options: nosniff">
		<meta name="generator" content="Harmonize2.1">
		<?php if ($this->configJson->settings->testMode) 
			echo '<meta name="robots" content="noindex,follow" />';
			else 
			echo '<meta name="Robots" content="all, index, follow">';
		?>
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="https://accounts.google.com/gsi/client" async defer></script>

		<script src="https://zasoby.kominkowo.pl/ckeditor-basic/ckeditor.js" async ></script>
		
		
		<meta property="og:type" content="website" />
		<meta property="og:title" content="European Literary Bibliography" />
		<meta property="og:description" content="Discover and explore literary information from across Europe in one place – with smart tools, open data, and multilingual access." />
		<meta property="og:image" content="https://literarybibliography.eu/themes/default/images/ELB-homePage.png" />
		<meta property="og:url" content="https://literarybibliography.eu/" />
		<meta property="og:site_name" content="European Literary Bibliography" />
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:title" content="European Literary Bibliography" />
		<meta name="twitter:description" content="Discover and explore literary information from across Europe in one place – with smart tools, open data, and multilingual access." />
		<meta name="twitter:image" content="https://literarybibliography.eu/themes/default/images/ELB-homePage.png" />
		<meta name="twitter:url" content="https://literarybibliography.eu/" />
		<script type="application/ld+json">
		{
		  "@context": "https://schema.org",
		  "@type": "WebSite",
		  "name": "European Literary Bibliography",
		  "url": "https://literarybibliography.eu/",
		  "description": "Discover and explore literary information from across Europe in one place – with smart tools, open data, and multilingual access.",
		  "image": "https://literarybibliography.eu/themes/default/images/ELB-homePage.png"
		}
		</script>
				
		<?= $this->head->JS ?>       	
		<?= $this->head->CSS ?>       	
		<?= $this->head->meta ?>
		<meta name="viewport" content="width=device-width, initial-scale=1">	
		<meta name="color-scheme" content="light">
		
	</head>
<BODY>