<?php 

### do poprawy sorotowanie (active first, potem według liczby wystąpień)

$list = $this->buffer->getBottomList('persons');
$activePersons = [];

$formattedFacets = $this->getConfigParam('facets', 'formattedFacets');
if (!empty($this->buffer->usedFacets))
	foreach ($this->buffer->usedFacets as $usedFacet=>$facetValues)
		if (array_key_exists($usedFacet,$formattedFacets) && $formattedFacets[$usedFacet] == 'formatPerson') 
			foreach ($facetValues as $activePerson) {
				$row = str_replace('"','',$activePerson['value']);
				$key = $this->buffer->shortHash($row);
				$activePersons['!'.$key] = $row;
				
				if (!empty($list[$key])) {
					$list['!'.$key] = $row;
					unset($list[$key]);
					}
				}


#echo "<pre>".print_R($formattedFacets,1)."</pre>";
#echo "<pre>".print_R($this->buffer->usedFacets,1)."</pre>";
#echo "<pre>".print_R($activePersons,1)."</pre>";
#echo "<pre>".print_R($list,1)."</pre>";


if (count($list)>0) {
	ksort($list);
	echo '<h4>'.$this->transEsc('Related persons').' <span class="badge">'.count($list).'</span></h4>'; 
	$idDescBox = uniqid();
	$minSize = 344;
	
	echo '<div class="collapseBox" id="'. $idDescBox .'">
			<input type="hidden" id="'.$idDescBox.'_maxSize">
			<input type="hidden" id="'.$idDescBox.'_minSize" value="'. $minSize .'">
			<div class="collapseBox-body">';

	foreach ($list as $key=>$personLine) {
		$rec = explode('|', $personLine);
		$AP = new stdClass;
		$AP->name = $rec[0];
		$AP->year_born = $rec[1];
		$AP->year_death = $rec[2];
		$AP->viaf_id = $rec[3];
		$AP->wikiq = trim($rec[4]);
		$AP->date = trim($rec[5]);
		if (!empty($AP->wikiq)) {
			$AP->field = "personBoxQ".$AP->wikiq;
			$base = base64_encode(json_encode($AP));
			$this->addJS("page.post('{$AP->field}', 'wiki/person/box/{$AP->wikiq}?', ".json_encode($AP).");");
			} else {
			$AP->field = "personBoxB".$this->buffer->shortHash($AP->name.$AP->date);	
			$this->addJS("$('.".$AP->field."').html('');");
			}

		if (array_key_exists($key, $activePersons))
			$activeBox = 'active';
			else
			$activeBox = '';	
		
		
		echo '<div class="person-info '.$activeBox.'" id="'.$AP->field.'">';
		echo $this->render('persons/results/list.php',['activePerson'=>$AP]);
		echo '</div>';
		}
	echo '</div>
	<div class="collapseBox-bottom text-right">
		<button class="toolbar-btn show-btn" OnClick="colbox.Show(\''.$idDescBox.'\')"><i class="ph-caret-down-bold"></i> '.$this->transEsc('More').' ...</button>
		<button class="toolbar-btn hide-btn" OnClick="colbox.Hide(\''.$idDescBox.'\')"><i class="ph-caret-up-bold"></i> '.$this->transEsc('Less').' ...</button>
	</div>
</div>';
	$this->addJS("colbox.Check('$idDescBox')"); 
	}


?>