<?PHP


class postgresql {

	public $host; 
	public $dbname;
	public $error;
	protected $db;
	
	public function __construct($db){
		// Próbujemy się połączyć
		IF(!$this->db = pg_connect('host='.$db['host'].' dbname='.$db['dbname'].' user='.$db['user'].' password='.$db['password']))
			{
			$this->error = true;
			throw new Exception('Błąd Połączenia z Bazą Danych - '); //.pg_last_error()
			} else {
			$this->host = $db['host'];
			$this->dbname = $db['dbname'];
			}
		// połączyliśmy się, rozpoczynamy tranzakcję
		pg_query($this->db, 'BEGIN');
		# $this->logger->ZapiszStan('BEGIN','');
		}
	
	public function nextVal($seq) {
		$t=$this->querySelect("SELECT nextval('$seq');");
		if (is_array($t)) {
			$tmp=current($t);
			return $tmp['nextval'];
			} else 
			return null;
		}
	
	public function isNull($val) {
		$val = chop(trim($val));
		$val = str_replace("'", '`', $val);
		if ($val=='')
			return 'NULL';
			else 
			return "'$val'";
		}
	
	public function query($query) {
		If (!stristr('SELECT', $query) and !$this->error) {
			If (!$result = pg_query($this->db, $query)) {
				#$this->error = true;
				$this->errors[] = 'Błąd wykonania zapytania - ('.$query.') - '.pg_last_error();
				return false;
				} else {
				return true;
				}
			} else {
			return false;
			}
		}
	
	
	public function querySelect($query) {
		$return = null;
		If (!$this->error) {
			IF(!$result = pg_query($this->db, $query)) {
				$this->error = true;
				throw new Exception('Błąd wykonania zapytania - ('.$query.') - '.pg_last_error());
				}
			while($row = pg_fetch_array($result, null, PGSQL_ASSOC)) {
				$return[] = $row;
				}
			unset($result);
			unset($row);
			return $return;
			}
		}
	
	public function escape($string)	{
		return  pg_escape_string($string);
		}
		
	function __destruct() {
		If(!$this->error) {
			pg_query($this->db, 'COMMIT');
			} else {
			pg_query($this->db, 'ROLLBACK');
			}
		unset($this->db);
		unset($this->error);
		}

	
	}
	
	
// PRZYKŁADOWE ZASTOSOWANIE
#$pgs->query("CREATE TABLE rk_pages ( page_id SERIAL, page_title varchar(255) default NULL,  page_desc text,  page_content text,  PRIMARY KEY  (page_id))");
#$pgs->query("INSERT INTO rk_pages (page_title, page_desc, page_content) VALUES ('tytuł1', 'opis1', 'treść artykułu1')");
#$pgs->query("INSERT INTO rk_pages (page_title, page_desc, page_content) VALUES ('tytuł2', 'opis2', 'treść artykułu2')");

?>
