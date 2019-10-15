<?php
//POST request to Cancel an active session
if(isset($_POST["action"]) and isset($_POST["authCode"])) {
  $file = session_save_path()."/sess_".$_POST["action"];
  $contents = file_get_contents($file); //returns false if file not found
  if($contents != false) {
    session_start();
    session_decode($contents); //read $_SESSION vars into current session
    if($_POST["authCode"] == $_SESSION["cancelCode"]) {
      unlink($file);
    }
  }
}
//called by Logout button from dashboard.php
session_start();
session_unset();
session_destroy();
header("Location: index.php");
?>
