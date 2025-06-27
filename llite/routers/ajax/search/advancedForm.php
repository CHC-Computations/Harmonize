<?php 
if (empty($this)) die;
$this->addJS('$("#formBox").css("opacity","1"); ');
$this->addJS("advancedSearch.summary();");


$advS = new AdvSearch;

#$advS->addClass('psql', new postgresql($psqldb));
// moving from session to current
if (!empty($_SESSION['advSearch']['form']))
	$groups = $_SESSION['advSearch']['form'];

// setting up defaults
#unset($groups);
#unset($_SESSION['advSearch']);
$newRow = ['lookfor'=>'', 'meth'=>'contains', 'type'=>'AllFields'];

if (empty($groups)) {
	$groups[1][1] = $newRow;
	$groups[1][2] = $newRow;
	$groups['operator'][1] = 'and';
	}



/*
################################################################################
####				 actions 
################################################################################
*/
if (!empty($this->POST['action']))
	$actions = $this->POST['action'];

if (!empty($actions['addGroup'])) {
	$groups[][1] = $newRow;
	$groups['operator'][] = 'and';
	if (empty($groups['operator']['g']))
		$groups['operator']['g'] = 'and';
	}
if (!empty($actions['removeGroup'])) {
	unset($groups[$actions['removeGroup']]);
	}
	
if (!empty($actions['addRow'])) {
	$gk = floatval($actions['addRow']);
	$groups[$gk][] = $newRow;
	}
if (!empty($actions['delRow'])) {
	unset($groups[$actions['group']][$actions['delRow']]);
	}




/*
################################################################################
####				 display 
################################################################################
*/

$advS->setGroups($groups);
$content ='';
$content.= "<form method=GET name=advancedSearchForm id=advancedSearchForm>";
$LP = 0;
foreach ($groups as $gk=>$group) 
	if (is_numeric($gk)) {
		$match = '';
		if (($LP>1) && !empty($advS->groups['operator']['g']))
			$match = '<div class="col-sm-1 text-center"><b>'.$this->transEsc($advS->groups['operator']['g']).'</b></div>';
		if (($LP == 1) && count($advS->groups)>1) {
			
			$OC = "advancedSearch.newValue({ 'newValue': this.value, 'field': this.name}); ";
			$match = '<div class="col-sm-2">'.$advS->select('operator-g', $advS->Gmatches , ['id'=>'searchMatch', 'class'=>'form-control', 'onChange'=>$OC]).'</div>';
			
			}
		$match.=" ";
		$content.= $this->helper->panelCollapse(
						'group'.$gk, 
						'<div class="row">'.$match.'<div class="col-sm-5" style="vertical-align:bottom">'.$this->transEsc('Look for').'</div></div>', 
						$advS->groupFields($gk, $group) 
						);
		$LP++;
		
		}
	

$OC = "advancedSearch.refresh({ 'addGroup': 'new'}); ";
		
$content.= '<button class="btn btn-success" type="button" OnClick="'.$OC.'" name="addGroup" value="new"><i class="ph-plus-bold"></i> '.$this->transEsc('Add group').'</button>';
$content.= "</form>";

echo $this->helper->panelCollapse('lookfor', '<b>'.$this->transEsc('Search').'</b>', $content);

############### tmp displays 
/*
$x = time().'<hr/>';
if (!empty($actions))
	$x .= "Actions:<pre>".print_r($actions,1)."</pre>";
$x.= "Form Values:<pre>".print_r($advS->groups,1)."</pre>";
$this->addJS("$('#techView').html('".str_replace(array("\r", "\n"), '<br/>', $x)."')");
*/

#echo "<prE>".print_R($advS,1)."</pre>";

$_SESSION['advSearch']['form'] = $groups;


class AdvSearch extends CMS {
	
	function __construct() {
		parent::__construct();
		$searchOptions = $this->getConfig('search');
		foreach ($this->getIniParam('search', 'basicSearches') as $k=>$v) {
			@$this->opt[$k] = $this->transEsc( $v );
			}

		@$this->Gmatches['and'] = $this->transEsc( 'and' );
		$this->Gmatches['or'] = $this->transEsc( 'or' );
		
		@$this->matches['and'] = $this->transEsc( 'and' );
		$this->matches['or'] = $this->transEsc( 'or' );
		#$this->matches['without'] = $this->transEsc( 'without' );
		
		@$this->methods['contains'] = $this->transEsc( 'contains' );
		$this->methods['is'] = $this->transEsc( 'is equal to' );
		$this->methods['isnot'] = $this->transEsc( 'is not' );
		#$this->methods['isnotcontains'] = $this->transEsc( 'don`t contains' );
		$this->methods['isnotcontains'] = $this->transEsc( "don't contains" );

		}
	
