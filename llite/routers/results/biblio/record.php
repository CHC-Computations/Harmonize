<?php 
if (empty($this)) die;
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.helper.php');
require_once('functions/class.converter.php');
require_once('functions/class.maps.php');
require_once('functions/class.wikidata.php');
require_once('./functions/class.bookcart.php');



$recordCalled = $fn = end($this->routeParam);
$tmp = explode('.', $recordCalled);
$format = end($tmp);
$x = count($tmp)-1;
unset($tmp[$x]);
$rec_id = implode('.',$tmp);



$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->addClass('convert', 	new converter()); 
$this->addClass('wikiData', new wikiData($this)); 
$this->addClass('bookcart', new bookcart);

$solrRecord = $this->solr->getRecord('biblio', $rec_id);

switch ($format) {
	case 'html' : 
			$Tmap = [];
			
			if (!empty($solrRecord->id)) {
				if (!empty($solrRecord->fullrecord) && ($solrRecord->record_format == 'mrk')) {
					$marcJson = $this->convert->mrk2json($solrRecord->fullrecord);
					$this->addClass('record', new bibliographicRecord($solrRecord, $marcJson));
					} else 
					$this->addClass('record', new bibliographicRecord($solrRecord));
				
				$this->setTitle($this->record->getTitle());
				$this->head->meta = $this->record->getMetaZotero();
				$this->head->meta .= $this->record->getMetaAlternate();
				
				
				echo $this->render('head.php');
				echo $this->render('core/header.php');
				echo '<div class="main">';
				echo $this->render('record/core.php', ['record' => $this->record ]);
					# echo $this->helper->pre($this->record->elbRecord);
				echo '</div>';
				echo $this->render('core/footer.php');
				
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

			require_once('functions/class.exporter.php');
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
			
			// https://testlibri.ucl.cas.cz/_tools/marc21/json2mrc.php
			
			$post = ['id'=>$rec_id, 'file'=> base64_encode(json_encode($marcJson, JSON_INVALID_UTF8_SUBSTITUTE))];
			$ch = curl_init("https://testlibri.ucl.cas.cz/_tools/marc21/json2mrc.php"); 
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			$response = curl_exec($ch);
			curl_close($ch);
			
			/*
			echo $this->render('head.php');
			echo $this->render('core/header.php');
			echo '<div class="container">';
			echo '<div class="main">';
			echo $this->helper->pre($post);
			echo '<textarea style="width:100%; height: 400px">';
			var_dump($response);
			echo "</textarea>";
			echo '</div>';
			echo '</div>';
			$this->render('core/footer.php');	
			*/
			$len=strlen($response);
			
			if ($len>0) {
				header("Content-type: application/mrc");
				header("Content-Length: $len");
				header("Content-Disposition: inline; filename=$fn");
				print $response;
				} 

			break;
			
	case 'marcxml' : 
			$record = $this->solr->getRecord('biblio', $rec_id);
			#$marcJson = $this->buffer->getJsonRecord($record->id, $record->fullrecord);
			$marcJson = $this->convert->mrk2json($record->fullrecord);

			require_once('functions/class.exporter.php');
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
				} if (isset($this->GET['elb'])) {
				$plik = $record->relations;	
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
	
	case 'btx' :
			$record = $this->solr->getRecord('biblio', $rec_id);
			$marcJson = $this->convert->mrk2json($record->fullrecord);
			$this->addClass('record', new bibliographicRecord($solrRecord, $marcJson));
				
			$btx = $this->render('converter/toBibTeX.php', ['record' => $this->record ]);
			$len=strlen($btx);
			header("Content-type: application/$format");
			header("Content-Length: $len");
			header("Content-Disposition: inline; filename=$fn");
			print $btx;
			
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
