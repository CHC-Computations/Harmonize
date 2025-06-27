<?php 
if (empty($this)) die;

require_once('functions/class.helper.php');
require_once('functions/class.forms.php');
require_once('functions/class.converter.php');
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.bookcart.php');

$this->addClass('buffer', 	new buffer($this)); 
$this->addClass('solr', 	new solr($this)); 
$this->addClass('helper', 	new helper($this)); 
$this->addClass('forms', 	new forms($this)); 
$this->addClass('convert', 	new converter($this));
$this->addClass('bookcart',	new bookcart());




	$statsFolder = './_tools/awstats_data/';

	$fileLists = glob ($statsFolder.'*.txt');
	$sizeMax = 0;
	foreach ($fileLists as $fileName) {
		$statFile = str_replace( [$statsFolder.'awstats' , '.txt'] , '', $fileName);
		$statMonth = substr($statFile, 0, 2);
		$statYear = substr($statFile, 2, 4);
		$statSize = filesize($fileName);
		$sizeMax = max($sizeMax, $statSize);
		$statFiles[$statYear.'.'.$statMonth] = $statSize;
			
		}
	ksort($statFiles);
	$dataArray = ['statFiles'=>$statFiles, 'sizeMax' =>$sizeMax];
	
	if (!empty($this->routeParam[0]) && !empty($this->routeParam[1])) {
		$p = new AWStats($this->routeParam[1], $this->routeParam[0], null ,$statsFolder, $this->routeParam[2] ?? '');
		$dataArray['p'] = $p;
		}
	

	class AWStats {
		private $fh = false;
		public $lastError = false;
		public $data = array();
		public $sectionName = '';

		function __construct($month,$year,$domain,$path='/tmp/awstats/', $sectionName = '') {
			$this->sectionName = $sectionName;
			if (!empty($domain))
				$filename = $path.'awstats'.$month.$year.'.'.$domain.'.txt';
				else
				$filename = $path.'awstats'.$month.$year.'.txt';
			if(!file_exists($filename)) {
				$this->lastError = 'File does not exist.';
				return false;
			}

			$this->fh = fopen($filename,'r');
			if($this->fh === false) {
				$this->lastError = 'File cannot be opened.';
				return false;
			}

			$this->parse();
		}

		/* Checks if line is a comment */
		private function comment($line) {
			if(isset($line[0]) && $line[0] == '#') {
				return true;
			}
			return false;
		}

		/* Builds an array based on a section */
		private function section() {
			$in_section = false;
			$section_name = '';
			$section_lines = 0;
			$on_line = 0;
			$section_content = array();

			if($this->fh === false) {
				return false;
			}

			while(($line = fgets($this->fh)) !== false) {
				$line = trim($line);
				if($this->comment($line)) {
					continue;
				}

				if($in_section) {
					if(strpos($line,'END_'.$section_name) === 0) {
						return array(
							'name' => $section_name,
							'lines' => $section_lines,
							'content' => $section_content
						);
					}else if($on_line <= $section_lines) {
						if ($section_name == $this->sectionName)
							array_push($section_content,$line);
						$on_line++;
						continue;
					}else {
						$this->lastError = 'Section Can Not Find Ending';
						return false;
					}
				}

				if(strpos($line,'BEGIN_') === 0) {
					$in_section = true;
					$section_info = explode(' ',$line);
					$section_name = substr($section_info[0],6);
					$section_lines = $section_info[1];
					$on_line = 0;
					$section_content = array();
					continue;
				}
			}
			return false;
		}

		/* Parses the sections array and uses that data for whatever it needs it for */
		private function parse() {
			if($this->fh === false) {
				return false;
			}

			while($section = $this->section()) {
				/*
					Here you would place extra parsing code based on what you want
					to do with the data. But since this is only an example, the
					data is placed into an array with just the section name and
					the data for each line (untouched). Will have to split by [space]
				*/
				array_push($this->data,$section);


				/* You can add specific rules based on the section here */
				switch($section['name']) {
					case 'GENERAL':

						break;
					case 'ROBOT':

						break;
					/* Add the rest of the section cases */
				}
			}
		}
	}





?> 

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<?= $this->render('cms/awstats/review.php', $dataArray) ?> 
<?= $this->render('helpers/report.error.php') ?> 
<?= $this->render('core/footer.php') ?>
