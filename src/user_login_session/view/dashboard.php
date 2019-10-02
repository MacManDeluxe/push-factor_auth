<?php
namespace Phppot;

use \Phppot\Member;

if (!empty($_SESSION["userId"])) {
    require_once (__DIR__ . '/../class/Member.php');
    $member = new Member();
    $memberResult = $member->getMemberById($_SESSION["userId"]);
    if(!empty($memberResult[0]["display_name"])) {
        $displayName = ucwords($memberResult[0]["display_name"]);
    } else {
        $displayName = $memberResult[0]["user_name"];
    }
}
?>
<html>
<head>
<title>User Login</title>
<link href="./view/css/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div>
      <!--<script>
        setTimeout(function(){
        window.location.reload(1);
      }, 5000);
    </script>-->
        <div class="dashboard">
            <div class="member-dashboard">Welcome <b>
            <?php echo $displayName;
             ?>
            </b>, You have successfully logged in!<br>
            <?php $timeSinceLogin = time() - $_SESSION['loggedin_time'];
            echo 'Time since login: '.(string)$timeSinceLogin; ?><br><?php
            echo 'Max duration: '.(string)$_SESSION['max_session_duration']; ?><br><?php
            echo 'Login Session Expired = ';
            if(isLoginSessionExpired()) {
              echo "TRUE";
            }else{echo "FALSE";}?> <br>
            Click to <a href="./logout.php" class="logout-button">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
//sends cancel code, listens for cancel code
$cancelCode = random_int(1000,9999);
$pushString = $cancelCode . "Cancel\n";

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_connect($socket, 'localhost', 8080);
socket_write($socket, $pushString, strlen($pushString));
//wait for response code from receiverapp
$pushFactorResponseCode = socket_read($socket, strlen($cancelCode));
socket_close($socket);
if($pushFactorResponseCode == $cancelCode) {
  require_once './logout.php';
}
 ?>
