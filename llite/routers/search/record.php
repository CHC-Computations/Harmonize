<?php 
if (empty($this)) die;
require_once('functions/klasa.helper.php');
require_once('functions/klasa.converter.php');
require_once('functions/klasa.maps.php');
require_once('functions/klasa.wikidata.php');

$x = count($this->routeParam)-1;

$tmp = explode('.', $this->routeParam[$x]);

$this->addClass('solr', new solr($this->config));
$this->addClass('buffer', new marcBuffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->addClass('convert', 	new converter()); 
$this->buffer->setSql($this->sql);

$rec_id = current($tmp);
$format = end($tmp);
$fn = $rec_id.'.'.$format; 

switch ($format) {
	case 'html' : 
			$Tmap = [];
			$rec_id=str_replace('.html', '', $this->routeParam[$x]);
			$record = $this->solr->getRecord('biblio', $rec_id);
			if (!empty($record->id)) {
				#$marcJson = $this->buffer->getJsonRecord($record->id, $record->fullrecord);
				$marcJson = $this->convert->mrk2json($record->fullrecord);
				$this->addClass('record', new marc21($marcJson, $record));
				$this->setTitle($this->record->getTitle());

				$similar = [];
				if (!empty($this->record->solrRecord->title_sort)) {
					$query['q']=[ 
							'field' => 'q',
							'value' => 'title_sort:"'.$this->record->solrRecord->title_sort.'" AND -id:'.$record->id
							];
					$query['fl']=[ 
							'field' => 'fl',
							'value' => "id,title,author"
							];
						
					$similar = $this->solr->getQuery('biblio', $query);
					$similar = $this->solr->resultsList();
					}
					


				$regions = $this->record->getRegion();
				
				
				if ($this->routeParam[0]=='inmodal') {
					$i = count($this->routeParam)-1;
					$t = $this->routeParam;
					unset($t[$i]);
					#echo print_R($t,1);
					echo $this->render('record/'.implode('/',$t).'.php', ['rec'=>$record, 'Tmap'=>$Tmap]);
					} else {
					echo $this->render('head.php');
					echo $this->render('core/header.php');
					echo '<div class="main">';
					
					echo $this->render('record/core.php', ['rec'=>$record, 'Tmap'=>$Tmap, 'regions'=>$regions, 'similar'=>$similar ]);
					echo '</div>';
					echo $this->render('core/footer.php');
					}		
				} else {
					$this->setTitle($this->transEsc("No data"));
					echo $this->render('head.php');
					echo $this->render('core/header.php');
					echo '<div class="main">';
					
					echo $this->render('record/no-core.php', ['rec'=>$rec_id, 'Tmap'=>$Tmap]);
					echo '</div>';
					echo $this->render('core/footer.php');
					
				}
			break;
	case 'mrk' : 
			
			$record = $this->solr->getRecord('biblio', $rec_id);
			#$marcJson = $this->buffer->getJsonRecord($record->id, $record->fullrecord);
			$marcJson = $this->convert->mrk2json($record->fullrecord);

			require_once('functions/klasa.exporter.php');
			$this->addClass('exporter', new exporter()); 

			$plik = $this->exporter->toMRK($marcJson);
			$len=strlen($plik);
			header("Content-type: application/$format");
			header("Content-Length: $len");
			header("Content-Disposition: inline; filename=$fn");
			print $plik;

			break;
			
			
	case 'mrc' :
			$record = $this->solr->getRecord('biblio', $rec_id);
			#$marcJson = $this->buffer->getJsonRecord($record->id, $record->fullrecord);
			$marcJson = $this->convert->mrk2json($record->fullrecord);

			require_once('functions/klasa.exporter.php');
			$this->addClass('exporter', new exporter()); 
			
			if ($record->record_format == 'marc') {
				$plik = $record->fullrecord;
				$len=strlen($plik);
				header("Content-type: application/$format");
				header("Content-Length: $len");
				header("Content-Disposition: inline; filename=$fn");
				print $plik;
				} else {
				$error_reading_file = $this->render('head.php');
				$error_reading_file .= $this->render('core/header.php');
				$error_reading_file .= '<div class="container">';
				$error_reading_file .= '<div class="main">';
				$error_reading_file .= '<p style="padding:100px; text-align:center;">error while loading the file</p>';
				$error_reading_file .= '</div>';
				$error_reading_file .= '</div>';
				$error_reading_file .= $this->render('core/footer.php');		
				echo $error_reading_file;
				}

			break;
			
	case 'marcxml' : 
			$record = $this->solr->getRecord('biblio', $rec_id);
			#$marcJson = $this->buffer->getJsonRecord($record->id, $record->fullrecord);
			$marcJson = $this->convert->mrk2json($record->fullrecord);

			require_once('functions/klasa.exporter.php');
			$this->addClass('exporter', new exporter()); 

			$plik = $this->exporter->XMLheader();
			if (empty($this->GET['isPart']))
				$plik .= $this->exporter->XMLcollection($this->exporter->toMARCXML($marcJson));
				else 
				$plik .= $this->exporter->toMARCXML($marcJson);	
			$len=strlen($plik);
			header("Content-type: application/$format");
			header("Content-Length: $len");
			header("Content-Disposition: inline; filename=$fn");
			print $plik;
			
			break;		
			
	case 'json' :
			$record = $this->solr->getRecord('biblio', $rec_id);
			if (isset($this->GET['solr'])) {
				$plik = json_encode($record);
				} else {
				$marcJson = $this->convert->mrk2json($record->fullrecord);	
				$plik = json_encode($marcJson);
				}
			$len=strlen($plik);
			header("Content-type: application/json");
			header("Content-Length: $len");
			header("Content-Disposition: inline; filename=$fn");
			print $plik;	
			break;
	/*
	case 'rdf' :
			$host = str_replace($this->ignorePath, '' ,$this->HOST);
			$plik = file_get_contents($host."vufind/Record/$rec_id/Export?style=RDF");
			$len=strlen($plik);
			header("Content-type: application/$format");
			header("Content-Length: $len");
			header("Content-Disposition: inline; filename=$fn");
			print $plik;
			
			break;
	case 'btx' :
			$host = str_replace($this->ignorePath, '' ,$this->HOST);
			$plik = file_get_contents($host."vufind/Record/$rec_id/Export?style=BibTeX");
			$len=strlen($plik);
			header("Content-type: application/$format");
			header("Content-Length: $len");
			header("Content-Disposition: inline; filename=$fn");
			print $plik;
			
			break;
	
	case 'ris' :
			$host = str_replace($this->ignorePath, '' ,$this->HOST);
			$plik = file_get_contents($host."vufind/Record/$rec_id/Export?style=RIS");
			$len=strlen($plik);
			header("Content-type: application/$format");
			header("Content-Length: $len");
			header("Content-Disposition: inline; filename=$fn");
			print $plik;
			
			break;
	*/
	
	default :

			$record = $this->solr->getRecord('biblio', $rec_id);
			$marcJson = $this->convert->mrk2json($record->fullrecord);	

			$this->addClass('record', new marc21($marcJson));

			$this->setTitle($record->title);

			echo $this->render('head.php');
			echo $this->render('core/header.php');
			echo '<div class="container">';
			echo '<div class="main">';
			echo '<h1 style="padding:100px; text-align:center;"><i class="fa fa-face-frown"></i> Sorry, I don\'t know this format yet. </h1>';
			echo '<p style="padding:100px; text-align:center;">Trying to get: <b>'.$rec_id.'</b> in format: <b>'.$format.'</b></p>';
			echo '</div>';
			echo '</div>';
			echo $this->render('core/footer.php');
			break;
			

}
