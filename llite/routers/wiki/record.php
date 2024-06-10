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
	$refreshRecord = true;	

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

switch ($recType) {
	case 'person' :
			$rec_id = $this->wiki->getViafId();
			$renderer = 'wiki/fullcard-person.php';
			break;
	
	case 'corporate' :
			$renderer = 'wiki/fullcard-corporate.php';
			break;
			
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
			
			$renderer = 'wiki/fullcard-subject.php';
			$params = [ 
					'recType' => $recType,
					'tabsToShow' => $tabsToShow,
					'stat'=>$stat, 'names'=>$Tchecked, 'allNames'=>$allNames, 'res'=>$res, 
					'bibliographicalStatistics' => $bibliographicalStatistics, 
					'comparisonOfRolesInBibliography' => $comparisonOfRolesInBibliography, 
					'dataQualityStr' => $dataQualityStr
					];
			break;
	}


echo $this->render('head.php');
echo $this->render('core/header.php');
echo "<div class='main'>";
echo '<div class="graybox">';
echo '<div class="infopage">';
# echo "<h1>".$recType.'</h1>';
echo $this->render($renderer, ['recType' => $recType]); 
echo $this->render('wiki/tabPanels.php', $params); 
echo "</div>";
echo "</div>";
echo "</div>";
echo $this->render('core/footer.php');


?>


