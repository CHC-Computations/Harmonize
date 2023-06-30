<?php 
$time = time();
?>

<!DOCTYPE html>
<html lang="<?= $this->langCode ?>">
	<head>
	<title><?= $this->title ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="Robots" content="all, index, follow">
		<meta name="author" content="Marcin Giersz">
		
		<link rel="stylesheet" media="screen" href="https://fontlibrary.org//face/lato" type="text/css" async defer /> 
		<link rel="stylesheet" media="screen" href="https://fontlibrary.org//face/raleway" type="text/css" async defer/> 
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="https://accounts.google.com/gsi/client" async defer></script>

		<script src="https://kit.fontawesome.com/24b479c936.js" crossorigin="anonymous"></script>
		<script src="https://unpkg.com/phosphor-icons"></script>
		<!-- script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script -->

		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css"  
				integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ==" crossorigin=""/>
		<script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"
				integrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ=="	crossorigin=""></script>
				
		
				
		<?= $this->head->JS ?>       	
		<?= $this->head->CSS ?>       	
		
		<meta name="viewport" content="width=device-width, initial-scale=1">	
	</head>
<BODY>