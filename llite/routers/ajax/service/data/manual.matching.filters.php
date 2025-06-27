<?php
require_once ('./functions/class.lists.php');

$this->addClass('lists', new lists());
$this->addJS("$('#'+page.filterField).css('opacity','1');");


if ($this->user->isLoggedIn() && $this->user->hasPower('admin')) {
	$baseQUERY = "
		FROM matching_manual a 
		LEFT JOIN matching_fields f ON a.field = f.id
		LEFT JOIN dic_rec_types rt ON a.rectype = rt.id
		LEFT JOIN dic_values_types v ON a.valuetype = v.id
		";
	$CONDITIONS = '';
	$ORDER = '';
		
	$fields = [
			'fieldname' => $this->transEsc('Field type'), 
			'value_type' => $this->transEsc('Value type'), 
			'rec_type_name' => $this->transEsc('Record type'), 
			'operator' => $this->transEsc('Operator')
			];
	
	foreach ($fields as $field=>$fieldName) {
		$t = $this->psql->querySelect("SELECT $field, count(*) as item_count 
								$baseQUERY 
								$CONDITIONS
								GROUP BY $field ORDER BY $field ");
		$menuLists[$field] = $this->lists->raportMenu($t, $field, $fieldName);
		}
	
	$input = '<input type="text" class="form-control" placeholder="'.$this->transEsc('search').'..." onkeyup="page.panelSuggestions(this.value,\'autohintsPHP\',\'filtry\');" onblur="page.hide(\'filtry\');">';
	/*
	if (!empty($listy->GET['nazwa']))
		$input='<input type="text" class="form-control" placeholder="Szukaj" value="'.$listy->GET['nazwa'].'" onkeyup="AutoPodpowiedzi(this.value,\'funkcje/DK/umowy.szukaj.php\',\'filtry\');" onblur="ukryj(\'filtry\');"> <a href="?'.$link.'" title="usuń filtr"><span class="glyphicon glyphicon-remove"></span></a>';
		else
		$input='<input type="text" class="form-control" placeholder="Szukaj" onkeyup="AutoPodpowiedzi(this.value,\'funkcje/DK/kontr_szukaj.php\',\'filtry\');" onblur="ukryj(\'filtry\');">';
	*/
	echo '
		<br/>
		<nav class="navbar navbar-default">
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