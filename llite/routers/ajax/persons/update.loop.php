<div class="alert alert-info">

<?php 
if (empty($this)) die;
print_r($this->routeParam);

$this->addClass('buffer', 	new marcBuffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('solr', 	new solr($this->config)); 


if ($this->routeParam[0]=='start') {
	$this->getConfig('person-card');
	$presonsFields = $this->getIniParam('person-card', 'biblioPersonFields');
	
	print_r($presonsFields);
	$res = $this->sql->query("TRUNCATE TABLE libri_persons_full;");
	$res = $this->sql->query("DELETE FROM libri_persons_full;");
	
	
	}

$res = $this->sql->query("SELECT * FROM libri_biblio_persons WHERE reccount IS NULL ORDER BY lastupdate DESC LIMIT 1;");
if (!empty($res->num_rows) && ($res->num_rows>0)) {
	echo "<div class='panel panel-default'>";
	$res2 = $this->sql->query("SELECT count(*) as number FROM libri_biblio_persons WHERE reccount IS NULL;");
	if (!empty($res2->num_rows) && ($res2->num_rows>0)) {
		$row2 = mysqli_fetch_assoc($res2); 
		echo "<div class='panel-heading'>Persons to do: <b>$row2[number]</b></div>";
		}
	echo "<div class='panel-body'>";	
	$row = mysqli_fetch_assoc($res); 
	
	echo '
			<li class="long-list">
				<a class="name" href="/lite/pl/person/'.$this->urlName($row['name']).'/viaf_id'.$row['viafid'].'">'.
					htmlspecialchars($row['name']).'</a><span class="date">'.$row['dates'].'</span><span class="id">'.$row['viafid'].'</span>
			</li>';
	
	
	$viafId = $row['viafid'];
	
	$xmlf = $this->buffer->loadFromViaf($viafId);
	$xml = $this->buffer->xml2array($xmlf);
	
	$tmp = explode('(WKP)',$xmlf);
	if (count($tmp)>1) {
		$tmp = explode('<',$tmp[1]);
		$wikiId = trim(current($tmp));
		echo "wikiId: $wikiId<br>";
		
		$wikifile = $this->buffer->loadFromWikidata($wikiId); 
		$photo = $this->buffer->loadMediaFromWikidata($wikiId);
		$image = end($photo);

		$json = json_decode($wikifile);

		$rec = $json->entities->$wikiId;
		$rec->wikiId = $wikiId;
		$rec->viafId = $viafId;


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

		foreach ($this->lang['available'] as $id_lang=>$langName) {
			
			$person->name->$id_lang = getValue('labels->'.$id_lang.'->value', $rec);
			$person->desc->$id_lang = getValue('descriptions->'.$id_lang.'->value', $rec);
			}

		echo "<div class=row>";
		echo "<div class=col-sm-8><pre style='border:0px; background-color:transparent'>".print_R($person,1)."</pre></div>";	
		if (!empty($person->image->fname))
			echo "<div class=col-sm-4><img src='{$person->image->fname}' class='img img-responsive'></div>";	
		echo "</div>";
		echo "</div>";

		$this->buffer->savePerson($person->ids->viafId, $person);
			
		
		} else {
		echo "<br><br>No Wiki Id :-(";
		$wikiId = 0;
		}

	$this->sql->query($Q="UPDATE libri_biblio_persons SET reccount=1, wikiid='$wikiId', lastupdate=now() WHERE id_person='$row[id_person]';");
	echo "</div>";
	#echo $Q;
	$this->JS[]="page.ajax('updated_person_box','persons/update.loop/start');";
	} else 
	echo "<Br/><br/>All checked!<Br/><br/>";


$res = $this->sql->query("SELECT count(*) as ile FROM  libri_persons ;");
if (!empty($res->num_rows) && ($res->num_rows>0)) {
	$row = mysqli_fetch_assoc($res);
	$this->JS[] = "$('#countWithIB').html('$row[ile]');";

	}

?>
</div>