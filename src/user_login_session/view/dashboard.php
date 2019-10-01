<?php
namespace Phppot;

use \Phppot\Member;
//include("../session-expired.php");

if (!empty($_SESSION["userId"])) {
    require_once (__DIR__ . '/../class/Member.php');
    $member = new Member();
    $memberResult = $member->getMemberById($_SESSION["userId"]);
    if(!empty($memberResult[0]["display_name"])) {
        $displayName = ucwords($memberResult[0]["display_name"]);
    } else {
        $displayName = $memberResult[0]["user_name"];
    }
} //else return to index.php?
/*else {
  require_once '../logout.php';
}*/
?>
<html>
<head>
<title>User Login</title>
<link href="./view/css/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div>
      <script>
        setTimeout(function(){
        window.location.reload(1);
      }, 5000);
      </script>
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
