<?php

class CMS {
	
	private $configPath = './config/';
	
	// readed from config files 
	public $configJson;
	public $configIni;
	public $config;
	public $SERVER;
	public $translations;
	
	// setup in __construct
	public $redirectTo = null;
	public $start_time;
	public $time;
	public $title;
	public $theme;
	public $themePath;
	public $HOST;
	public $ignorePath;
	public $GET = [];
	public $POST = [];
	public $linkParts;
	public $params;
	public $routeParam;
	public $routePaths;
	public $router;
	public $ajaxMode;
	public $lang;
	public $userLang;
	public $defaultLanguage;
	public $facetsCode;
	public $sortCode;
	
	// side comunications
	public $errors = [];
	public $warnings = [];
	public $infos = [];
	public $success = [];
	public $JS = [];
	public $meta = [];
	
	// default sub-classes 
	public $psql;
	public $solr;
	public $user;
	public $buffer;
	public $helper;
	public $forms;
	public $maps;
	public $wikiData;
	public $wikiLibri;
	
	public $head;
	
	function __construct() {

		$this->start_time = $this->gen_www();	
		$this->time = time();
		
		$jsonFiles = glob ($this->configPath.'*.json');
		$this->configJson = new stdClass;
		foreach ($jsonFiles as $jsonFile) {
			$confName = str_replace([$this->configPath, '.json'], '', $jsonFile);
			$this->configJson->$confName = json_decode(@file_get_contents($jsonFile));
			if (empty($this->configJson->$confName)) {
				die ($confName.".json file not found or json error\n");
				}
			}
			
		$conFiles = glob ($this->configPath.'*.ini'); 
		if (is_array($conFiles))
			foreach ($conFiles as $fullFileName) {
				$confName = str_replace([$this->configPath, '.ini'], '', $fullFileName);
				$this->configIni[$confName] = parse_ini_file($fullFileName, true);
				}
				
				
		$this->title = $this->configJson->settings->www->title ?? '';
		$this->theme = $this->configJson->settings->www->theme ?? 'default';
		$this->themePath = 'themes/'.$this->theme;
		
		if (!empty($this->configJson->settings->www->host)) {
			$this->HOST = $this->configJson->settings->www->host;
			} else if (!empty($_SERVER['HTTP_HOST']))
			$this->HOST = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/';
		
		$this->ignorePath = $this->configJson->settings->www->ignorePath ?? '';
		
		$this->SERVER = new stdclass;
		$this->SERVER->domain = $_SERVER['SERVER_NAME'] ?? null;

		$this->SERVER->REQUEST_URI = $_SERVER['REQUEST_URI'] ?? '';
		
		$tRU = explode('?', $this->SERVER->REQUEST_URI);
		$REQUEST_URI = current($tRU);
		$this->linkParts = explode('/', str_replace($this->ignorePath, '', $REQUEST_URI)); 
		unset($this->linkParts[0]);
		
		$this->params = $this->linkParts;
		if (!empty($this->linkParts[2]))
			$this->router = $this->linkParts[2];
			else 
			$this->router = 'home';
		
		if (($this->router == 'ajax') or ($this->router == 'autocomplete'))
			$this->ajaxMode = true;	
			else 
			$this->ajaxMode = false;	
		
		$langGlobalDir = './languages/';
		$langFiles = glob ($langGlobalDir.'*', GLOB_ONLYDIR);
		foreach ($langFiles as $langDir) {
			$langCode = str_replace($langGlobalDir, '', $langDir);
			if ((file_exists($langDir.'/'.$langCode.'.ini'))and(file_exists($langDir.'/settings.ini'))) {
				$langSetting = parse_ini_file( $langDir.'/settings.ini' );
				$this->lang['available'][$langCode]=$langSetting['langName'];
				}
			}
			
		if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$clang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
			if (array_key_exists($clang, $this->lang['available'])) {
				$this->userLang = $clang;
				}
			} else {
			$this->userLang = $this->setting->www->userLang ?? 'en';
			}
		$this->defaultLanguage = $this->setting->www->defaultLanguage ?? 'en';
		
		
		if (!empty($this->linkParts[1]) && array_key_exists($this->linkParts[1], $this->lang['available'])) { // not in langList
			$this->userLang = $this->lang['userLang'] = $this->linkParts[1];
			include($langGlobalDir.$this->userLang.'.php');
			#$this->translations = parse_ini_file( $langGlobalDir.$this->userLang.'/'.$this->userLang.'.ini' );
			# echo "trans: <pre>".print_r($this->translations,1)."</pre>";
			} else {
			$this->redirectTo = $this->HOST.$this->userLang.'/';	
			# header( "Location: ".$this->redirectTo );
			}
		
