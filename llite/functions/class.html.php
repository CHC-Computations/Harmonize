<?PHP


class HTML {
	public $GLOBAL_MENU='dane/'; // ścieżka dostępu do folderu z menu 
	public $start_time=0; // uruchomienie systemu
	public $aktywny_modul=array(); 
	public $aktywny_menu=""; 
	public $before=true; 
	public $ALERTY='';
	
	public $ProbnikKolorow="<div class=row>
				<div class=col-sm-1 style='background-color:#DEEDDA; height:100px;'>Jasny zielony</div>
				<div class=col-sm-1 style='background-color:#C1E0C1; height:100px;'>Zielony</div>
				<div class=col-sm-1 style='background-color:#9BD3A2; height:100px;'>Zieleń trawy</div>
				<div class=col-sm-1 style='background-color:#66BF87; height:100px;'>Zieleń natruralna</div>
				<div class=col-sm-1 style='background-color:#AFDAED; height:100px;'>Jasny niebieski</div>
				<div class=col-sm-1 style='background-color:#5EC0ED; height:100px;'>Błękit</div>
				<div class=col-sm-1 style='background-color:#87A6D5; height:100px;'>Lawendowy</div>
				<div class=col-sm-1 style='background-color:#DCB9D7; height:100px;'>Lila</div>
				<div class=col-sm-1 style='background-color:#FBCEB7; height:100px;'>Łososiowy</div>
				<div class=col-sm-1 style='background-color:#F7D80E; height:100px;'>Złoty</div>
				<div class=col-sm-1 style='background-color:#FCFCD8; height:100px;'>Kremowy</div>
				<div class=col-sm-1 style='background-color:#F8F1D7; height:100px;'>Piaskowy</div>
			</div>";
	
	
	public $Miesiace=array(
			'01'=>'styczeń',
			'02'=>'luty',
			'03'=>'marzec',
			'04'=>'kwiecień',
			'05'=>'maj',
			'06'=>'czerwiec',
			'07'=>'lipiec',
			'08'=>'sierpień',
			'09'=>'wrzesień',
			'10'=>'październik',
			'11'=>'listopad',
			'12'=>'grudzień'
			);
	public $RomanMonth=array( 
			'01'=>'I',
			'02'=>'II',
			'03'=>'III',
			'04'=>'IV',
			'05'=>'V',
			'06'=>'VI',
			'07'=>'VII',
			'08'=>'VIII',
			'09'=>'IX',
			'10'=>'X',
			'11'=>'XI',
			'12'=>'XII'
			);
	
	public function __construct () {
		global $OPCJE;
		$this->start_time=$this->gen_www();	
		$this->OPCJE=$OPCJE;
		
		}
		
	
	public function nr_miesiaca($t) {
		foreach ($this->Miesiace as $k=>$m)
			if ($t==$m)
				return floatval($k);
		}	
		
	public function miesiac($t,$r='nazwa') {
		if ($t<10)
			$t='0'.$t;
		if ($r=='nazwa') 
			return $this->Miesiace[$t];
			else 
			return $this->RomanMonth[$t];	
		}
	
