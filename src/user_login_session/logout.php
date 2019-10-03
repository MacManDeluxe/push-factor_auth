<?php
if(isset($_GET["action"])) {
  echo $_GET["action"];
  //$file = session_save_path()."sess_".session_id();
  $file = session_save_path()."/sess_".$_GET["action"];
  unlink($file);
}
session_start();
session_unset();
session_destroy();
header("Location: index.php");
?>
