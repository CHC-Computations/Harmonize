<?php 
$time = time();
?>

<!DOCTYPE html>
<html lang="<?= $this->userLang ?>">
	<head>
	<title><?= $this->title ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8 X-Content-Type-Options: nosniff">
		<meta name="Robots" content="all, index, follow">
		<meta name="author" content="Marcin Giersz">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="https://accounts.google.com/gsi/client" async defer></script>

		<script src="https://kit.fontawesome.com/24b479c936.js" crossorigin="anonymous"></script>
		<script src="https://unpkg.com/phosphor-icons"></script>
		<!-- script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script -->

		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
			 integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
			 crossorigin=""/>
		<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
			 integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
			 crossorigin=""></script>
		<script src="https://unpkg.com/leaflet.minichart/dist/leaflet.minichart.min.js" charset="utf-8"></script>		
		
				
		<?= $this->head->JS ?>       	
		<?= $this->head->CSS ?>       	
		
		<meta name="viewport" content="width=device-width, initial-scale=1">	
	</head>
<BODY>