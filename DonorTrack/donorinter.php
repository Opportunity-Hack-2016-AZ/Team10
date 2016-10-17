<?php
require_once('cxa/php/session.php');
boot_user(2);

function refreshDonorList(){
	$_SESSION["donorlist"] = "pending";
	$newDonorList = Array();
	global $pypath, $fbm_user, $fbm_pass;
	$pyInter = shell_exec("$pypath \"../FBM Utility/FoodBankManager.py\" \"donors\" \"$fbm_user\" \"$fbm_pass\"");
	$interList = json_decode(preg_replace('/,\s*([\]}])/m', '$1', "{\"donors\":[".substr($pyInter,0,-7)."]}"), true)["donors"];
	error_log(json_encode($interList));
	foreach($interList as $interDonor){
		$newDonorID = intval($interDonor["Donor ID"]);
		$newDonorList[$newDonorID] = Array();
		$newDonorList[$newDonorID]["firstname"] = $interDonor["First Name"];
		$newDonorList[$newDonorID]["lastname"] = $interDonor["Last Name"];
		$newDonorList[$newDonorID]["email"] = $interDonor["Email Address"];
	}
	$_SESSION["donorlist_timestamp"] = time();
	$_SESSION["donorlist"] = $newDonorList;	
}

function nextDonorID(){
	return max(array_keys($_SESSION["donorlist"]))+1;
}

function addDonor($fields){
	$params = Array("first", "last", "email", "street", "town", "state", "zip");
	$json_inter = Array();
	foreach($params as $param){
		if(!empty($fields[$param])){
			$json_inter[$param]=escapeshellcmd($fields[$param]);
		}
	}
	$json = json_encode($json_inter);
	return shell_exec("$pypath \"../FBM Utility/FoodBankManager.py\" \"add_donor\" \"$fbm_user\" \"$fbm_pass\" \"$json\"");
	
}

if(empty($_SESSION["donorlist"]) || (!empty($_SESSION["donorlist_timestamp"]) && $_SESSION["donorlist_timestamp"]+3600<time())){
	refreshDonorList();
}elseif($_SESSION["donorlist"]=="pending"){
	while($_SESSION["donorlist"]=="pending"){
		sleep(0.1);
	}
}
?>