		if (!empty($_SERVER['QUERY_STRING']))
			parse_str(urldecode($_SERVER['QUERY_STRING']), $this->GET);
			else 
			$this->GET = [];
		
		
		if (!empty($this->configJson->settings->routersOrder->{$this->router})) {
			$routerOrder = $this->configJson->settings->routersOrder->{$this->router};
			# echo "router order action:\n";
			$r = $this->router;
			# echo "$r\n";
			$key = array_search($r, $this->linkParts)+1;
			# echo "$key\n";
			# echo get_class($this)."\n";
			
			foreach ($this->linkParts as $i => $value) {
				$k = $i-$key;
				if ($k>=0) 
					$this->GET[$routerOrder[$k]] = $value;
				#echo $i.' '.$k.' '.$routerOrder[$k].' = '.$value."\n";
				}
			}
		
		if (!$this->ajaxMode) {
			$_SESSION['lang'] = $this->lang;	
			$_SESSION['GET'] = $this->GET;	
			$_SESSION['parentParams'] = $this->params;
			$_SESSION['parentRouter'] = $this->router;
			} else {
			@$this->ajaxparent = new stdClass;
			$this->ajaxparent->lang = $_SESSION['lang'];
			$this->ajaxparent->GET = $_SESSION['GET'];
			$this->ajaxparent->params = $_SESSION['parentParams'];
			$this->ajaxparent->router = $_SESSION['parentRouter'];
			}
		$this->POST = $_POST;
		
