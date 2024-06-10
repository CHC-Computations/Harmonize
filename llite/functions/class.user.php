<?php 
require_once('./functions/class.cms.php');



class user extends CMS {
	public $SERVER;
	public $cmsKey;
	public $cms;
	
	function __construct() {
		
		parent::__construct();
		
		$this->SERVER = new stdclass;
		if (!empty($_SERVER['SERVER_NAME']))
			$this->SERVER->domain = $_SERVER['SERVER_NAME'];
		if (!empty($_COOKIE['CookieAccepted']))
			setCookie('CookieAccepted', 'yes', time() + (86400 * 30), "/", $this->SERVER->domain, 0, 0 );  
		
		if (empty($_COOKIE['cmsKey']))
			setcookie('cmsKey', $this->cmsKey = $this->randStr(40), time() + (86400 * 30), "/", $this->SERVER->domain, 0, 0);
			else {
			$this->cmsKey = $_COOKIE['cmsKey'];
			}
		}

	public function register($name, $var) {
		$this->$name = $var;
		}
	
	public function setSql($sql) {
		$this->sql = $sql;
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
	
	public function logOut() {
		$this->cms->psql->query($Q = "DELETE FROM users_logged WHERE cmskey='{$this->cmsKey}';"); 
		unset($this->LoggedIn);
		}
	
	public function isLoggedIn() {
		$res = $this->cms->psql->querySelect($Q = "SELECT * FROM users_logged WHERE user_agent='$_SERVER[HTTP_USER_AGENT]' AND cmskey='{$this->cmsKey}' LIMIT 1;"); 
		if (is_array($res)) {
			$userL = current($res);
			$res = $this->cms->psql->querySelect($Q = "SELECT * FROM users WHERE id_user='$userL[id_user]' LIMIT 1;"); 
			if (is_array($res)) {
				$this->LoggedIn = current($res);
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
		#echo "<pre>".print_R($this,1)."</pre>";
		return $this->LoggedIn['username'];
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
			if (!empty($v['ico']))
				$ico = '<i class="'.$v['ico'].'"></i> ';
				else 
				$ico = '';
			$menu .= '<li><a href="'.$this->baseUrl($v['path']).'">'.$ico.$this->transEsc($v['name']).'</a></li>';	
			}
		$menu .='</ul>';	
		return $menu;
		}
	
	function adminMenu() {
		if (!empty($this->LoggedIn)) {
			$run=$this->runTime();
			
			$this->panelMenu = $this->dirToMenu('panel');
			# echo "<pre>".print_R($this->panelMenu,1)."</pre>";
			# echo "<br/><br/><br/>";
			$menu = '';
			
			foreach ($this->panelMenu as $k=>$v) {
				if (!empty($v['ico']))
					$ico = '<i class="'.$v['ico'].'"></i> ';
					else 
					$ico = '';
				
				if (!empty($v['submenu'])) {
					$menu .= '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="'.$this->baseUrl($v['path']).'">'.$ico.$this->transEsc($v['name']).'<span class="caret"></span></a>'.$this->subMenu($v['submenu']).'</li>';
					
					} else {
					$menu .= '<li><a href="'.$this->baseUrl($v['path']).'">'.$ico.$this->transEsc($v['name']).'</a></li>';
					}
				}
			
			$tresc =' 
				<nav class="navbar navbar-inverse navbar-fixed-bottom">
				  <div class="container-fluid">
					<div class="navbar-header">
					  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#footer-collapse">
						<span class="sr-only">'.$this->transEsc('User panel').'</span>
						<i class="fa fa-bars" aria-hidden="true"></i>
					  </button>
					
					  <a class="navbar-brand" href="#">'.$this->transEsc('User panel').'</a>
					</div>
					<ul class="nav navbar-nav navbar-collapse" id="footer-collapse"">
						'.$menu.'
					</ul>
					<ul class="nav navbar-nav navbar-right">
					  <li><a id="workInProgress">...</a></li>
					  <li><a>Gotowe w: <b>'.substr($run, 0, 5).'</b> sek.</a></li>
					  <li id=down_menu><a href="'.$this->baseUrl('user/logout').'"><span class="glyphicon glyphicon-off"></span> </a></li>
					  <li id=down_menu><a href="#bottom" OnClick="page.ScrollDown();"><span class="glyphicon glyphicon-chevron-down"></span> '.$this->transEsc('Bottom').'</a></li>
					  <li id=up_menu><a href="#TrescStrony" OnClick="page.ScrollUp();"><span class="glyphicon glyphicon-chevron-up"></span> '.$this->transEsc('Top').'</a></li>
					</ul>
				  </div>
				</nav> ';	
			$tresc .= "<script>
				page.ajax('workInProgress', 'service/reindexing/status')
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