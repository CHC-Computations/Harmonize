<?php
echo "Cleaning temporary values in psql";
$date = date("Y-m-d H:i",time()-86400);
$this->psql->query("DELETE FROM facets_queries WHERE time<='$date'");

$date = date("Y-m-d H:i",time()-86400*7);
$this->psql->query("DELETE FROM searchstrings WHERE lastuse<='$date'");
$this->psql->query("DELETE FROM users_params WHERE ctime<='$date'");
echo " - done";
?>