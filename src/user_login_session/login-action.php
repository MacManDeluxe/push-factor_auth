<?php
namespace Phppot;

use \Phppot\Member;
$_SESSION["errorMessage"] = "Outside the if statement!";
echo "$_SESSION[errorMessage]";
print_r("anything");
if (isset($_POST["login"])) {
    $_SESSION["errorMessage"] = "Inside the if statement!";
    echo "$_SESSION[errorMessage]";
    session_start();
    echo "session_start";
    $username = filter_var($_POST["user_name"], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);
    echo $username;
    echo $password;
    echo __DIR__ . "/class/Member.php";
    require_once (__DIR__ . "/class/Member.php");
    echo "require";
    $member = new Member();
    echo "member created";
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
