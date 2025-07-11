<?php 
require_once('./functions/class.cms.php');


class user {
	public $SERVER;
	public $cmsKey;
	public $cms;
	public $lang; 
	
	private $panelMenu;
	private $extraMenu = '';
	private $loggedIn;
	private $powersTable;
	
	private $userAgentId = '';
	
	function __construct() {
		
		$this->SERVER = new stdclass;
		if (!empty($_SERVER['SERVER_NAME']))
			$this->SERVER->domain = $_SERVER['SERVER_NAME'];
		if (!empty($_COOKIE['CookieAccepted'])) {
			setCookie('CookieAccepted', 'yes', [
						"expires" => time() + (86400 * 30), 
						"path" => "/", 
						"domain" =>  $this->SERVER->domain, 
						"secure" => true, 
						"httponly" => true, 
						"samesite" => "Lax" 
						]);
			}
		
		if (empty($_COOKIE['cmsKey']))
			#setcookie('cmsKey', $this->cmsKey = $this->randStr(40), time() + (86400 * 30), "/", $this->SERVER->domain, 0, 0);
			setcookie('cmsKey', $this->cmsKey = $this->randStr(40), 
						[
						"expires" => time() + (86400 * 30), 
						"path" => "/", 
						"domain" =>  $this->SERVER->domain ?? 'localhost', 
						"secure" => true, 
						"httponly" => true, 
						"samesite" => "Lax" 
						]);
		
		
			else {
			$this->cmsKey = $_COOKIE['cmsKey'];
			}
		}

	public function register($name, $var) {
		$this->$name = $var;
		if (!empty($this->cms->lang))
			$this->lang = $this->cms->lang;
		}
	
	public function randStr($len) {
		#$len=rand($len-10,$len+10);
		$rstr = '';
		for($i=0;$i<$len;$i++) {
			$p = rand(0,1);
			switch($p) {
				case(0): $rstr .= chr(rand(ord('A'),ord('Z')));break;
				case(1): $rstr .= chr(rand(ord('a'),ord('z')));break;
				}
			}
		return $rstr;
		}
		
	public function getUserAgentId() {
		if (!empty($this->userAgentId)) 
			return $this->userAgentId;
		$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
		if ($user_agent !== '')
		$t = $this->cms->psql->querySelect("SELECT id FROM user_agents WHERE user_agent = {$this->cms->psql->string($user_agent)};");		
		if (is_Array($t)) {
			$this->userAgentId = current($t)['id'];
			return $this->userAgentId;
			} else {
			$id = $this->userAgentId = $this->cms->psql->nextVal('user_agents_id_seq');	
			$this->cms->psql->query("INSERT INTO user_agents (id, user_agent) VALUES ('$id', {$this->cms->psql->string($user_agent)});");			
			return $this->userAgentId;
			}
		}
	
