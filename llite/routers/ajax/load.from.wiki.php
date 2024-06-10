<?php
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.buffer.php');

$this->addClass('buffer', 	new buffer()); 
$this->addClass('helper', 	new helper()); 

$wikiId = $this->routeParam[0];
$viafId = $this->routeParam[1];

$wikifile = $this->buffer->loadFromWikidata($wikiId); 
$photo = $this->buffer->loadMediaFromWikidata($wikiId);
$image = end($photo);


$json = json_decode($wikifile);

$rec = $json->entities->$wikiId;
$rec->wikiId = $wikiId;
$rec->viafId = $viafId;

/*
echo $this->helper->panelCollapse(
		uniqid(), 
		"record from wiki", 
		"<pre style='background-color:#fff; border:0px;' id='json-viewer'>".print_r($rec,1).'</pre>', 
		"
		<a target=_blank href='https://www.wikidata.org/wiki/Special:EntityData/$wikiId.json'>soure: https://www.wikidata.org/wiki/Special:EntityData/$wikiId.json</a><br/>
		<a target=_blank href='https://www.wikidata.org/wiki/$wikiId'>soure: https://www.wikidata.org/wiki/$wikiId</a>
		", 
		false
		);
echo "<script>
	var input = ".json_encode($rec).";
	$('#json-viewer').jsonViewer(input, {collapsed: true, rootCollapsable: false});
	</script>";
# echo "PH:<pre>".print_r($image,1)."</pre>";			
*/
			
$mapping = array(
	'ViafId' 				=> ['field' => 'viafId', 					'marcField' => '001'],
	'WikiId'				=> ['field' => 'wikiId', 					'marcField' => ''],
	'Nazwisko, imię PL' 	=> ['field' => 'labels->pl->value', 		'marcField' => '400 |2 <b>PL</b> |1'],
	'Nazwisko, imię EN' 	=> ['field' => 'labels->en->value', 		'marcField' => '400 |2 <b>EN</b> |1'],
	'Nazwisko, imię CZ' 	=> ['field' => 'labels->cz->value', 		'marcField' => '400 |2 <b>CZ</b> |1'],
	
	'Opis PL' 				=> ['field' => 'descriptions->pl->value', 	'marcField' => '678 |2 <b>PL</b> |0'],
	'Opis EN' 				=> ['field' => 'descriptions->en->value', 	'marcField' => '678 |2 <b>EN</b> |0'],
	'Opis CZ'				=> ['field' => 'descriptions->cz->value', 	'marcField' => '678 |2 <b>CZ</b> |0'],
	
	'Miejsce urodzenia' 	=> ['field' => 'claims->xXx', 				'marcField' => ''],
	'Data urodzenia' 		=> ['field' => 'claims->P569->0->mainsnak->datavalue->value->time', 'marcField' => ''], //
	'Miejsce śmierci' 		=> ['field' => 'claims->xXx', 				'marcField' => ''],
	'Data śmierci' 			=> ['field' => 'claims->P570->0->mainsnak->datavalue->value->time', 'marcField' => ''], //
	
	);			
	
	
	
$person = new stdclass;
$person->name = new stdclass;
$this->buffer->setSql($this->sql);
$person->id_source_doc = $this->buffer->getSourceId('page', 'wikidata');

$person->ids = new stdclass;
$person->ids->viafId = getValue('viafId', $rec);
$person->ids->wikiId = getValue('wikiId', $rec);

$person->desc = new stdclass;
$person->image = (object)$image;
$person->gender = '';

$person->dates = new stdclass;
$person->dates->birth = getValue('claims->P569->0->mainsnak->datavalue->value->time', $rec);
$person->dates->death = getValue('claims->P570->0->mainsnak->datavalue->value->time', $rec);
$person->places = new stdclass;
$person->places->birth_id = getValue('claims->P19->0->mainsnak->datavalue->value->id', $rec);
$person->places->death_id = getValue('claims->P20->0->mainsnak->datavalue->value->id', $rec);

foreach ($this->lang['available'] as $id_lang=>$langName) {
	
	$person->name->$id_lang = getValue('labels->'.$id_lang.'->value', $rec);
	$person->desc->$id_lang = getValue('descriptions->'.$id_lang.'->value', $rec);
	}

echo "<div class=row>";
echo "<div class=col-sm-8><pre>".print_R($person,1)."</pre></div>";	
if (!empty($person->image->fname))
	echo "<div class=col-sm-4><img src='{$person->image->fname}' class='img img-responsive'></div>";	
echo "</div>";

$this->buffer->savePerson($person->ids->viafId, $person);
			
echo '<table class="table table-hover">';			
foreach ($mapping as $k=>$v) {
	
	$value = getValue($v['field'], $rec);
	echo '
		<tr>
			<td class="text-right">'.$k.':</td>
			<td class="text-right">'.$v['marcField'].':</td>
			<td><b>'.$value.'</b></td>
		</tr>
		';
	}			
echo '</table>';




function getValue($x, $rec) {
	$sep = '->';
	$t = explode($sep, $x);
	
	
	#echo "$x <pre>".print_r($rec,1)."</pre>";
	
	if (count($t)>1) {
		$nt = $t[0];
		if (!empty($rec->$nt) && (is_object($rec->$nt)) )  {
				unset($t[0]);
				return getValue(implode($sep,$t),$rec->$nt);
				} 
		if (!empty($rec->$nt) && (is_Array($rec->$nt)) )  {
				unset($t[0]);
				unset($t[1]);
				return getValue(implode($sep,$t),$rec->$nt[0]);
				} 
		
		return null;
		// return $x;
		
		} else {
		if (!empty($rec->$x))
			return $rec->$x;
			else 
			return null;
		}
	}

// 
?>
<script> location.reload() </script>;	