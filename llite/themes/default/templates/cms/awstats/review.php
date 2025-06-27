<?php 
#echo $this->helper->pre($statFiles);

$filesLinksPrint = '';
$graphPrint = '';
$detailPrint = '';

foreach ($statFiles as $key=>$size) {
	$linkKey = str_replace('.', '/', $key);
	if ((count($this->routeParam)>1) && ($key == $this->routeParam[0].'.'.$this->routeParam[1]) )
		$active = 'active';
		else 
		$active = '';	
	$filesLinks[] = '<a href="'.$this->baseURL('results/awstats/'.$linkKey).'" class="list-group-item '.$active.'">'.$key.' <span class="badge" title="stat filesize (not number of visitors or something)" data-toggle="tooltip">'.$this->helper->badgeFormat($size).'</span><br/>'
			.$this->helper->progressThin($size, $sizeMax)
			.'</a>';
	}
$filesLinksPrint = '<div class="list-group">'.implode('', $filesLinks).'</div>';

if (!empty($p->data)) {
	#$graphPrint = $this->helper->pre($p->data);
	foreach ($p->data as $section) {
		if (!empty($this->routeParam[2]) && ($section['name'] == $this->routeParam[2])) {
			$active = 'active';
			$detailPrint = $this->helper->pre($section['content']);
			} else 
			$active = '';	
		
		$graphLinks[] = '<a href="'.$this->baseURL('results/awstats/'.$this->routeParam[0].'/'.$this->routeParam[1].'/'.$section['name']).'" class="list-group-item '.$active.'">'.$section['name'].' <span class="badge">'.$this->helper->badgeFormat($section['lines']).'</span></a>';
		}
	$graphPrint .= '<div class="list-group">'.implode('', $graphLinks).'</div>';
	}

echo '
	<div class="container">
	<h1>Data refers to literarybibliography.eu</h1>
		<div class="row">
			<div class="col-sm-2">
			'.$filesLinksPrint.'
			</div>
			<div class="col-sm-3">
			'.$graphPrint.'
			</div>
			<div class="col-sm-7">
			'.$detailPrint.'
			</div>
		</div>
	</div>
	';

?>