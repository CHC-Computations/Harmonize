<?php
if (empty($this)) die();
require_once('functions/klasa.wikidata.php');
require_once('functions/klasa.buffer.php');
$this->addClass('buffer', 	new marcBuffer()); 

$elp = 0;

if (!empty($this->POST['list']) && is_array($personsList = $this->POST['list'])) {
	echo '<h4>'.$this->transEsc('Related persons').' <span class="badge">'.count($personsList).'</span></h4>';
	$idDescBox = uniqid();
	$minSize = 344;
	echo '<div class="collapseBox" id="'. $idDescBox .'">
			<input type="hidden" id="'.$idDescBox.'_maxSize">
			<input type="hidden" id="'.$idDescBox.'_minSize" value="'. $minSize .'">
			<div class="collapseBox-body">';
	
	
	foreach ($personsList as $key=>$personLine) {
		$rec = explode('|', $personLine);
		$AP = new stdClass;
		$AP->name = $rec[0];
		$AP->year_born = $rec[1];
		$AP->year_death = $rec[2];
		$AP->viaf_id = $rec[3];
		$AP->wikiq = trim($rec[4]);
		$AP->date = trim($rec[5]);
		if (!empty($AP->wikiq)) {
			$wikiIDint = $AP->wikiq;
			// $t = $this->psql->querySelect("SELECT * FROM persons WHERE wikiq={$this->psql->isNull($wikiIDint)};");
			// if (is_array($t))
				// $activePerson = (object)current($t);
			$wikiId = 'Q'.$AP->wikiq;
			$wiki = new wikidata($wikiId);
			$activePerson = $wiki->getActivePersonValues();
			$activePerson->wiki = $wiki;
			
			if (substr($key,0,1) == '!')
				$activeBox = 'active';
				else
				$activeBox = '';	
			$AP->field = "personBoxQ".$AP->wikiq;
			
			
			$activePerson->wiki = new wikidata($wikiId); 
			$activePerson->wiki->setUserLang($this->user->lang['userLang']);

			$photo = $this->buffer->loadWikiMediaUrl($activePerson->wiki->getStrVal('P18'));

			echo '<div class="person-info '.$activeBox.'" id="'.$AP->field.'">';
			echo $this->render('persons/results/list-wiki.php',['activePerson'=>$activePerson, 'photo'=>$photo]);
			echo '</div>';
			
			$this->addJS("$('.personBox{$AP->wikiq}').html($('#{$AP->field}').html());"); 
			
			} else {
			if ($elp == 0) echo '<br/>'.$this->transEsc('Also').':<br/>';	
			$elp++;
			$TolnyNames[] = '<a href="'.$this->buildUrl('search/results/').'lookfor='.urlencode($AP->name).'&type=AllFields" title="'.$this->transEsc('look for').'">'.$AP->name.'</a> ';
			}
		}
	if (!empty($TolnyNames))
		echo implode('<br/>',$TolnyNames);
	echo '</div>
		<div class="collapseBox-bottom text-right">
			<button class="toolbar-btn show-btn" OnClick="colbox.Show(\''.$idDescBox.'\')"><i class="ph-caret-down-bold"></i> '.$this->transEsc('More').' ...</button>
			<button class="toolbar-btn hide-btn" OnClick="colbox.Hide(\''.$idDescBox.'\')"><i class="ph-caret-up-bold"></i> '.$this->transEsc('Less').' ...</button>
		</div>
	</div>';
	$this->addJS("colbox.Check('$idDescBox')"); 
	#echo $this->helper->pre($personsList);
	}




?>