<?php
session_start();
//include(__DIR__ . "/session_expired.php");
if(!empty($_SESSION["userId"]) and !isLoginSessionExpired()) {
    require_once './view/dashboard.php';
} else {
    require_once './view/login-form.php';
}

function isLoginSessionExpired() {
	if(empty($_SESSION['max_session_duration'])) {
		$_SESSION['max_session_duration'] = 10;	//default value if not set during login
	}
	$login_session_duration = $_SESSION['max_session_duration'];

	$current_time = time();
		if((time() - $_SESSION['loggedin_time']) > $login_session_duration){
      session_start();
      session_unset();
      session_destroy();
      header("Location: index.php");
      return true;
		}
	else{return false;}
}
?>
