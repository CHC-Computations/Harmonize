<?php
// dodaj nagłowki!


$time = date("Y-m-d H:i:s", time()-86400*2);
$this->sql->query("DELETE FROM libri_users_params WHERE ctime<'$time';");

?>