<?php 
if (empty($this)) die;
$format = $this->routeParam[0];


$this->addClass('buffer', new buffer()); 
$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this)); 

$this->buffer->setSql($this->sql);


switch ($format) {
	case 'solr' :
			############################################### List from solr facet 
			
			$facets = $this->getConfig('facets');
			$currFacet = "author_facet_s";
			$facetName = $facets['facetList'][$currFacet];

			$queryoptions[]=[ 
							'field' => 'facet.sort',
							'value' => 'index'
							];
			$queryoptions[]=[ 
							'field' => 'json.facet', //json.facet={x:"unique(author_facet_s)"}
							'value' => '{x:"unique('.$currFacet.')"}'
							];
			$queryoptions[]=[
							'field' => 'facet.limit',
							'value' => '500'
							];
							
			$results = $this->solr->getFacets('biblio', [$currFacet], $queryoptions);


			echo $this->transEsc('All persons in LiBRI').' <span class="badge">'.$this->solr->getFacetsCount().'</span>';

			foreach ($results[$currFacet] as $name=>$count) {
				echo "<li class='list-group-item'>$name <span class='badge'>$count</span></li>";
				}

		
		break;

	case 'viafid' : 
		##################################################### list from sql: libri_biblio_persons (the logiest list)
			
			$first = $ll = '';
			$Tfirst = [];
			$text = '  empty list  ';
			$res = $this->sql->query("SELECT DISTINCT fl FROM libri_biblio_persons ORDER BY fl;");
			if (!empty($res->num_rows) && ($res->num_rows>0)) 
				while ($row = mysqli_fetch_assoc($res)) 
					echo "<a class='btn btn-success' href=\"#char_$row[fl]\">$row[fl]</a> ";
			echo " <button class=\"btn btn-warning\" OnClick=\"page.ajax('updated_person_box','persons/update.loop/start');\">update persons info-boxes</button> ";
			echo "<div id='updated_person_box'></div>";
			
			$res = $this->sql->query("SELECT * FROM libri_biblio_persons ORDER BY fl, name, dates LIMIT 1000;");
			if (!empty($res->num_rows) && ($res->num_rows>0)) {
				echo '<div class="scroll-box" id="fullListOfPersons">';
				$lp = 0;
				while ($row = mysqli_fetch_assoc($res)) {
					if ($ll <> $row['fl']) 
						echo "<h5 class='separator-line' id='char_$row[fl]' name='char_$row[fl]'>$row[fl]</h5>";
					$lp++;
					echo '
							<li class="long-list">
								<a class="name" href="/lite/pl/persons/record/'.$this->urlName2($row['name'],'_').'/viaf_id'.$row['viafid'].'">'.
									htmlspecialchars($row['name']).'</a><span class="date">'.$row['dates'].'</span><span class="id">'.$row['viafid'].'</span>
							</li>';
					$ll = $row['fl'];
					}
				
				echo "</div>";
				} 
		break;
	
	default: echo $this->transEsc('unknown list').': '.$format; break;
	}
	

function basicChar($char) {
	#$char = mb_convert_encoding( $char, "SJIS");
	$char = strtoupper($char);
	switch ($char) {
		case '':
		case 'a': return 'A'; break;
		
		case 'c': return 'C'; break;
		
		case 'Đ':
		case 'd': return 'D'; break;
		
		case 'e': return 'E'; break;
		
		case 'Ł':
		case 'L': return 'L'; break;
		
		case 'Ø':
		case 'O': return 'O'; break;
		
		case 'ʻ': 
		case 'ʾ': 
		case '´': 
		case 'ʿ': return 'Spec'; break;
		
		default: return $char;
		}
	}




?>