	public function ModalPrzerwaTechniczna($tresc=null) {
		
		return "
			<div class='modal-header' style='background-color:#fcf8e3;'>
				<button type='button' class='close' data-dismiss='modal'>&times;</button>
				<h3 class='modal-title' style='color:#800;'>Pracuje nad tym!</h3>
			</div>
			<div class='modal-body' >
				<div class=row>
					<div class='col-sm-2 center'><span class='glyphicon glyphicon glyphicon-wrench' style='font-size:3em; padding:15px; padding-top:40px; color:#800;'></span></div>
					<div class=col-sm-10>
						<h3>Pracuje nad poprawkami w tym module.</h3>
						<h2>Zapraszam później. 	</h2>
					</div>
				</div>
			</div>
			<div class='modal-footer' id=pole_akcji>
				<button type='button' class='btn btn-warning' data-dismiss='modal'><span class='glyphicon glyphicon-remove'></span> Zamknij</button>
			</div>
			";
		}
	public function AlertPrzerwaTechniczna($tresc=null) {
		
		return $this->Alert('danger',"
						<div class=row>
							<div class='col-sm-2 center'><span class='glyphicon glyphicon glyphicon-wrench' style='font-size:3em; padding:15px;'></span></div>
							<div class=col-sm-9>
							<h3 style='margin-top:0px;'>Pracuje nad tym!</h3>
							Usuwam błędy, wprowadzam udoskonalenia lub tworzę nowe błędy. Zapraszam później. <br/>
							$tresc
							</div>
						</div>");
		}
	
	public function termin_klasa($data) {
		$termin=round((strtotime($data)-time())/86600);
		if ($termin>1)
			return 'success';
		if (($termin==0)or($termin==1))
			return 'warning';
		if ($termin<0)
			return 'danger';
		}
	
	public function termin_text($data) {
		$termin=floor((strtotime($data)-mktime())/86400)+1;
		if ($termin==0) 
			return 'dziś'; 
			elseif ($termin==1)
				return 'jutro';
			elseif ($termin>1)
				return "za $termin dni";
			elseif ($termin==-1)
				return 'wczoraj';
			elseif ($termin<-1)
				return "$termin dni temu";
		}
	
	
	public function AddAlert($string) {
		$this->ALERTY.=$string;
		}	
	
	
	
	public function gen_www(){
	    $time = explode(" ", microtime());
	    $usec = (double)$time[0];
	    $sec = (double)$time[1];
		return $sec + $usec;
		}
	
	public function runTime() {
		return $this->gen_www() - $this->start_time;
		}
	
	public function StopkaStrony($user=null) {
		
		$run=$this->runTime();
		
	
		$tresc= "
			<div id=bottom></div>
			<div id='contentFooter' class='content-footer'>
				<div id=session>Sesja </div>
				<div id=timer>Gotowe w: <b>".substr($run, 0, 5)."</b> sek.</div>
				<div id=version>Wersja: <b>BETA 1 </div>
				<div id=counter>...</div>
				<div id=cookie>online: </div>
				<div id=down_menu><a href='#bottom' OnClick='page.ScrollDown();'><span class='glyphicon glyphicon-chevron-down'></span> stopka</a></div>
				<div id=up_menu><a href='#TrescStrony' OnClick='page.ScrollUp();'><span class='glyphicon glyphicon-chevron-up'></span> góra</a></div>
			</div>
			<div class='md-background'><div class='md-content'></div></div>
			
			";
		return $tresc;
		}	

	public function CheckSession() {
		$sesja=print_r($_SESSION,TRUE);
		return "
				<div class=sesja-btn OnClick=\"Session.Show();\">Aktywna sesja</div> 
				<div class=sesja-pole>
					<form method=post action='' style='padding:5px; position:fixed; bottom:0px;'>
						<div class=btn-group>	
							<button class='btn btn-warning' type=button OnClick=\"Session.Show();\"><span class='glyphicon glyphicon-remove'></span> zamknij podgląd</button>
							<button class='btn btn-danger' type=submit name='sesja-akcja' value='wyczyść sesję'><span class='glyphicon glyphicon-trash'></span> wyczyść sesję</button>
						</div>
					</form>
					<pre id=pre_sesji>$sesja</pre><br/><br/>
				</div>";
			 
		}
		
	public function Alert($klasa,$tresc) {
		return "
			<div class='alert alert-$klasa alert-dismissible' role='alert'><button type='button' class='close' data-dismiss='alert'><span aria-hidden='true'>&times;</span><span class='sr-only'>Zamknij</span></button>
			$tresc
			</div>
			";
		}
	
	public function AlertInfo($klasa,$glyphicon,$tresc=null) {
		
		return $this->Alert($klasa,"
						<div class=row>
							<div class='col-sm-2 center'><span class='glyphicon glyphicon glyphicon-$glyphicon' style='font-size:3em; padding:15px;'></span></div>
							<div class=col-sm-9>
							$tresc
							</div>
						</div>");
		}
	
	
	
	public Function TakNie($zm) {
		if (strtolower($zm)=='t') return 'Tak';
		if (strtolower($zm)=='f') return 'Nie';
		if ($zm) return 'Tak';
			else return 'Nie';
		return '---';
		}
	
	public function Modal() {
		return "<!-- Modal -->
		<div id='myModal' class='modal fade' role='dialog'>
		  <div class='modal-dialog modal-lg'>

			<!-- Modal content-->
			<div class='modal-content' id='okno_formularza_zawartosc'>
			  
			</div>

		  </div>
		</div>";
		}
	
	public function ToolTip($symbol,$tresc,$kolor='') {
		$tresc=str_replace('<br/>',"\n",$tresc);
		$tresc=strip_tags($tresc);
		return "<span style='cursor: help; text-align:left;' data-toggle='tooltip' data-placement='top' title='$tresc'><span class='glyphicon glyphicon-$symbol $kolor'></span></span>";
		}

	public function PopOver($symbol,$naglowek,$tresc,$kolor='') {
		$tresc=str_replace('<br/>',"\n",$tresc);
		$tresc=strip_tags($tresc);
		return "<a style='cursor: help; text-align:left;' data-toggle='popover' data-placement='top' title='$naglowek' data-content='$tresc'><span class='glyphicon glyphicon-$symbol $kolor'></span></a>";
		}
	
	Public function PanelCollapse($id, $tytul, $tresc, $stopka='', $rozwiniety='true', $klasa='default') {
		if ($stopka<>'')
			$stopka="<div class='panel-footer'>$stopka</div>";
		if (($rozwiniety=='true')or($rozwiniety===true)) {
			$kl='glyphicon-collapse-up';
			$in='in';
			$rozwiniety='true';
			} else {
			$kl='glyphicon-collapse-down';
			$rozwiniety='false';
			$js="$('#{$id}_body').collapse('hide');";
			}
		return "
			<div class='panel panel-$klasa' id='{$id}_panel'>
				<div class='panel-heading' role='tab' data-toggle='collapse' data-target='#{$id}_body' >
					<button type='button' class='close'><span class='glyphicon $kl' id='{$id}_iko'></span></button> $tytul
				</div>
				<div id='{$id}_body' role='tabpanel' class='collapse'>
					<div class='panel-body'>
						$tresc	
					</div>
					$stopka
				</div>
			</div>
			<script>
				$('#{$id}_body').collapse({ toggle: $rozwiniety });
				$('#{$id}_body').on('shown.bs.collapse', function () {
					$('#{$id}_iko').removeClass('glyphicon-collapse-down').addClass('glyphicon-collapse-up');
					});
				$('#{$id}_body').on('hidden.bs.collapse', function () {
					$('#{$id}_iko').removeClass('glyphicon-collapse-up').addClass('glyphicon-collapse-down');
					});
			</script>
			";
		// $('#{$id}_body').collapse('hide');
		}

	public function tabsCarousel($tabs=array(), $active = '') {
		if (is_array($tabs)) {
			$id = uniqid();
			$result = '<ul class="nav nav-tabs">';
			$car = '<div id="myCarousel'.$id.'" class="carousel slide" data-ride="carousel">
					<div class="carousel-inner">
					';
			$lp = 0;
			foreach ($tabs as $tabcode=>$tab) {
				if ($active==$tabcode) 
					$aclass='active';
					else 
					$aclass='';
				$onClick = "$('.ind_{$id}').removeClass('active'); $('#ind_{$id}_{$lp}').addClass('active'); ";
				$result .= '
					<li class="'.$aclass.' ind_'.$id.'" id="ind_'.$id.'_'.$lp.'" data-target="#myCarousel'.$id.'" data-slide-to="'.$lp.'">
						<a href="#'.$tabcode.'" OnClick="'.$onClick.'">'.$tab['label'].'</a>
					</li>';
				$car.='
					<div class="item '.$aclass.'">
					  '.$tab['content'].'
					</div>
					';
				$lp++;
				}
			$car .='</div></div>';
			$result .='</ul><br/>'.$car;
			
			$result .= '
				<script>
					$(document).ready(function(){
						$("#myCarousel'.$id.'").carousel({interval: false});
						});
				</script>
				';
			
		
			return $result;
			} else 
			return null;
		}
	
	public function CzytajINI($plik) {
		$plik=file("$plik");
		foreach ($plik as $line)
			if ($line[0]=='[') 
				$kat=str_replace('[','',str_replace(']','',trim($line)));
				else if (isset($tabela["$kat"])) 
					$tabela["$kat"].='<br />'.trim($line);
					else 
					$tabela["$kat"]=trim($line);
		return $tabela;
		}
	
	public function Kreciolek($komunikat) {
		return "<div class=\"progress\"><div class=\"progress-bar progress-bar-striped active\" role=\"progressbar\" aria-valuenow=\"100\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width:100%\">$komunikat</div></div>";
		}
	
	public function Graph ($liczba, $maks=100, $klasa='-success') {
		if ($klasa!='-success')
			$klasa='-$klasa';
		if ($liczba!='') {
			$proc=ceil(($liczba/$maks)*100);
			return "
				<div class='procent-box'>
					<span class=overlaygrow><span class=procent-box-bar$klasa style='width:".$proc."%;'>&nbsp;</span></span>
					<span class=overlay><span class=liczba>$liczba</span></span>
				</div>";
			} else 
			return '---';
		}
	
	public function Procent($ile,$suma, $klasa='primary') {
		$procent=round(($ile/$suma)*100,2);
		if ($procent>100) 
			$procent=100;
		$sl=round(($ile/$suma)*100,0);
		$slopek="
				<div class='progress'>
				  <div class='progress-bar progress-bar-$klasa' role='progressbar' aria-valuenow='$ile' aria-valuemin='0' aria-valuemax='$suma' style='width:$sl%'>
					$procent% 
				  </div>
				</div> 
				";
		return $slopek;		
		}
	
	public function MultiProcent($dane=array(),$max) {
		foreach ($dane as $row) {
			$procent=round(($row[count]/$max)*100,2);
			$bar.="<div class='progress-bar progress-bar-$row[class]' style='width: {$procent}%' title='$row[title]' data-toggle='tooltip'>
					<span class='sr-only'>{$procent}% $row[title]</span>
				</div>";
			}		
					
		$slopek="		
			<div class='progress'>
				$bar
			</div>";
				
		return $slopek;		
		}
	
	public function ClearLink($pole='') {
		
		if (is_Array($pole))
			$pole[]='show';
			else {
			$tmp=$pole;
			unset($pole);
			$pole[]=$tmp;
			$pole[]='show';
			}
		
		$bl=explode('?',$_SERVER[HTTP_REFERER]);
		$get=explode('&',$bl[1]);
		if (is_array($get))
			foreach ($get as $k){
				$tmp=explode('=',$k);
				$GET[$tmp[0]]=$tmp[1];
				}
		if (is_array($_SESSION[GET])){
			$GET=$_SESSION[GET];
			if (is_array($pole)){ 
				While (list($key,$val)=each($GET))
					if(!in_array($key,$pole)) 
						if ($key<>'poz') $Tlink[]="$key=".urlencode($val);
							else $Tlink[]="$key=$val";
				} else
				While (list($key,$val)=each($GET))
					if($key<>$pole) 
						if ($key<>'poz') $Tlink[]="$key=".urlencode($val);
							else $Tlink[]="$key=$val";
			$link='';
			if (isset($Tlink))
				if (is_array($Tlink)) $link=implode("&amp;",$Tlink);
					else $link=$Tlink;
			}
		return $link;
		// http_build_query($GET);
		}
	
	
	public function DzienTygodnia($data) {
		$dni=array(
			'0'=>'niedziela',
			'1'=>'poniedziałek',
			'2'=>'wtorek',
			'3'=>'środa',
			'4'=>'czwartek',
			'5'=>'piątek',
			'6'=>'sobota',
			'7'=>'niedziela',
			); 
		$dt=date("w", strtotime($data));
		#$dt=date("l", strtotime($data));
		return $dni[$dt];
		}
	

	public function TabelaSwiat($rok,$miesiac) {
		global $pgs;
		# echo "$rok, $miesiac";
		$t=$pgs->Query_select("SELECT * FROM sys_swieta WHERE (rok=0 OR rok='$rok') AND miesiac='".floatval($miesiac)."'");
		if (is_array($t))
			foreach ($t as $row) {
				if ($row[miesiac]<10)
					$row[miesiac]='0'.$row[miesiac];
				if ($row[dzien]<10)
					$row[dzien]='0'.$row[dzien];
				if ($row[rodzaj]=='święto')
					$this->Swieta["$rok-$row[miesiac]-$row[dzien]"]=$row[nazwa];
					else 
					$this->Wydarzenia["$rok-$row[miesiac]-$row[dzien]"]=$row[nazwa];	
				}
		return $this->Swieta;
		}
	
	public function GenerujKalendarz($Twydarzen,$miesiac='',$rok='',$filtry=array()) {
		global $pgs;
		
		if ($miesiac=='')
			$miesiac=Date("m");
		if ($rok=='')
			$rok=date("Y");
			
			
		$M=$this->Miesiace;
		
		$Tswiat=$this->TabelaSwiat($rok,$miesiac);
		
		$poprzedni=$this->checkM($miesiac-1);
		$nastepny=$this->checkM($miesiac+1);
		$nrok=$prok=$rok;
		if ($poprzedni==12) $prok=$rok-1;
		if ($nastepny==01) $nrok=$rok+1;
		
		$kalendarz="";
		$str_data_start="$rok-$miesiac-01 12:00";
		$data_start=strtotime($str_data_start);
		$tydzien_roku=Date("W",$data_start);
		$dzien_tygodnia=Date("w",$data_start); // dzień tygodnia 1 dnia miesiaca. 
		if ($dzien_tygodnia==0) $dzien_tygodnia=7;
		$data_start=$data_start-$dzien_tygodnia*86400;
		$tygodni=5;
		
		$licznik_dni=0;
		for ($tyg=$tydzien_roku; $tyg<=$tydzien_roku+$tygodni; $tyg++) {
			$wiersz="";
			for ($i=1;$i<=7;$i++) {
				$licznik_dni++;
				$dzien=$data_start+$licznik_dni*86400;
				$dzien_miesiaca=Date("j",$dzien);
				$nmiesiac=Date("m",$dzien);
				#if ($licznik_dni==1) $last_miesiac=$miesiac;
				$nrok=Date("Y",$dzien);
				$styl='';
				if ($i==6) $styl='sob';
				if ($i==7) $styl='nie';
				if ($nmiesiac<>$miesiac) 
					$styl='empty';
				$data_dzis=date("Y-m-d",$dzien);
				if (date("Y-m-d")==date("Y-m-d",$dzien)) $styl.=" dzis";
				if ($Tswiat[$data_dzis]<>'') $styl.=" nie"; 
				$color='';
				$wydarzenia='';	
				if (is_array($Twydarzen[$data_dzis])) {
					$styl.=" wydarzenie";
					ksort($Twydarzen[$data_dzis]);
					foreach ($Twydarzen[$data_dzis] as $hstart=>$event) 
						foreach ($event as $user=>$row){
							$hstart=substr($hstart,0,5);
							
							$wydarzenia.="<span class='event' style='background-color:$row[color];' OnClick='AkcjaOnClick(\"$row[id]\");'><span class=godzina>$hstart $row[ico]</span><span class=opis>$row[name]</span></span>";
						}
					}
				if (($i==1)and($nmiesiac==$nastepny)) $nd='t';
				$wiersz.="<td class='$styl'><span class='numer' OnClick=\"AkcjaOnClick('in','$data_dzis');\"><span class=sw>".$Tswiat[$data_dzis]."</span><span class=cf>$dzien_miesiaca</span></span>$wydarzenia</td>";
				}
			if ($nd<>'t')
				$kalendarz.="<tr><td class=tygR>$tyg</td>$wiersz</tr>";
			}
		
		
		$lista=implode('',$filtry);
		if ($lista=='')
			$lista='<li><a href="#" role="button" aria-haspopup="true" aria-expanded="false">brak filtrów</a></li>';
		
		$kalendarz=" 
		<nav class='navbar navbar-default'>
		  <div class='container-fluid'>
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class='navbar-header'>
			  <button type='button' class='navbar-toggle collapsed' data-toggle='collapse' data-target='#bs-example-navbar-collapse-1' aria-expanded='false'>
				<span class='sr-only'>Filtry</span>
				<span class='icon-bar'></span>
				<span class='icon-bar'></span>
				<span class='icon-bar'></span>
			  </button>
			<a class='navbar-brand' href='#' onClick=\"Kalendarz('".date("m")."','".date("Y")."');\"><span class='glyphicon glyphicon-calendar'></span> dziś</button>
			<a class='navbar-brand' href='#' onClick=\"Kalendarz('$poprzedni','$prok');\"><span class='glyphicon glyphicon-chevron-left'></span></button>
			<a class='navbar-brand' href='#' onClick=\"Kalendarz('$nastepny','$nrok');\"><span class='glyphicon glyphicon-chevron-right'></span></button>
			<a class='navbar-brand' href='#' onClick=\"Kalendarz('$miesiac','$rok');\"> <b>$M[$miesiac]</b> $rok</button>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class='collapse navbar-collapse' id='bs-example-navbar-collapse-1'>
			  <ul class='nav navbar-nav navbar-right'>
				".$lista."
			  </ul>
			  
			</div><!-- /.navbar-collapse -->
		  </div><!-- /.container-fluid -->
		</nav>
		
		<table class=kalendarz>
		 <thead>
		  <tr>
		   <td class=tygR></td>
		   <td>Pon.</td>
		   <td>Wt.</td>
		   <td>Śr.</td>
		   <td>Czw.</td>
		   <td>Pt.</td>
		   <td>So.</td>
		   <td>Nie.</td>
		  </tr>
		  </thead>
		  $kalendarz
		  </table>
		  ";
		return $kalendarz;
		}
	
	public function MiniKalendarz($data,$Twydarzen=array()) {
		if ($this->MiniKalendarz->AOC=='')
			$FOC='AkcjaOnClick';
			else 
			$FOC=$this->MiniKalendarz->AOC;
		if ($data=='') {
			$rok=date("Y");
			$miesiac=Date("m");
			} else {
			$tmp=explode('-',$data);
			$rok=$tmp[0];
			$miesiac=$tmp[1];
			}
		if ($miesiac<10)
			$miesiac='0'.floatval($miesiac);
					
		
		$M = $this->Miesiace;
		$Tswiat=$this->TabelaSwiat($rok,$miesiac);
		
		
		$poprzedni=$this->checkM($miesiac-1);
		$nastepny=$this->checkM($miesiac+1);
		$nrok=$prok=$rok;
		if ($poprzedni==12) $prok=$rok-1;
		if ($nastepny==01) $nrok=$rok+1;
		
		$kalendarz="";
		$str_data_start="$rok-$miesiac-01 12:00";
		$data_start=strtotime($str_data_start);
		$tydzien_roku=Date("W",$data_start);
		$dzien_tygodnia=Date("w",$data_start); // dzień tygodnia 1 dnia miesiaca. 
		if ($dzien_tygodnia==0) $dzien_tygodnia=7;
		$data_start=$data_start-$dzien_tygodnia*86400;
		$tygodni=5;
		
		$licznik_dni=0;
		for ($tyg=$tydzien_roku; $tyg<=$tydzien_roku+$tygodni; $tyg++) {
			$wiersz="";
			for ($i=1;$i<=7;$i++) {
				$licznik_dni++;
				$dzien=$data_start+$licznik_dni*86400;
				$dzien_miesiaca=Date("j",$dzien);
				$nmiesiac=Date("m",$dzien);
				#if ($licznik_dni==1) $last_miesiac=$miesiac;
				$nrok=Date("Y",$dzien);
				$styl='';
				if ($i==6) 
					$styl='sob';
				if ($i==7) 
					$styl='nie';
				$data_dzis=date("Y-m-d",$dzien);
				$pdata_dzis=date("m-d",$dzien);
				if (date("Y-m-d")==date("Y-m-d",$dzien)) 
					$styl.=" dzis";
				
				$color='';
				$wydarzenia='';	
				if (is_array($Twydarzen[$data_dzis])) {
					$styl.=" wydarzenie";
					ksort($Twydarzen[$data_dzis]);
					foreach ($Twydarzen[$data_dzis] as $login=>$event) {
						if ($event[color]=='')
							$event[color]='pink';
						unset($pozycje);
						foreach ($event[pozycje] as $idp=>$row){
							$OC=$href='';
							if ($row[onClick]<>'')
								$OC="onClick=\"$row[onClick]\"";
							if ($row[link]<>'')
								$href="href='$row[link]'";
							$pozycje.="<li><a $OC $href>$row[name]</a></li>";
							}	
						#$wydarzenia.="<span class='event' style='background-color:$event[color];' title='$event[nazwa]' data-toggle='$ile'></span>";
						if ($event[onClick]<>'')
							$OC="onClick=\"$event[onClick]\"";
						$wydarzenia.="
							<div class=event>
								<div class='dropdown'>
									<a class='dropdown-toggle' data-toggle='dropdown' style='cursor:pointer;'><span class='glyphicon glyphicon-stop' style='color:$event[color];'></span></a>
									<ul class='dropdown-menu'>
										<li class='dropdown-header' $OC>$event[nazwa]</li>
										<li role='presentation' class='divider'></li>
										$pozycje
									</ul>
								</div>
							</div>";
						}
					}
				if (($i==1)and($nmiesiac==$nastepny)) 
					$nd='t';
				$SW='';
				if ($this->Wydarzenia[$data_dzis]<>'')
					$SW="<span class='glyphicon glyphicon-star-empty' style='float:left; font-size:0.7em; padding:4px;' title='".$this->Wydarzenia[$data_dzis]."' data-toggle='tooltip'></span> ";
				if ($this->Swieta[$data_dzis]<>'') {
					$SW="<span class='glyphicon glyphicon-star text-danger' style='float:left; font-size:0.7em; padding:4px;' title='".$this->Swieta[$data_dzis]."' data-toggle='tooltip'></span> ";
					$styl.=" nie";
					}
				if ($nmiesiac<>$miesiac) {
					$styl='empty';
					$SW='';
					}
				
				$wiersz.="<td class='$styl'><span class='numer'>$SW<span class=cf>$dzien_miesiaca</span></span>$wydarzenia</td>";
				}
			if ($tyg<10)
				$tyg='0'.floatval($tyg);
			if ($nd<>'t')
				$kalendarz.="<tr><td class=tygR>$tyg</td>$wiersz</tr>";
			}
		
		if ($this->DrukujMiesiac=='tak')
			$Naglowek="<div class='kal_naglowek'>".$this->Miesiace[$miesiac]."</div>";
		$kalendarz="
			$Naglowek
			<table class=kalendarz>
				<thead>
					<tr>
						<td class=tygR></td>
						<td>Pon.</td>
						<td>Wt.</td>
						<td>Śr.</td>
						<td>Czw.</td>
						<td>Pt.</td>
						<td>So.</td>
						<td>Nie.</td>
					</tr>
				</thead>
				<tbody>
					$kalendarz
				</tbody>
			  </table>
			  ";
		return $kalendarz;
		}
		
	public function LosujKolor() {
		$r=dechex (rand(150,240));
		$g=dechex (rand(150,240));
		$b=dechex (rand(150,240));
		
		$kolor="#$r$g$b";
		return $kolor;
		}
	
	
	public function GenerujKalendarzAgr($Twydarzen,$miesiac='',$rok='',$filtry=array()) {
		global $pgs;
		
		if ($miesiac=='')
			$miesiac=Date("m");
		if ($rok=='')
			$rok=date("Y");
			
		$M=$this->Miesiace;
		$Tswiat=$this->TabelaSwiat($rok,$miesiac);

		
		$poprzedni=$this->checkM($miesiac-1);
		$nastepny=$this->checkM($miesiac+1);
		$nrok=$prok=$rok;
		if ($poprzedni==12) $prok=$rok-1;
		if ($nastepny==01) $nrok=$rok+1;
		
		$kalendarz="";
		$str_data_start="$rok-$miesiac-01 12:00";
		$data_start=strtotime($str_data_start);
		$tydzien_roku=Date("W",$data_start);
		$dzien_tygodnia=Date("w",$data_start); // dzień tygodnia 1 dnia miesiaca. 
		if ($dzien_tygodnia==0) $dzien_tygodnia=7;
		$data_start=$data_start-$dzien_tygodnia*86400;
		$tygodni=5;
		
		$licznik_dni=0;
		$maxW=0;	
				
		for ($tyg=$tydzien_roku; $tyg<=$tydzien_roku+$tygodni; $tyg++) {
			$wiersz="";
			for ($i=1;$i<=7;$i++) {
				$licznik_dni++;
				$dzien=$data_start+$licznik_dni*86400;
				$dzien_miesiaca=Date("j",$dzien);
				$nmiesiac=Date("m",$dzien);
				#if ($licznik_dni==1) $last_miesiac=$miesiac;
				$nrok=Date("Y",$dzien);
				$styl='';
				if ($i==6) $styl='sob';
				if ($i==7) $styl='nie';
				if ($nmiesiac<>$miesiac) 
					$styl='empty';
				$data_dzis=date("Y-m-d",$dzien);
				if (date("Y-m-d")==date("Y-m-d",$dzien)) $styl.=" dzis";
				if ($Tswiat[$data_dzis]<>'') $styl.=" nie"; 
				$color='';
				$wydarzenia='';	
				unset($Twud);
				if (is_array($Twydarzen[$data_dzis])) {
					$styl.=" suma-osoby";
					foreach ($Twydarzen[$data_dzis] as $user=>$Tzad)
						foreach ($Tzad as $row) {
							if($row[color]=='')
								$Twydarzen[$data_dzis][$user][color]=$row[color]=$this->LosujKolor();
							$Twud[$user][zadania][$row[name]][ile]++;
							$Twud[$user][zadania][$row[name]][ico]=$row[ico];
							$Twud[$user][color]=$row[color];
							}
					}
				$lw=0;
				if (is_array($Twud))
					foreach ($Twud as $user=>$row) {
						unset($wz);
						foreach ($row[zadania] as $zadanie)
							$wz[]="$zadanie[ico] $zadanie[ile]";
						$zadania=implode(', ',$wz);
						$font=$this->FontColor($row[color]);
						$wydarzenia.="
								<span class='event' style='background-color:$row[color]; color:$font;' OnClick=\"kal.rdzien('$data_dzis','$user');\">
									<span class=opis>$user</span>
									<span class=godzina style='background-color:$row[color]; color:$font;' >$zadania</span>
								</span>";
						$lw++;
						}
				if ($maxW<$lw)
					$maxW=$lw;
				
				if (($i==1)and($nmiesiac==$nastepny)) $nd='t';
				$wiersz.="<td class='$styl'><span class='numer' OnClick=\"kal.rdzien('$data_dzis');\"><span class=cf>$dzien_miesiaca</span><br/><span class=sw>".$Tswiat[$data_dzis].$this->Wydarzenia[$data_dzis]."</span></span>$wydarzenia</td>";
				}
			if ($nd<>'t')
				$kalendarz.="<tr><td class=tygR>$tyg</td>$wiersz</tr>";
			}
		
		
		$lista=implode('',$filtry);
		if ($lista=='')
			$lista='<li><a href="#" role="button" aria-haspopup="true" aria-expanded="false">brak filtrów</a></li>';
		
		$wys=24+$maxW*34;
		$kalendarz=" 
			<nav class='navbar navbar-default'>
			  <div class='container-fluid'>
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class='navbar-header'>
				  <button type='button' class='navbar-toggle collapsed' data-toggle='collapse' data-target='#bs-example-navbar-collapse-1' aria-expanded='false'>
					<span class='sr-only'>Filtry</span>
					<span class='icon-bar'></span>
					<span class='icon-bar'></span>
					<span class='icon-bar'></span>
				  </button>
				<a class='navbar-brand' href='#' onClick=\"kal.caly('".date("m")."','".date("Y")."');\"><span class='glyphicon glyphicon-calendar'></span> dziś</button>
				<a class='navbar-brand' href='#' onClick=\"kal.caly('$poprzedni','$prok');\"><span class='glyphicon glyphicon-chevron-left'></span></button>
				<a class='navbar-brand' href='#' onClick=\"kal.caly('$nastepny','$nrok');\"><span class='glyphicon glyphicon-chevron-right'></span></button>
				<a class='navbar-brand' href='#' onClick=\"kal.caly('$miesiac','$rok');\"> <b>$M[$miesiac]</b> $rok</button>
				</div>

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class='collapse navbar-collapse' id='bs-example-navbar-collapse-1'>
				  <ul class='nav navbar-nav navbar-right'>
					".$lista."
				  </ul>
				  
				</div><!-- /.navbar-collapse -->
			  </div><!-- /.container-fluid -->
			</nav>
			
			<table class=kalendarz>
			 <thead>
			  <tr>
			   <td></td>
			   <td>Pon.</td>
			   <td>Wt.</td>
			   <td>Śr.</td>
			   <td>Czw.</td>
			   <td>Pt.</td>
			   <td>So.</td>
			   <td>Nie.</td>
			  </tr>
			  </thead>
			  $kalendarz
			  </table>
			  <script>$('.tygR').css('height','{$wys}px');</script>
		  ";
		return $kalendarz;
		}
	
	public function checkM ($liczba){
		if ($liczba==0) return 12;
		if (strlen($liczba)==1) return '0'.$liczba;
		if ($liczba==13) return '01';
		return $liczba;
		}
	
	public function FontColor($rgb) {
		$rgb=str_replace('#','',$rgb);
		if (strlen($rgb)>4) {
			$r=hexdec(substr($rgb,0,2));
			$g=hexdec(substr($rgb,2,2));
			$b=hexdec(substr($rgb,4,2));
			} else {
			$r=hexdec(substr($rgb,0,1));
			$g=hexdec(substr($rgb,1,1));
			$b=hexdec(substr($rgb,2,1));
			} 
				
		$sum=$r+$g+$b;		
		#echo "$rgb, $r, $g, $b, $sum<Br/>";		
		if(($r+$g+$b) > 400) 
			return '#000000'; 
			else 
			return '#fff';
		}

	public function TimeLine($lista=array(),$naglowek='') {
		#echo "<pre>".print_r($lista,1)."</pre>";
		
		if (is_Array($lista))
			foreach ($lista as $row) {
				$lp++;
				$tmp=explode(' ',strip_tags($row[data]));
				$dzien=$pdzien=$tmp[0];
				if ($olddzien==$dzien) {
					$pdzien='';
					}
				$olddzien=$dzien;
				$czas=substr($tmp[1],0,5);
				$clouddzien=$this->DzienTygodnia($dzien).' '.$dzien;
				$LT.="
					<div class='line-time-container'>
						<div class=line-time-header>
							<span class='line-time-data right'><b>$pdzien</b> <small>$czas</small></span>
							<span class=line-time-point style='cursor:pointer' title='$clouddzien' data-toggle='tooltip'><span>&nbsp;</span></span>
							<span class=line-time-operator>$row[login]</span>
							<span class=line-time-kind>$row[czynnosc]</span>
							<span class='line-time-title $row[klasa]'><span>$row[uwagi]</span></span>
						</div>
					</div>
					";
				}
		if ($naglowek<>'')
			$LT.= "<div class='line-time-descriptions'><span>$naglowek</span></div>";
					
		$CRM="<div class=line-time>$LT</div>";
		return $CRM;
		}	

	public function Notatki($notatki=array()) {
		$ile=count($notatki);
		if ($ile>0) {
			if ($ile<3)
				$kol=6;
				else 
				$kol=4;
			if ($kol==12)
				$kol=6;
			echo "<div class=row>";
			foreach ($notatki as $row) {
				$data=substr($row[data_wpisu],0,16);
				echo "<div class=col-sm-$kol id='notatka_$row[id]'>
						<div class=notatka>
						<button class=close title='Usuń notatkę' OnClick=\"listy.PD('notatka_$row[id]','funkcje/SPRAWY/notatka.juz.nieistotna.php', '$row[id]');\"><span class='glyphicon glyphicon-trash' style=''></span></button>
						$row[opis]<br>
						
						<div class=right><small>$row[operator_name], $data </small></div>
						</div>
						</div>";
				#echo "<pre>".print_r($row,1)."</pre>";
				}
			echo "</div><br/>";
			} else 
			return null;
		}
		
	public function TimeLineCollapsed($lista=array(),$naglowek='') {
		if (is_Array($lista))
			foreach ($lista as $row) {
				$lp++;
				$tmp=explode(' ',strip_tags($row[data]));
				$dzien=$tmp[0];
				$czas=substr($tmp[1],0,5);
				$LT.="
					<div class='line-time-container'>
						<div class=line-time-header data-toggle='collapse' data-target='#contact_$lp' >
							<span class=line-time-data>$dzien <small>$czas</small></span>
							<span class=line-time-point><span>&nbsp;</span></span>
							<span class=line-time-operator>$row[login]</span>
							<span class=line-time-kind>$row[czynnosc]</span>
							<span class=line-time-title><span>$row[uwagi]</span></span>
						</div>
						<div id='contact_$lp' class=collapse>
							<div class=line-time-collapsed>
								<div class='line-time-buttons'>
									<span class='btn-group'>
										<button type='button' class='close' OnClick=\"Kontrahent.DodajNotatke('{$kontrahent->dane[id]}',{id_kontaktu:'$row[id]', rodzaj_kontaktu: '$row[rodzaj_kontaktu]'});\"><span class='glyphicon glyphicon-pencil'><span></button>
										<button type='button' class='close' OnClick=\"Kontrahent.DodajNotatke('{$kontrahent->dane[id]}',{id_kontaktu:'$row[id]', akcja: 'usuń'});\"><span class='glyphicon glyphicon-trash'><span></button>
									</span>
								</div>
								<div class=line-time-point></div>
								<div class='line-time-descriptions'><span>$row[tresc]</span></div>
							</div>
						</div>
					</div>
					";
				}
		if ($naglowek<>'')
			$LT.= "<div class='line-time-descriptions'><span>$naglowek</span></div>";
					
		$CRM="<div class=line-time>$LT</div>";
		return $CRM;
		}	
		
	
	public function WyslijWiadomosc($wewnmail,$odbiorca,$temat,$tresc,$from='SEMEN',$backlink='semen@bnl.gov.pl') {
		global $OPCJE;
		$naglowek='
			<head>
			<title>GierszBOT</title>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<meta name="Author" content="gierszbot">
			<style>
			<!--
				p {Font: 12px Verdana; color:#222222; margin:10px;}	
				p.tresc {Font: 14px Verdana; color:#222222; padding:15px; background-color:#fff;}	
				ul li {Font: 12px Verdana; color:#222222;}	
				h1 { font: 18px Verdana; margin: 3px;}
				h2 { font: 15px Verdana; margin: 3px; text-weight:normal;}
				body {font: 14px Verdana; color:#222222; background-color: #eee;}
				
				-->
			</style>
			</head>
			<body>
			'.$tresc.'
			</body>';
		$subject = $temat;
		// message
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

		// Additional headers
		$headers .= 'To: ' . "\r\n";
		$headers .= "From: $from <semen@{$this->OPCJE[email]}>" . "\r\n";
		//$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
		$headers .= "Reply-to: $from <$backlink>" . "\r\n";
		# echo "<textarea>mail($wewnmail,$subject,$naglowek,$headers);</textarea>";
		
		mail($wewnmail,$subject,$naglowek,$headers);
		}			
		
	}
?>