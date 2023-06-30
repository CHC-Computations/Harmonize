<?php
if (empty($this)) die;
	$lista_echo = '';
	$file_id='http://testlibri.ucl.cas.cz/vufind/';
	$imp = new importer($this->pgs);
	
	$step = 256;
	
	$file = file_get_contents($fn=$file_id.'oop/export.all.php?start='.$this->routeParam[0].'&step='.$this->routeParam[1]);
	
	$res = json_decode($file);
	
	$num_rec = $res->response->numFound;
	
	$done = $this->routeParam[0]+$this->routeParam[1];
	$proc = number_format(($done/$num_rec)*100,1,'.',' ');
	$lp = $this->routeParam[0];
	
	foreach ($res->response->docs as $bib) {
		$qf = $qs = array();
		$lp++;
		# echo "<pre>".print_r($bib,1)."</pre>";
		
		$id_biblio = $bib->id;
		
		/*
		$qf[] = "DELETE FROM biblio_facets WHERE id_biblio='{$id_biblio}';";
		$qf[] = "DELETE FROM biblio_docs_c WHERE id_biblio='{$id_biblio}';";
		$qf[] = "DELETE FROM biblio_eras_c WHERE id_biblio='{$id_biblio}';";
		$qf[] = "DELETE FROM biblio_formats_c WHERE id_biblio='{$id_biblio}';";
		$qf[] = "DELETE FROM biblio_forms_c WHERE id_biblio='{$id_biblio}';";
		$qf[] = "DELETE FROM biblio_lang_c WHERE id_biblio='{$id_biblio}';";
		$qf[] = "DELETE FROM biblio_persons_c WHERE id_biblio='{$id_biblio}';";
		$qf[] = "DELETE FROM biblio_regions_c WHERE id_biblio='{$id_biblio}';";
		*/
		$qf[] = "DELETE FROM biblio WHERE id='{$id_biblio}';";
		$qf[] = "COMMIT;";
		
		
		$LEADER = '';
		$full_record = $bib->fullrecord;
		$rec_type = $bib->recordtype;
		
		/*
		######################## source db
		if (!empty($bib->info_resource_str_mv[0])) {
			$source_db = $bib->info_resource_str_mv[0];

			$id_source = $imp->getId('biblio_source_db', ['value' => $source_db]);
			if ($id_source == null) {
				$this->pgs->query("INSERT INTO biblio_source_db (value) VALUES ('{$imp->clearVal($source_db)}'); COMMIT;");  
				$id_source = $imp->getId('biblio_source_db', ['value' => $source_db['a']]);
				}
				
			} else 
			$id_source = null;
		
		######################## authors 
		if (!empty($bib->author)) {
			foreach ($bib->author as $k=>$author) {
				$tmp = explode(' ', $author); 
				# echo "$k. author tmp <pre>".print_r($tmp,1)."</pre>";
				
				$count = count($tmp)-2;
				if ($count>0) { // maybe there is id or data 
					$lastWord = array_pop($tmp);
					$almostLastWord = $tmp[$count];
					
					if (preg_match_all( "/[0-9]/", $lastWord)>5) { // ostatnie słowo to id ?
						$Tauthor['id'] = $lastWord;
						unset($tmp[$count+1]); // zmiejszamy liczbę słów
						$lastWord = array_pop($tmp); // nowe ostatnie słowo
						} else 
						$Tauthor['id'] = '';
					
					if ((preg_match_all( "/[0-9]/", $lastWord)>3)and (stristr($lastWord, '-'))){ // ostatnie słowo jest datą?
						$Tauthor['date'] = $lastWord;
						unset($tmp[count($tmp)]); // zmiejszamy liczbę słów
						} else 
						$Tauthor['date'] = '';
						
					
					$Tauthor['name'] = trim(chop(implode(' ',$tmp)));	
					} else {
					$Tauthor['name'] = $author;	
					}
		
				# echo "$k. Tauthor <pre>".print_r($Tauthor,1)."</pre>";
				
				$id_author = $imp->getIdA('biblio_persons', [
							'name' => $Tauthor['name'],
							'date' => $Tauthor['date'],
							'id_out' => $Tauthor['id'],
							]);
				$role = $bib->author_role[$k];
				if ($role=='')
					$role = 'aut';
							
				$ap = $imp->tryAppend('biblio_persons_c', [
							'id_biblio' => $id_biblio,
							'id_person' => $id_author,
							'role' => $role
							]);
				
				if (($k==0)and($role=='aut')) 
					$id_m_author = $id_author;
					else 
					$id_m_author = null;
				}
			}
		
		
		# $qs[]="INSERT INTO biblio_formats_c (id_biblio, code) VALUES ('{$id_biblio}', '{$format_code}');"; 	
		*/	
		
		$id_m_author = null;
		$id_source = null;
		######################## title 
		$title = $imp->removeLastSlash( $bib->title );
		######################## sub title 
		if (!empty($bib->title_sub))
			$subtitle = $imp->removeLastSlash( $bib->title_sub );
			else
			$subtitle = null;
		
		######################## publish_year
		if (!empty($bib->year_str_mv[0]))
			$publish_year = $bib->year_str_mv[0];
			else
			$publish_year = null;
		######################## lang_code
		if (!empty($bib->language))
			foreach ($bib->language as $k=>$id_lang) {
				#$qs[]="INSERT INTO biblio_lang_c (id_biblio, id_lang) VALUES ('{$id_biblio}', '{$id_lang}');"; 	
				if ($k==0)
					$lang_code = $id_lang;
				}
		
		
		$eight = null;
		$rec_id = $lp;
		$qf[] = "INSERT INTO biblio (id, data_c, data_m, status, leader, source_db_id, author_id, title, subtitle, publish_year, full_record, rec_type, eight, lang_code, file_id, rec_id) 
						VALUES ('{$id_biblio}', now(), now(), 1, '{$LEADER}', {$imp->isNull($id_source)}, {$imp->isNull($id_m_author)}, {$imp->isNull($title)}, {$imp->isNull($subtitle)}, '{$publish_year}', '".pg_escape_string($full_record)."', '{$rec_type}', '$eight', '$lang_code', null, '$rec_id' );";
				
		
		$q = array_merge($qf, $qs);
		$res = $this->pgs->query(implode("\n",$q));  // SQL
		$lista_echo .= "$lp. $id_biblio. $LEADER ... <br/>";
		
		}
	
	
	
?>
			Pobrałem rekordów: <b><?= $done ?></b> z <?= $num_rec ?> (<?= $proc ?>%)<br/><br/>
			
			<?= $this->Procent($done, $num_rec) ?>

			params: 
			<pre><?= print_r($this->routeParam, 1) ?> </pre>
			Lista: <br/><br/>
			<?= $lista_echo ?>
			


<?php 

	if ($done<$num_rec) {
		echo "importer.All('$done','$step');";
		echo "<script>importer.All('$done','$step');document.title='$lp. ($proc%) done.'</script>";
	} else {
		echo "<script>document.title='Import pass 1 complete!'</script>";
	}



