<?php
//POST request to Cancel an active session
if(isset($_POST["action"])) {
  $file = session_save_path()."/sess_".$_POST["action"];
  unlink($file);
}
//called by Logout button from dashboard.php
session_start();
session_unset();
session_destroy();
header("Location: index.php");
?>
