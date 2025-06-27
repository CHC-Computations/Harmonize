<?php 
$allReadyExists = [];

$t = $this->psql->querySelect("SELECT * FROM users_powers a JOIN dic_users_powers b ON a.power_id = b.id;");
if (is_Array($t))
	foreach ($t as $row) {
		$powers[$row['email']] = $row['power'];
		}

$t = $this->psql->querySelect("SELECT * FROM users_logged ORDER BY data_in DESC LIMIT 20");

if (is_Array($t)) {
	foreach ($t as $row) {
		if (($row['account_type'] == 'google') & empty($allReadyExists[$row['user_data']])){
			
			$userLogged = $this->user->googleEncode($row['user_data']);
			$userPower = $powers[$userLogged->email] ?? '';
			if (!in_array($userLogged->email, $allReadyExists)) 
				echo '<div class="panel panel-default">
						<div class="panel-body">
							<div class="row userData">
								<div class="col-sm-2 text-center">
									<img src="'.$userLogged->picture.'">
								</div>
								<div class="col-sm-4">
									<h3><b>'.$userLogged->name.'</b></h3>
									<i class="ph ph-envelope"></i> '.$userLogged->email.'<br/>
									
								</div>
								<div class="col-sm-4">
									<b>Google</b> '.$row['data_in'].'<br/>
									'.$userPower.'<br/>
								</div>
							</div>
						</div>
					</div>
					';
			$allReadyExists[$userLogged->email] = $userLogged->email;
			}
		}
	
	}




?>