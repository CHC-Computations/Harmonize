<?php
require_once('functions/class.helper.php');
$this->addClass('helper', new helper());

class acimp {
	
	function __construct($sql) {
		$this->LP = 0;
		$this->sql = $sql;
		}
	
	function removeLastSlash($t1) {
		$t2 = '';
		$t1 = (string)$t1;
		$pos = strrpos($t1,'/');
		
		if ($pos>=strlen($t1)-3)
			return substr($t1, 0, $pos);
			else 
			return $t1;
		}
	
	function clearVal($val) {
		$val = str_replace('\\','',$val);
		if ($val<>'')
			return pg_escape_string($val);
			else 
			return '';
		}
	
	function isNull($val) {
		$val=chop(trim($val));
		$val=str_replace("'",'',$val);
		if ($val=='')
			return 'NULL';
			else 
			return "'".pg_escape_string($val)."'";
		}
		
	
	function getStrId($str) {
		$bstr=base64_encode($str);
		$md5 = md5($str);
		$res = $this->sql->query($Q="SELECT id_str FROM libri_strings WHERE csum='$md5';");
		if ($res->num_rows>0) {
			$row = mysqli_fetch_assoc($res);
			return $row['id_str'];
			} else {
			$res = $this->sql->query("SELECT id_str FROM libri_strings ORDER BY id_str DESC LIMIT 1");
			if ($res->num_rows>0) {
				$row = mysqli_fetch_assoc($res);
				$id_str = $row['id_str']+1;
				$this->sql->query($Q="INSERT INTO libri_strings (id_str, csum, string) VALUES ('$id_str', '$md5', FROM_BASE64('$bstr'));");
				return $id_str;
				} else {
				$id_str = 1;
				$this->sql->query($Q="INSERT INTO libri_strings (id_str, csum, string) VALUES ('$id_str', '$md5', FROM_BASE64('$bstr'));");
				return $id_str;
				}
			}
		}
		
}



$imp = new acimp($this->sql);
	
$step = 1000;	
$curr_step = $this->routeParam[0];


$file = file_get_contents('http://147.231.80.162:8983/solr/biblio/select?fl=title%2C%20author%2C%20genre%2C%20topic%2C%20info_resource_str_mv%2C%20article_resource_txt_mv%2C%20year_str_mv&q.op=OR&q=*%3A*&rows=10&start=0');
$json = json_decode($file);
$numFound = $json->response->numFound;

$start_point = $numFound - ($numFound % $step);
$curr_start_point = $start_point-($step*$curr_step);
$steps = $start_point/$step;

$file = file_get_contents('http://147.231.80.162:8983/solr/biblio/select?fl=title%2C%20author%2C%20genre%2C%20topic%2C%20info_resource_str_mv%2C%20article_resource_txt_mv%2C%20year_str_mv&q.op=OR&q=*%3A*&rows='.$step.'&start=0'.$curr_start_point);
$json = json_decode($file);


if ($curr_start_point>=0) {
	echo $this->helper->percent($curr_step, $steps);
	echo "start point: <b>$start_point</b><br/>";
	echo "step: <b>$step</b><br/>";
	echo "all steps: <b>$steps</b><br/>";
	echo "curr_step: <b>$curr_step</b><br/>";
	echo "curr_start_point: <b>$curr_start_point</b><br/>";
	echo "<div id='wainting'></div>";
	echo "<hr>";
	$recs = $json->response->docs;

	foreach ($recs as $rec) {
		
		foreach ($rec as $indeks_name=>$srecs) {
			if (!is_Array($srecs))
				$srecs = [$srecs];
			foreach ($srecs as $value) {
				$value = $imp->removeLastSlash($value);
				$id_str = $imp->getStrId($value);
				$res = $this->sql->query("SELECT * FROM libri_ac_indeks WHERE id_str='$id_str' AND indeks_name='$indeks_name';");
				if ($res->num_rows==0) {
					$this->sql->query("INSERT INTO libri_ac_indeks (id_str, indeks_name, meter) VALUES ({$imp->isNull($id_str)}, {$imp->isNull($indeks_name)}, 1); ");
					echo "adding: ";
					} else {
					echo "exist: ";
					$row = mysqli_fetch_assoc($res);
					$nc = $row['meter']+1;
					echo "exist ($nc): ";
					$this->sql->query("UPDATE libri_ac_indeks SET meter='$nc' WHERE id_str='$id_str' AND indeks_name='$indeks_name';");
					}
				echo "$indeks_name: $id_str: <b>$value</b> <br/>";
				}
			
			}
		}

	$next=$curr_step+1;
	echo "Next step: 
	<script>
		importer.acIndeks($next);
		$('#wainting').html('<div class=\"text-center\"><div class=\"lds-ellipsis\"><div></div><div></div><div></div><div></div></div></div>');
	</script>
	";
		
	if ($curr_start_point>0) {
		// nothing 
		}
	} else {
	echo "All done";	
	}

?>





