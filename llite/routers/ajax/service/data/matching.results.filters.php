<?php
require_once ('./functions/class.lists.php');

$this->addClass('lists', new lists());
$this->addJS("$('#'+page.filterField).css('opacity','1');");

$this->GET = $this->ajaxparent->GET;

if ($this->user->isLoggedIn() && $this->user->hasPower('admin')) {
	$baseQUERY = "
		FROM matching_results a 
		LEFT JOIN matching_strings s ON a.string_id = s.id
		LEFT JOIN dic_rec_types rt ON a.rectype_id = rt.id
		";
	$ConditionsSTR = '';
	$CONDITIONS = $this->lists->getConditions('matching.results');
	$ORDER = '';
	if (count($CONDITIONS))
		$ConditionsSTR = 'WHERE '.implode(' AND ', $CONDITIONS);
		
	$fields = [
			'rec_type_name' => $this->transEsc('Record type'),
			'match_type' => $this->transEsc('Match type'), 
			'match_source' => $this->transEsc('Match source'), 
			#'match_level' => $this->transEsc('Match level') 
			];
	
	foreach ($fields as $field=>$fieldName) {
		$t = $this->psql->querySelect("SELECT $field, count(*) as item_count 
								$baseQUERY 
								$ConditionsSTR
								GROUP BY $field ORDER BY $field ");
		$menuLists[$field] = $this->lists->raportMenu($t, $field, $fieldName);
		}
	
	
	$field = 'match_level';
	$t = $this->psql->querySelect("SELECT $field, count(*) as item_count 
								$baseQUERY 
								$ConditionsSTR
								GROUP BY $field ORDER BY $field ");
	$menuLists[$field] = $this->lists->graphMenu($t, $field, 'Match level');
		
		
	$sstring = $this->GET['sstring'] ?? '';
	$input = '
		<form method=GET>
		<input type="text" class="form-control" name="sstring" id="sstring" value="'.$sstring.'" placeholder="'.$this->transEsc('search').'..." >
		</form>
		';
	
	echo '
		<br/>
		<nav class="navbar navbar-default elb-panel">
		  <div class="container-fluid">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
			  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
				<span class="sr-only">Filtry</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			  </button>
			  <a class="navbar-brand" href="#"><span class="glyphicon glyphicon-filter"></span></a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			  <div class="navbar-form navbar-left" role="search">
				<div class="form-group">
				  '.$input.'
				</div>
			  </div>
			  <ul class="nav navbar-nav navbar-right">
				'.implode('',$menuLists).'
			  </ul>
			  
			</div><!-- /.navbar-collapse -->
		  </div><!-- /.container-fluid -->
		</nav>
		';
	 
	
	} 


?>