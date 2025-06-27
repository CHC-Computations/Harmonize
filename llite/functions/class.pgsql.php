<?PHP


class postgresql {

	public $host; 
	public $dbname;
	public $error;
	public $errors;
	public $counter;
	public $autocommit;
	protected $db;
	
	public function __construct($db){
		// Próbujemy się połączyć
		IF(!$this->db = @pg_connect('host='.$db['host'].' dbname='.$db['dbname'].' user='.$db['user'].' password='.$db['password'])) {
			die ('Harmonize unable to connect to PostgreSQL server!');
			$this->error = true;
			throw new Exception('Harmonize unable to connect to PostgreSQL server! '); //.pg_last_error()
			} else {
			$this->host = $db['host'];
			$this->dbname = $db['dbname'];
			}
		// połączyliśmy się, rozpoczynamy tranzakcję
		pg_query($this->db, 'BEGIN');
		$this->counter = 0;
		$this->autocommit = 50000;
		}
	
	public function nextVal($seq) {
		$t=$this->querySelect("SELECT nextval('$seq');");
		if (is_array($t)) {
			$tmp=current($t);
			return $tmp['nextval'];
			} else 
			return null;
		}
	
	public function setSequenceValue($sequence, $value) {
		$t=$this->querySelect("SELECT setval('$sequence', $value, true);");
		if (is_array($t)) {
			$tmp = current($t);
			return $tmp['setval'];
			} else 
			return null;
		}
	
	public function isNull($val) {
		if (empty($val)) return 'NULL';
		$val = chop(trim($val));
		$val = str_replace("'", '`', $val);
		if ($val=='')
			return 'NULL';
			else 
			return "'$val'";
		}
	
	public function getCount($array) {
		if (!empty($array)) {
			$res = current($array);
			return $res['count'] ?? 0; 
			}
		}
	
	public function json($data) {
		if (empty($data)) return 'NULL';
		
		// Jeśli to tablica lub obiekt – konwertujemy na JSON
		if (is_array($data) || is_object($data)) {
			$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			} else {
			$json = trim($data);
			}

		if (!empty($json)) {
			$b64 = base64_encode($json);
			// Zwracamy jako tekst i rzutujemy na JSON
			return "decode('$b64', 'base64')::json";
			} else {
			return 'NULL';
			}
		}
	
	
	public function string($string) {
		if (empty($string)) return 'NULL';
		$val = trim($string);
		if (!empty($val)) {
			$b64 = base64_encode($val);		
			return "convert_from(decode('$b64', 'base64'), 'UTF8') ";
			} else 
			return 'NULL';
		}
	
	public function real($value) {
		$floatval = floatval($value); 
		return "'$floatval'";
		}
	
	public function integer($value) {
		$floatval = intval($value); 
		return "'$floatval'";
		}
	
	public function createInsertQuery($table, $data) {
		foreach ($data as $field => $value) {
			$tfields[$field] = $field;
			$vtype = key($value);
			$inputValue = current($value);
			switch ($vtype) {
				case 'text' : 
				case 'name' : $tvalues[$field] = $this->string($inputValue); break;
				case 'real' : $tvalues[$field] = $this->real($inputValue); break;
				case 'integer' : $tvalues[$field] = $this->integer($inputValue); break;
				default : $tvalues[$field] = $this->isNull($inputValue); break;
				}
			}
		$query = "INSERT INTO $table (".implode(', ', $tfields).") VALUES (".implode(', ',$tvalues).");";
		return $query;
		}
	
	public function busy() {
		$q = "SELECT count(*) AS busy
			FROM pg_stat_activity
			WHERE state != 'idle'
			  AND pid != pg_backend_pid()";
		$t = $this->querySelect($q);
		if (is_array($t))
			return current($t)['busy'];
		}
	
	public function query($query) {
		If (!stristr('SELECT', $query) and !$this->error) {
			$this->counter++;
			If (!$result = pg_query($this->db, $query)) {
				#$this->error = true;
				throw new Exception('Błąd wykonania zapytania - ('.$query.') - '.pg_last_error());
				return false;
				} else {
				if ($this->counter == $this->autocommit) {
					pg_query($this->db, 'COMMIT');
					$this->counter = 0;
					}
				return true;
				}
			} else {
			return false;
			}
		}
	
	
	public function querySelect($query) {
		$return = null;
		if (stristr('INSERT',$query))
			$this->counter++;
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
	
	
	public function queryObject($query, $idField = '') {
		$return = null;
		If (!$this->error) {
			IF(!$result = pg_query($this->db, $query)) {
				$this->error = true;
				throw new Exception('Błąd wykonania zapytania - ('.$query.') - '.pg_last_error());
				}
			while($row = pg_fetch_object($result)) {
				if (!empty($idField))
					$return[$row->$idField] = $row;
					else 
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
	
	


?>
