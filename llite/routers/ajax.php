<?php 
if (empty($this)) die;


switch ($this->params[3]) {
	default : echo "unknow action: "; print_r($this->routeParam); break;
	case 'facet': echo $this->render('search/facet-box.php', ['facet'=>$this->params[4]]); break;
}

$run=$this->runTime();

echo " <i class='fa fa-stopwatch' title='working time: {$run}s'></i>";