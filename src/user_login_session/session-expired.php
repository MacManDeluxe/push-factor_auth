<?php
namespace Phppot;

function isLoginSessionExpired() {
	if(!isset($_SESSION['max_session_duration'])) {
		$login_session_duration = 10;	//default value if not set during login
	}
	else {
		$login_session_duration = $_SESSION['max_session_duration'];
	}
	$current_time = time();
	if(isset($_SESSION['loggedin_time']) and isset($_SESSION["user_id"])){
		if(((time() - $_SESSION['loggedin_time']) > $login_session_duration)){
			return true;
		}
	}
	return false;
}
?>
