<?php
if (empty($this)) die;
require_once('functions/class.maps.php');
require_once('functions/class.wikidata.libri.php');
require_once('functions/class.wikidata.php');
#require_once('functions/class.wikiDataCores.php');
require_once('functions/class.viafSearcher.php');

$wikiq = $this->routeParam[0];
$recTypeExpected = $this->routeParam[1] ?? null;
if (!empty($this->routeParam[2]) && ($this->routeParam[2]=='reload'))
	$refreshRecord = true;
	else 
	$refreshRecord = false;	

$wikiIdInt = substr($wikiq,1);
$this->clearGET();

$this->addClass('buffer', 	new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->addClass('solr', 	new solr($this));  
$this->addClass('wiki', 	new wikidata($this)); 
$this->addClass('wikiRec', 	new wikidata($this)); 
$this->addClass('viafSearcher', new viafSearcher($this)); 
#$this->addClass('wikiDataCores',	new wikiDataCores($this));

$this->wiki->loadRecord($wikiq, $refreshRecord);
$recType = $this->wiki->recType();
$recCore = $recType.'s';
$coreRecord = $this->solr->getWikiRecord($recCore, $wikiq);
$this->addClass('coreRecord', new wikiLibri($this->userLang, $coreRecord));
#echo $this->helper->pre($this->wiki->labels);

$this->setTitle($this->wiki->get('labels').' | '.$this->transEsc($recType));

# echo '<h1>record</h1>'.$this->helper->pre($this->wiki->record);
# echo '<h1>solrRecord</h1>'.$this->helper->pre($this->wiki->solrRecord);
$dataQualityStr = $this->render('wiki/tab-dataQuality.php');


$query = [];
$query['q'] 			= ['field' => 'q',				'value' => '*:*' ];
$query['facet'] 		= ['field' => 'facet',			'value' => 'true'];
$query['facet.field'] 	= ['field' => 'facet.field',	'value' => 'with_roles_wiki'];		
$query['facet.limit']	= ['field' => 'facet.limit',	'value' => 9999 ];
$query['facet.prefix']	= ['field' => 'facet.prefix', 	'value' => $wikiq.'|' ];
$this->solr->getQuery('biblio', $query); 
$results = $this->solr->resultsList();
$facets = $this->solr->facetsList();		
# $bibliographicalStatistics  = $this->render('wiki/tab-bibliographicalStatistics .php', ['recCore'=>$recCore, 'wikiq'=>$wikiq]);
# $comparisonOfRolesInBibliography = $this->render('wiki/tab-comparisonOfRolesInBibliography.php', ['recCore'=>$recCore, 'wikiq'=>$wikiq]);
		


$tabsToShow = (object)[
				'map' => true,
				'bibliographicalStatistics' => true,
				'comparisonOfRolesInBibliography' => true,
				'related' => true,
				'dataQuality' => true,
				];
$params = [ 
			'recType' => $recType,
			'tabsToShow' => $tabsToShow,
			'bibliographicalStatistics' => '', //$bibliographicalStatistics, 
			'comparisonOfRolesInBibliography' => '', //$comparisonOfRolesInBibliography, 
			'dataQualityStr' => $dataQualityStr
			];

$showTabsPanel = true;
switch ($recType) {
	case 'person' :
			$rec_id = $this->wiki->getViafId();
			$renderer = 'wiki/fullcard-person.php';
			break;
	
	case 'corporate' :
			$renderer = 'wiki/fullcard-corporate.php';
			break;
			
	case 'maybePlace' : 
	case 'place' : 
			require_once('functions/class.places.php');
			$renderer = 'wiki/fullcard-place.php';
			break;
			
	case 'magazine' : 
			$renderer = 'wiki/fullcard-magazine.php';
			break;
			
	case 'event' : 
			$renderer = 'wiki/fullcard-event.php';
			break;
			
	default :
			$showTabsPanel = false;
			$renderer = 'wiki/fullcard-subject.php';
			$params = [ 
					'recType' => $recType,
					'tabsToShow' => $tabsToShow,
					#'stat'=>$stat, 
					#'names'=>$Tchecked, 
					#'allNames'=>$allNames, 
					#'res'=>$res, 
					#'bibliographicalStatistics' => $bibliographicalStatistics, 
					#'comparisonOfRolesInBibliography' => $comparisonOfRolesInBibliography, 
					'dataQualityStr' => $dataQualityStr
					];
			break;
	}

$wikiLink = $this->wiki->getSiteLink();
$t = $this->psql->querySelect($Q = "SELECT * FROM wikipedia_descriptions WHERE wikilink = {$this->psql->string($wikiLink)};");
if (is_Array($t)) {
	$row = current($t);
	$this->wiki->record->wikipediaDescription = $row['description'].'<div class="text-right"><a href="'. $row['wikilink'].'">'. $this->transEsc('More information on Wikipedia').'</a></div>';
				;
	if (strlen($this->wiki->record->wikipediaDescription)>500) 
		$this->addJS("$('#wikimediaDescription').addClass('multi-column');");
	} else {
	$postparams = (object)[
			'wikipedia' => $this->wiki->getSiteLink(),
			'API' => $this->wiki->getWikimediaAPILink(),
			'wikiq' => $wikiq
			];
	$this->addJS ('page.post("wikimediaDescription", "wiki/wikimedia.description", '.json_encode($postparams).')');
	}



echo $this->render('head.php');
echo $this->render('core/header.php');
echo "<div class='main'>";
echo '<div class="graybox">';
echo '<div class="infopage">';
echo '<div id="fixedHeaders"></div>';
# echo "<h1>".$recType.'</h1>';
echo $this->render($renderer, ['recType' => $recType]); 
echo $this->render('wiki/link.shared.php', ['facets'=>$facets]); 
if ($showTabsPanel) {
	if ($this->isMobile()) {
		echo $this->render('wiki/tabPanels.mobile.php', $params); 
		} else {
		echo '<div class="" style="padding-bottom:10px;">';
		$userMode = $this->getUserParam('wiki.tab.mode') ?? 'basic';
	
		if (!empty($this->routeParam[1]) && (($this->routeParam[1] == 'basic') or ($this->routeParam[1] == 'more'))) {
			$userMode = $this->routeParam[1];
			}
		$this->saveUserParam('wiki.tab.mode', $userMode); 
		
		if ($userMode == 'more') {
			echo '
				<div style="width:100%; margin-top:20px;">
					<div class="pilltab" title="'.$this->transEsc("Switch tab panel mode").'">
					<li><a href="'.$this->buildUrl('wiki/record/'.$this->wiki->getID().'/basic').'#ums" class="btn btn-link"><i class="ph ph-bookmark-simple"></i> '.$this->TransEsc('Basic').'</a></li>
					<li class="active"><a href="'.$this->buildUrl('wiki/record/'.$this->wiki->getID().'/more').'#ums" class="btn btn-link"><i class="ph ph-bookmarks"></i> '.$this->TransEsc('Advanced').'</a></li>
					</div>
				</div>
				';
			echo $this->render('wiki/tabPanels.php', $params); 
			} else {
			echo '
				<div style="width:100%; margin-top:20px;">
					<div class="pilltab" title="'.$this->transEsc("Switch tab panel mode").'">
					<li class="active"><a href="'.$this->buildUrl('wiki/record/'.$this->wiki->getID().'/basic').'#ums" class="btn btn-link"><i class="ph ph-bookmark-simple"></i> '.$this->TransEsc('Basic').'</a></li>
					<li><a href="'.$this->buildUrl('wiki/record/'.$this->wiki->getID().'/more').'#ums" class="btn btn-link"><i class="ph ph-bookmarks"></i> '.$this->TransEsc('Advanced').'</a></li>
					</div>
				</div>
				';
			echo $this->render('wiki/tabPanels.simple.php', $params); 	
			}
		echo '</div>';
			
		}
	} 
echo "</div>";
echo "</div>";
echo "</div>";
echo $this->render('helpers/report.error.php'); 
echo $this->render('helpers/wiki.licence.php');

echo '
	<div class="alert-cloud">
	<div class="row">
		<div class="col-sm-3">
		'.$this->helper->loader2().'
		</div>
		<div class="col-sm-9 text-center">
			<br/>
			Some data is still collected to generate the page.<br/>
			<b>Please wait patiently.</b> 
		</div>	
		</div>
	</div>
	';

echo $this->render('core/footer.php');




$this->addJS('
		let alertTimeout;
		function updateAjaxCount() {
			if ($.active > 0) {
				$("#mapRelationsAjaxArea").css("opacity", "0.2");
				if (!alertTimeout) {
					alertTimeout = setTimeout(() => {
						$(".alert-cloud").css("bottom", "150px");
						}, 1200);
					}
				} else {
				$("#mapRelationsAjaxArea").css("opacity", "1");	
				clearTimeout(alertTimeout);
				alertTimeout = null;
				$(".alert-cloud").css("bottom", "-250px");	
				}
			}
		setInterval(updateAjaxCount, 500);
		updateAjaxCount();
	');

?>