		#$this->JS[] = '$(\'[data-toggle="tooltip"]\').tooltip();';
		}
	
	

	public function mdb($o = []) {
		$dsn = 'mdsql:dbname=vufind;host=loacalhost;port=3306;charset=utf8';
		$connection = mysqli_connect($o['host'], $o['user'], $o['password'], $o['dbname']);
		$this->sql = $connection;
		}
 

	public function getMenu($parent=0) {
		$res = $this->psql->querySelect($Q="SELECT * FROM cms_posts WHERE parent_id='$parent' AND lang='{$this->userLang}' ORDER BY p_order;");
		if (is_array($res)) {
			foreach ($res as $row) {
				$Tres[$row['url']]=$row;
				}
			return $Tres;	
			}
		}

	public function getCurrentPost() {
		if (!empty($this->routeParam[0]))
			$currPage = $this->routeParam[0];
			else 
			$currPage = 'home';
		
		$res = $this->psql->querySelect($Q="SELECT * FROM cms_posts WHERE url='{$currPage}' AND lang='{$this->userLang}' ORDER BY p_order LIMIT 1;");
		if (is_array($res)) {
			return current($res);
			} else {
			return ['url'=>''];
			}
		}

	
	public function loadJsonSettings($fileName) {
		$this->configJson->$fileName = json_decode(@file_get_contents('./config/'.$fileName.'.json'));
		if (empty($this->configJson->$fileName)) {
			die($fileName.".json file not found or file error");
			}
		}
	
	
	public function getConfig($iniFile) {
		if (!empty($this->config[$iniFile]))
			return $this->config[$iniFile];
		$fullFileName = './config/'.$iniFile.'.ini';
		if (file_exists($fullFileName)) {
			$this->config[$iniFile] = parse_ini_file($fullFileName, true);
			return $this->config[$iniFile];
			} else 
			return null;
		}	
	
	public function getIniArray($file, $section=null, $param=null) {
		if (!empty($param) && !empty($section) && !empty($this->config[$file][$section][$param]) )
			$arr = $this->config[$file][$section][$param];
			else if (!empty($section) && !empty($this->config[$file][$section]) )
				$arr = $this->config[$file][$section];
				else if (!empty($this->config[$file]))
					$arr = $this->config[$file];
		if (!empty($arr))
			if (is_array($arr))
				return $arr;
				else {
				$t = explode(',',$arr);
				foreach ($t as $k=>$v)
					$t[$k]=trim(chop($v));
				return $t;
				}
		}
	
	public function getConfigParam($file, $section=null, $param=null) {
		if (!empty($this->configIni[$file]))
			$res = $this->configIni[$file];
		if (!empty($this->configIni[$file][$section]))
			$res = $this->configIni[$file][$section];
		if (!empty($this->configIni[$file][$section][$param])) 
			$res = $this->configIni[$file][$section][$param];
		if (empty($res))
			return null;
		
		if (is_string($res) && stristr($res, ',')) {
			$t = explode(',', $res);
			foreach ($t as $k=>$v)
				$t[$k] = trim($v);
			return $t;
			} else 
			return $res;
			
		}	
	
	public function getIniParam($file, $section=null, $param=null) {
		if (!empty($this->configIni[$file][$section][$param])) 
			$res = $this->configIni[$file][$section][$param];
			else if (!empty($this->configIni[$file][$section]))
					$res = $this->configIni[$file][$section];
					else if (!empty($this->configIni[$file]))
							$res = $this->configIni[$file];
							else 
							return null;
		if (is_string($res) && stristr($res, ',')) {
			$t = explode(',', $res);
			foreach ($t as $k=>$v)
				$t[$k] = trim($v);
			return $t;
			} else 
			return $res;
			
		}	
	
	public Function getParam($source, $param) {
		if (!empty($this->$source[$param]))
			return $this->$source[$param];
		if (!empty($this->$source->$param))
			return $this->$source->$param;
		return null;
		}
	
	public function postParam($param) {
		if (!empty($_POST[$param]))
			return $_POST[$param];
			else 
			return null;
		}
	

	public function getUserParamMeaning($core, $group, $param = null) {
		$value = $this->getUserParam($core.':'.$group);
		if (!empty($param)) {
			$return = $this->configJson->$core->summaryBarMenu->$group->optionsAvailable->$value->$param ?? '';
			if (!empty($return) && stristr($return, '*'))
				$return = str_replace('*', $this->userLang, $return);
			return $return;
			} else 
			return $this->configJson->$core->summaryBarMenu->$group->optionsAvailable->$value ?? null;
		}
	
	public function getUserParam($param) {
		return $_SESSION['userparams'][$param] ?? null;
		}
	
	public function saveUserParam($param, $value) {
		$_SESSION['userparams'][$param]=$value;
		}
	
	
	public function linkParts($change) {
		
		return '';
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
	
	public function register($var, $res) {
		if (empty($this->$var)) {
			$this->$var = $res;
			return true;
			} else 
			return false;
		}
	
	public function addClass($className, $res) {
		if (method_exists($res,'register'))
			$res->register('cms', $this);
		@$this->$className = $res;
		}
	
	public function addJS($script) {
		$this->JS[] = $script;
		}
	
	public function addMeta($msg) {
		$this->meta[] = $msg;
		}
	
	public function addError($msg) {
		$this->errors[] = $msg;
		}
	
	public function addWarning($msg) {
		$this->warnings[] = $msg;
		}
	
	public function addInfo($msg) {
		$this->infos[] = $msg;
		}
	
	public function addSuccess($msg) {
		$this->success[] = $msg;
		}
	
	
	public function error($error) {
		return $this->Alert('danger', $error);
		}
	
	function isMobile() {
		return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
		}
	
	
	public function phrase($templeName, $vars) {
		$userFullFileName = './languages/'.$this->userLang.'/phrases/'.$templeName;
		$defaultFullFileName = './languages/'.$this->defaultLanguage.'/phrases/'.$templeName;
		
		if (file_exists($userFullFileName)) 
			$fullFileName = $userFullFileName;
			else if (file_exists($defaultFullFileName)) 
			$fullFileName = $defaultFullFileName;
			else return 'phrase do not exist! ('.$templeName.')';
		
		extract($vars);
		include ($fullFileName);
		return $return;
		}
	
	public function transEsc($content, $array = []) {
		if (!is_string($content)) 
			return $content;
		if (!empty($content)) {
			$content = trim(str_replace('#', '', $content));
			/*
			if (($this->userLang != 'en') & !preg_match('/^Q\d+.*$/', $content)) {
				
				if (stristr($this->SERVER->REQUEST_URI, 'ajax') && !empty($this->ajaxparent->params))
					$this->SERVER->REQUEST_URI = '/'.implode('/', $this->ajaxparent->params);
				if (!empty($this->psql))
					$this->psql->querySelect("INSERT INTO translate (lang, string, context, importance) VALUES
						({$this->psql->string($this->userLang)}, {$this->psql->string($content)}, {$this->psql->string($this->SERVER->REQUEST_URI)}, 1)
						ON CONFLICT (lang, string)
						DO NOTHING;");
				}
			*/
			$translation = $this->translations[$content] ?? $content;
			if (!empty($array)) {
				foreach ($array as $key=>$value) {
					$translation = str_replace('__'.$key.'__', $value, $translation);
					}
				}
			
			return $translation;
			} 
		return $content;
		}	

	public function setTitle($title) {
		if (!empty($title)) $this->title = strip_tags($title);
		}

	public function addTitle($title) {
		$this->title .= strip_tags($title);
		}

	public function urlName($str) {
		#$str = str_ireplace(',', '', $str);
		$str = str_ireplace(' ', '', $str);
		$str = str_ireplace('.', '', $str);
		#$str = preg_replace ('/[^\p{L}\p{N}]/u', '_', $str );
		$str = urlencode($str);
		$str = str_ireplace('%2C', ',', $str);
		$str = str_ireplace('+', '_', $str);
		return $str;
		}
	
	function urlName2( $str, $replace = " " ){
        setlocale(LC_ALL, 'pl_PL.UTF8');
		$str = iconv('UTF-8', 'ASCII//TRANSLIT', $str); // TRANSLIT
        $charsArr = array( '^', "'", '"', '`', '~');
        $str = str_replace( $charsArr, '', $str );
        $return = trim(preg_replace('# +#',' ',preg_replace('/[^a-zA-Z0-9\s]/','',strtolower($str))));
        return str_replace(' ', $replace, $return);
        }		
		

	public function strToDate($strDate) {
		if ($strDate == '-9999-00-00T00:00:00Z')
			return $this->transEsc('long long time ago');
		if ($strDate == '+'.date("Y-m-d").'T00:00:00Z')
			return $this->transEsc('at present');
		$retDate = substr($strDate,1,10);
		
		if (substr($strDate, 9, 2) == '00') 
			$retDate = substr($strDate,1,7);
		
		if (substr($strDate, 6, 2) == '00') 
			$retDate = floatval(substr($strDate,1,4));
		
		if (substr($strDate,0,1) == '-')
			$retDate.=' '.$this->transEsc('BC');
		
		return $retDate;
		}	
	
	public function buildUri($uri=null, $GET=[], $addLastGET = true) {
		return $this->buildUrl($uri,$GET,$addLastGET);
		}
		
	public function clearGET() {
		$this->GET = [];
		}	
		
	public function buildUrl($uri = null, $GET = [], $addLastGET = false) {
		
		if ($addLastGET) $GET = array_merge($this->GET, $GET);
		$turi = explode('/',$uri);
		$uri1 = $turi[0];
		#print_r($GET);
		#print_r($this->GET);
		if (!empty($this->configJson->settings->routersOrder->$uri1) ) {
			$routers[$uri1] = $uri1;
			if (empty($GET['core']) && !empty($turi[1])) {
				$GET['core'] = $turi[1];
				}
			$core = $GET['core'];
			foreach ($this->configJson->settings->routersOrder->$uri1 as $field) {
				if (!empty($GET[$field])) {
					$routers[$field] = $GET[$field];
					unset($GET[$field]);
					} else if (!empty($this->getUserParam($core.':'.$field)))
					$routers[$field] = $this->getUserParam($core.':'.$field);
					else if (!empty($this->configJson->$core->summaryBarMenu->$field->default))
					$routers[$field] = $this->configJson->$core->summaryBarMenu->$field->default;
					else 
					$routers[$field] = 0;	
				}
			$uri = '/'.implode('/',$routers);	
			} 
		if (substr($uri,0,1) !== '/')
			$uri = '/'.$uri;
		$uri .='?'.http_build_query($GET);
		return $this->HOST.$this->userLang.$uri;
		}
	
		
	public function selfUrl($str1='', $str2='') {
		$scheme = $_SERVER['HTTP_X_FORWARDED_SCHEME'] ?? $_SERVER['REQUEST_SCHEME'];
		$str = $scheme.'://';
		$str .= $_SERVER['SERVER_NAME'];
		$str .= $_SERVER['REQUEST_URI'];
		
		return str_replace($str1, $str2, $str);
		}
	
	public function basicUri($uri=null) {
		if (!empty($uri) && substr($uri,0,1)!=='/')
			$uri='/'.$uri;
		
		return $this->HOST.$this->userLang.$uri;
		}

	public function baseURL($uri=null) {
		if (substr($uri,0,1)!=='/')
			$uri='/'.$uri;
		return $this->HOST.$this->userLang.$uri;
		}
	
	public function templatesExists($templeName) {
		$fullFileName = $this->themePath.'/templates/'.$templeName;
		
		if (file_exists($fullFileName)) {
			return true;
			} else 
			return false;
		}	
	
	public function renderLang($templateName, $vars = array()) {
		extract($vars);
		$templateParts = explode('.', $templateName);
		$position = count($templateParts)-1;
		$templateParts[] = end($templateParts);
		
		$fullFileName = $this->themePath.'/templates/'.$templateName;
		
		$templateParts[$position] = $this->userLang;
		$tmpName = $this->themePath.'/templates/'.implode('.', $templateParts);
		if (file_exists($tmpName)) {
			$fullFileName = $tmpName;
			} else {
			$templateParts[$position] = $this->defaultLanguage;
			$tmpName = $this->themePath.'/templates/'.implode('.', $templateParts);
			if (file_exists($tmpName))
				$fullFileName = $tmpName;
			}
		
		if (file_exists($fullFileName)) {
			ob_start();
			include ($fullFileName);
			$content = ob_get_contents();
			ob_clean();
			
			return $content;
			} else 
			return $this->error($this->transEsc('Template not found: ').$fullFileName);
		}
	
	
	public function render($templeName, $vars=array()) {
		extract($vars);
		$fullFileName = $this->themePath.'/templates/'.$templeName;
		
		if (file_exists($fullFileName)) {
			ob_start();
			include ($fullFileName);
			$content = ob_get_contents();
			ob_clean();
			
			return $content;
			} else 
			return $this->error($this->transEsc('Template not found: ').$fullFileName);
		}	
		
	public function head() {
		$this->head = new stdclass;
		$this->head->JS = '';
		$this->head->CSS = '';
		$this->head->meta = '';
		
		$js = glob ($this->themePath.'/js/*.js');
		$css = glob ($this->themePath.'/css/*.css');
		if ($this->configJson->settings->testMode) 
			$loadAgain = '?t='.$this->time;
			else 
			$loadAgain = '';
		if (count($js)>0) 
			foreach ($js as $row) {
				$this->head->JS.="\n\t\t".'<script src="'.$this->HOST.$row.$loadAgain.'"></script>';
			} 
		if (count($css)>0) 
			foreach ($css as $row) {
				$this->head->CSS.="\n\t\t".'<link rel="stylesheet" href="'.$this->HOST.$row.$loadAgain.'">';
			} 
		
		return null;
		}	
		
		
	public function content($content = null) {
		
		if (file_exists('workInProgress.txt')) {
			$work = parse_ini_file('workInProgress.txt');
			#echo "<pre>".print_r($work,1).'</pre>';
			$pauseScreen = '
					<body>
					
					<div style="display:table-cell; width:100vw; height:100vh; text-align:center; vertical-align:middle;">
					<img src="'.$this->HOST.'themes/default/images/libri_logo.svg"><br/>
					<img src="'.$this->HOST.'themes/default/images/extras/workInProgress.svg">
					<h1>Service work is in progress</h1>
					<p>Estimated completion time: <b>'.$work['finishtime'].'</b></p>
					</div>
					'.$_SERVER['REMOTE_ADDR'].' <> '.$work['ip'].'
					</body
					';
			
			if ($_SERVER['REMOTE_ADDR'] <> $work['ip'])
				return $pauseScreen;
			
			}
		include('./modules/save.post.data.php');
			
		$path='';
		$routerError='./routers/error404.php';
		
		if (is_Array($this->linkParts)) {
			$pathArray=$this->linkParts;
			unset($pathArray[1]);
			foreach ($pathArray as $routeFile) {
				$path .= "/$routeFile";
				$this->routePaths[]=$path.'.php';
				}
			if (!empty($this->routePaths) && is_array($this->routePaths)) krsort($this->routePaths);	
			}
		
		$lp=0;
		$routerFile='./routers/'.$this->router.'.php';
		#echo "<pre>".print_r($this->routePaths,1)."</pre>";
		if (!empty($this->routePaths) && is_array($this->routePaths))
			foreach ($this->routePaths as $k=>$routerFile) {
				if ($routerFile=='/.php')
					$routerFile = './routers/'.$this->router.'.php';
					else 
					$routerFile='./routers'.$routerFile;
				#echo "look for: $routerFile<Br>";
				$lp++;
				if (file_exists($routerFile)) {
					ob_start();
					
					$c = count($this->linkParts);
					$cc= $c-$lp+2;
					for ($i=$cc; $i<=$c; $i++) {
						$this->routeParam[] = $this->linkParts[$i];
						} 
					
					include($routerFile);
					$return = ob_get_contents();
					ob_clean();
					return $return;
					} else {
					$this->addInfo('router does not exists: '.$routerFile);	
					}
				}
		ob_start();
		include($routerError);
		$return = ob_get_contents();
		ob_clean();
		return $return;
		}	
		
	public function setLastPage($param) {
		$this->lastPage = $param;
		return $this->lastPage;
		}
	
	public function getLastPage($currentCore) {
		if (!empty($this->lastPage))
			return $this->lastPage;
		if (!empty($this->solr->response->numFound)) {
			$results = $this->solr->response->numFound;
			$rpp = $this->getUserParam($currentCore.':pagination');
			
			return ceil($results/$rpp);
			} else 
			return 1;
		}
		
			
		
	public function getCurrentPage() {
		if (!empty($this->GET['page'])) {
			$page = floatval($this->GET['page']);
			if ($page<=0) $page = 1;
			return $page;
			} else if (!empty($this->params[4])) {
				$page = floatval($this->params[4]); // default page position
				if ($page<=0) $page = 1;
				return $page;
				} else 
				return 1;
		}


	public function defaultSort() {
		return 1;
		}
	
	public function getCurrentSort() {
		if (!empty($this->GET['sort']))
			return $this->GET['sort'];
			else if (!empty($this->params[5])) 
				return $this->params[5]; // default sort position
				else 
				return $this->defaultSort();
			
		}


	
	public function footer() {
		if (count($this->JS)>0)
			return '<script>
$(document).ready(function(){
					'.implode(";\n",$this->JS).'
		});
</script>';
		}	
		
	public function Alert($klasa,$tresc) {
		return "
			<div class='alert alert-$klasa alert-dismissible' role='alert'><button type='button' class='close' data-dismiss='alert'><span aria-hidden='true'>&times;</span><span class='sr-only'>Zamknij</span></button>
			$tresc
			</div>
			";
		}
		

	}
	
?>