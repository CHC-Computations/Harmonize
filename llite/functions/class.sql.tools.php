<?php


class sqlTools {
	
	function where ($arr, $arr2=[]){
		$arr = array_merge($arr,$arr2);
		if (count($arr)>0)
			return "WHERE ".implode(' AND ',$arr);
			else 
			return '';
		}
	}

?>