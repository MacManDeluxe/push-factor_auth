<?php
namespace Phppot;

use \Phppot\Member;

print_r("anything");
if (isset($_POST["login"])) {

    session_start();

    $username = filter_var($_POST["user_name"], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);

    require_once (__DIR__ . "/class/Member.php");

    $member = new Member();

    $isLoggedIn = $member->processLogin($username, $password);
    if (! $isLoggedIn) {
        $_SESSION["errorMessage"] = "Invalid Credentials";
        echo "$_SESSION[errorMessage]";
    }
    if($isLoggedIn) {
      $_SESSION["errorMessage"] = "Valid Credentials";
      echo "$_SESSION[errorMessage]";
    }
    header("Location: ./index.php");
    exit();
}
?>