	function setGroups($gr) {
		@$this->groups = $gr;
		}
	
	function fieldFalue($field,$gk,$k='1') {
		if (!empty($this->groups[$gk][$k][$field]))
			return $this->groups[$gk][$k][$field];
			else 
			return '';
		}
	
	function groupFields($gk, $group) {
		
		$content = '<table class="table table-hover">';
		/*
		$content.= '<thead>';
		$content.= '<td></td>';
		$content.= '<td>'.$this->transEsc('Field').'</td>';
		$content.= '<td></td>';
		$content.= '<td>'.$this->transEsc('Phrase').'</td>';
		$content.= '</thead>';
		$content.= '<tbody>';
		*/
		$lp = 0;
		foreach ($group as $k=>$row) {
			$lp++;
			$content.= '<tr>';
			$OC = "advancedSearch.newValue({ 'newValue': this.value, 'field': this.name}); ";
			if ($k==0)
				$content.='<thead><tr>';
				else
				$content.='<tr>';
			
			if ($lp==2)
				$content.= '<td>'.$this->select('operator-'.$gk, $this->matches , ['id'=>'searchMatch', 'class'=>'form-control', 'onChange'=>$OC]).'</td>';
				else  
				$content.= '<td> </td>';	
				
			$content.= '<td>'.$this->select($gk.'-'.$k.'-type', $this->opt , ['id'=>'searchForm', 'class'=>'form-control', 'onChange'=>$OC])  .'</td>';
			$content.= '<td>'.$this->select($gk.'-'.$k.'-meth', $this->methods , ['id'=>'searchForm', 'class'=>'form-control', 'onChange'=>$OC])  .'</td>';
			$content.= '<td><input type="text" class="form-control" value="'.$this->fieldFalue('lookfor',$gk,$k).'" name="'.$gk.'-'.$k.'-lookfor" id="'.$gk.'-'.$k.'-lookfor" OnChange="'.$OC.'"></td>';
			if (count($group)>1) {
				$OC = "advancedSearch.refresh({ 'delRow': '$k', 'group' : '$gk'}); ";
				$content.='<td><button class="btn btn-danger" type="button" OnClick="'.$OC.'" title=""><i class="ph-minus-bold"></i></button>';
				}
			$content.="<td>$gk-$k</td>";
			
			if ($k==0)
				$content.='</tr></thead><tbody>';
				else
				$content.= '</tr>';
			}
			
		$content.= '</tbody>';
		$content.= '</table>';
		$OC = "advancedSearch.refresh({ 'addRow': '$gk'}); ";
		$content.= '<button class="btn btn-success" type="button" OnClick="'.$OC.'"><i class="ph-plus-bold"></i> '.$this->transEsc('Add row').'</button>';
		if (count($this->groups) > 1) {
			$OC = "advancedSearch.refresh({ 'removeGroup': '$gk'}); ";
			$content.= ' <button class="btn btn-danger" type="button" OnClick="'.$OC.'"><i class="ph-trash-bold"></i> '.$this->transEsc('Remove group').'</button>';
			}
			
		#$content .="Form Values:<pre>".print_r($this->groups,1)."</pre>";	
		return $content;
		}
		
	
	public function select($id, $values = [], $o = []) {
		$addOns = '';
		$name = $id;
		
		$tmp = explode('-',$id);
		$gk = $tmp[0];
		if (count($tmp)>2) {
			$fk = $tmp[1];
			$field = $tmp[2];
			} else {
			$fk = 'nofield';
			$field = $tmp[1];
			}
		
		if (!empty($o['class']))
			$class = $o['class'];
			else 
			$class = '';
		
		if (!empty($o['id']))
			$id = $o['id'].'_'.$id;
		
		if (!empty($o['onChange']))
			$addOns.=" onChange=\"$o[onChange]\";";
		
		$options = '';
		foreach ($values as $k=>$v) {
			$selected = '';
			if (!empty($this->groups[$gk][$fk][$field]) && ($this->groups[$gk][$fk][$field] == $k) )
				$selected = 'selected="selected"';
			if (!empty($this->groups[$gk][$field]) && ($this->groups[$gk][$field] == $k) )
				$selected = 'selected="selected"';
			
			$options .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
			}
		return '<select id="'.$id.'" class="'.$class.'" name="'.$name.'" data-native-menu="false" aria-label="Search type" '.$addOns.'>
					'.$options.'
				</select>';
		}
	
	
	}