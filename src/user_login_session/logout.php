<?php
if(isset($_POST["action"])) {
  //echo $_POST["action"];
  $file = session_save_path()."/sess_".$_POST["action"];
  unlink($file);
}
//$logoutID = $_POST["action"]
session_start();
session_unset();
session_destroy();
header("Location: index.php");
?>