	public function newGoogleLogin() {
		if (!empty($this->cms->POST['credential']) && !empty($this->cms->POST['g_csrf_token'])) {
			$credential = $this->cms->POST['credential'];
			$token = $this->cms->POST['g_csrf_token'];
			$datagiven = $this->googleEncode($credential);
			$dataverify = json_decode(@file_get_contents('https://oauth2.googleapis.com/tokeninfo?id_token='.$credential));
			
			$dataToSave = base64_encode(json_encode($dataverify));
			#echo $this->helper->pre($dataverify);
			$_SESSION['googleuser'] = $dataverify;
			
			$this->cms->psql->query($Q = "INSERT INTO users_logged (id, data_in, user_agent, cmskey, account_type, user_data) 
				VALUES ('{$this->cms->psql->nextVal('users_id_user_seq')}', now(), {$this->cms->psql->isNull($_SERVER['HTTP_USER_AGENT'])}, '{$this->cmsKey}', 'google', '$credential');");
			return true;
			}
		return false;	
		}
		
	public function googleEncode($credential) {
		return json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $credential)[1]))));
		}	
	
	public function logOut() {
		$this->cms->psql->query($Q = "DELETE FROM users_logged WHERE cmskey='{$this->cmsKey}';"); 
		$this->loggedIn = new stdClass;
		#unset($this->loggedIn);
		}
	
	public function isLoggedIn() {
		
		if (!empty($this->loggedIn->name))
			return true;
			else {
			#echo 'working ... ';
			$this->loggedIn = new stdclass;
					
			$res = $this->cms->psql->querySelect($Q = "SELECT * FROM users_logged WHERE user_agent='$_SERVER[HTTP_USER_AGENT]' AND cmskey='{$this->cmsKey}' LIMIT 1;"); 
			if (is_array($res)) {
				#echo 'we have user!';
				$userL = current($res);
				if ($userL['account_type'] == 'google') {
					$userData = $this->googleEncode($userL['user_data']);
					$this->loggedIn->name = $userData->name;
					$this->loggedIn->given_name = $userData->given_name;
					$this->loggedIn->family_name = $userData->family_name;
					$this->loggedIn->email = $userData->email;
					$this->loggedIn->email_verified = $userData->email_verified;
					$this->loggedIn->picture = $userData->picture;
					$this->loggedIn->accountType = 'google';
					}
				
				if (!empty($this->loggedIn->email) && !empty($this->loggedIn->email_verified) && ($this->loggedIn->email_verified == '1')) {
					$res = $this->cms->psql->querySelect($Q = "SELECT a.*,b.power FROM users_powers a LEFT JOIN dic_users_powers b ON a.power_id=b.id WHERE email='{$this->loggedIn->email}';"); 
					if (is_array($res)) {
						foreach ($res as $power) {
							$this->loggedIn->powers[$power['power_id']] = $power['power'];
							}
						if (empty($this->powersTable)) {
							$t = $this->cms->psql->querySelect("SELECT * FROM dic_users_powers;");
							if (is_array($t))
								foreach ($t as $row) {
									$this->powersTable[$row['id']] = $row['power'];
									$this->powersTable[$row['power']] = $row['id'];
									if ($power['power_id'] >= $row['id'])
										$this->loggedIn->powers[$row['id']] = $row['power'];
									}
							}
						
						
						}
					
					}
					
				/*
				$res = $this->cms->psql->querySelect($Q = "SELECT * FROM users WHERE id_user='$userL[id_user]' LIMIT 1;"); 
				if (is_array($res)) {
					$this->loggedIn = current($res);
					return true;
					}
				*/	
				if (!empty($this->loggedIn->name))
					return true;
				}
			}
		return false;
		}
	
	
	function pHash($pass) {
		return password_hash($pass, PASSWORD_BCRYPT,  [ 'cost' => 8] );
		}
	
	function pVerify($pass, $hash) {
		return password_verify($pass, $hash);
		}
	
	 
	function getUserName() {
		return $this->loggedIn->name;
		}
		
	function getPicture() {
		if (!empty($this->loggedIn->picture))
			return $this->loggedIn->picture;
		return '';
		}
	
	function full() {
		if (!empty($this->loggedIn))
			return $this->loggedIn;
		return '';
		}
	
	function hasPower($level) {
		if (!empty($this->loggedIn->powers))
			foreach ($this->loggedIn->powers as $key=>$name) {
				#echo "$key, $name =?= $level<Br/>";
				if (($key >= $level) OR ($name == $level))
					return true;
				}
		return false;
		}
	
	function saveUser($d) {
		$cpass = $this->pHash($d['password']);
		$vcode = $this->randStr(6);
		$this->cms->psql->query("INSERT INTO users (username, email, password, cdate, vcode, status) VALUES ('$d[username]', '$d[email]', '$cpass', now(), '$vcode', 0); ");
		return $vcode;
		}
	
	function checkLogIn($d = [], &$alerts = []) {
		
		if (empty($d['code'])) {
			$alerts[] = "Token is empty!";
			return false;
			}
		
		if (!empty($d['code']) && ($d['code']<>$this->cmsKey) ) {
			$alerts[] = "Token is broken";
			return false;
			}
		
		if (empty($d['login'])) {
			$alerts[] = "Loggin empty!";
			return false;
			}
		if (empty($d['pass'])) {
			$alerts[] = "Password empty!";
			return false;
			}
			
		
		$res = $this->cms->psql->querySelect($Q = "SELECT * FROM users WHERE email='$d[login]' LIMIT 1;"); 
		if (is_Array($res)) {
			$user = current($res);
			if ($this->pVerify($d['pass'], $user['password'])) {
				$this->cms->psql->query("
						INSERT INTO  users_logged (id_user, data_in, user_agent, cmskey) 
						VALUES ('$user[id_user]', now(), '$_SERVER[HTTP_USER_AGENT]', '{$this->cmsKey}');
						"); 
				$this->user = $user;
				return true;
				} else {
				$alerts[] = "Incorrect login or password.";	
				return false;
				}
			} else {
			$alerts[] = "User account doesn't exist";	
			return false;
			}
		$alerts[] = "Something unexpected happened!";	
		return false;	
		}
	
	function checkUserName($userName, &$alert) {
		$res = $this->cms->psql->querySelect($Q="SELECT * FROM users WHERE username='$userName' LIMIT 1;");
		if (is_array($res)) {
			$alert[] = "A user with this name already exists. Make up a different one.";
			return false;	
			} else 
			return true;
		}
	
	function checkEMail($email, &$alert) {
		if (!stristr($email, '@')) {
			$alert[] = "This e-mail seems to be incorrect.";
			return false;
			}
		if (!stristr($email, '.')) {
			$alert[] = "This e-mail seems to be incorrect.";
			return false;
			}
		
		$res = $this->cms->psql->querySelect($Q="SELECT * FROM users WHERE email='$email' LIMIT 1;");
		if (is_array($res)) {
			$alert[] = "There is already an account assigned to this email. Please try to log in. If you have forgotten your password, please use the password recovery function.";
			return false;	
			} else 
			return true;
		}
	
		
	function dirToMenu($folder) {
		$LP = 0;
		$path = './routers/'.$folder.'/*/content.ini';
		$glob = glob ($path);
		foreach ($glob as $cfile) {
			$nf = str_replace('./routers/', '', $cfile);
			$nf = str_replace('/content.ini', '', $nf);
			$LP++;
			$menu[$LP] = parse_ini_file($cfile, true);
			$menu[$LP]['path'] = $nf;
			$sm = $this->dirToMenu($nf);
			if (is_array($sm))
				$menu[$LP]['submenu'] = $sm;
			}
		if (!empty($menu))	
			return $menu;
		}
	
	
	function subMenu($arr = []) {
		$menu = '<ul class="dropdown-menu">';
		foreach ($arr as $k=>$v) {
			if (!empty($v['divider']) && ($v['divider']=='before'))
				$menu .= '<li class="divider"></li>';	
			if (!empty($v['ico']))
				$ico = '<i class="'.$v['ico'].'"></i> ';
				else 
				$ico = '';
			$menu .= '<li><a href="'.$this->cms->baseUrl($v['path']).'">'.$ico.$this->cms->transEsc($v['name']).'</a></li>';	
			if (!empty($v['divider']) && ($v['divider']=='after'))
				$menu .= '<li class="divider"></li>';	
			}
		$menu .='</ul>';	
		return $menu;
		}
	
	
	function addToMenu($content) {
		$this->extraMenu .= $content;
		}
	
	function adminMenu() {
		if (!empty($this->loggedIn)) {
			$run = $this->cms->runTime();
			
			$this->panelMenu = $this->dirToMenu('panel');
			# echo "<pre>".print_R($this->panelMenu,1)."</pre>";
			# echo "<br/><br/><br/>";
			$menu = '';
			
			foreach ($this->panelMenu as $k=>$v) {
				if (!empty($v['ico']))
					$ico = '<i class="'.$v['ico'].'"></i> ';
					else 
					$ico = '';
				$link = '';
				if (!empty($v['path'])) {
					$link .= ' href="'.$this->cms->baseUrl($v['path']).'"';
					} 
				if (!empty($v['onClick'])) {
					$link .= ' onClick="'.$v['onClick'].'"';
					} 
				
				if (!empty($v['submenu'])) {
					$menu .= '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" '.$link.'>'.$ico.$this->cms->transEsc($v['name']).'<span class="caret"></span></a>'.$this->subMenu($v['submenu']).'</li>';
					} else {
					$menu .= '<li><a '.$link.'>'.$ico.$this->cms->transEsc($v['name']).'</a></li>';
					}
				}
			
			$tresc =' 
				<nav class="navbar navbar-inverse navbar-fixed-bottom">
				  <div class="container-fluid">
					<div class="navbar-header">
					  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#footer-collapse">
						<span class="sr-only">'.$this->cms->transEsc('User panel').'</span>
						<i class="fa fa-bars" aria-hidden="true"></i>
					  </button>
					
					  <a class="navbar-brand" href="#">'.$this->cms->transEsc('User panel').'</a>
					</div>
					<ul class="nav navbar-nav navbar-collapse" id="footer-collapse"">
						'.$menu.'
					</ul>
					'.$this->extraMenu.'
					<ul class="nav navbar-nav navbar-right">
					  <li><a id="ajaxActive" title="ajax active"></a></li>
					  <li class="dropdown" id="workInProgress"><a>...</a></li>
					  <li><a id="constInfoCloud" title="screen width"></a></li>
					  <li><a>'.$this->cms->transEsc('Ready in').': <b>'.substr($run, 0, 5).'</b> sek.</a></li>
					  <li id=down_menu><a href="'.$this->cms->baseUrl('user/logout').'"><span class="glyphicon glyphicon-off"></span> </a></li>
					  <li id=down_menu><a href="#bottom" OnClick="page.ScrollDown();"><span class="glyphicon glyphicon-chevron-down"></span> '.$this->cms->transEsc('Bottom').'</a></li>
					  <li id=up_menu><a href="#TrescStrony" OnClick="page.ScrollUp();"><span class="glyphicon glyphicon-chevron-up"></span> '.$this->cms->transEsc('Top').'</a></li>
					</ul>
				  </div>
				</nav> ';	
			$tresc .= "<script>
				page.ajax('workInProgress', 'service/reindexing/status');
				$(document).ready(function() {
					function updateAjaxCount() {
						$('#ajaxActive').text($.active);
						}
					setInterval(updateAjaxCount, 500);
					updateAjaxCount();
				});
				</script>";	
			return $tresc;
			} else 
			return $this->cms->render('core/footer-controls.php');
		}	
				
	public function loadParam($name) {
		$res = $this->cms->psql->querySelect($Q="SELECT value FROM users_params WHERE session_id='{$this->cmsKey}' AND name='$name' LIMIT 1;");
		if (is_array($res)) {
			$row = current($res);
			return $row['value'];
			} else 
			return null;
		}
	
	public function saveParam($name, $value) {
		if (is_array($value)) 
			return 'array';
		$res = $this->cms->psql->querySelect($Q="SELECT value FROM users_params WHERE session_id='{$this->cmsKey}' AND name='$name' LIMIT 1;");
		if (is_array($res))
			$this->cms->psql->query($Q="UPDATE users_params SET value='$value' WHERE (session_id='{$this->cmsKey}' AND name='$name');");
			else 
			$this->cms->psql->query($Q="INSERT INTO users_params (session_id, name, value) VALUES ('{$this->cmsKey}', '$name', '$value');");
		return $Q;
		
		}
	
	
	}
	
?>