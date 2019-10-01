<?php
namespace Phppot;

use \Phppot\DataSource;

class Member
{

    private $dbConn;

    private $ds;

    public function __construct()
    {
        require_once "DataSource.php";

        $this->ds = new DataSource();
    }

    function getMemberById($memberId)
    {
        $query = "select * FROM registered_users WHERE id = ?";
        $paramType = "i";
        $paramArray = array($memberId);
        $memberResult = $this->ds->select($query, $paramType, $paramArray);

        return $memberResult;
    }

    public function processLogin($username, $password)
    {
        echo "processLogin";
        $passwordHash = md5($password);
        $query = "select * FROM registered_users WHERE user_name = ? AND password = ?";
        $paramType = "ss";
        $paramArray = array($username, $passwordHash);
        //check username and password against MySQL database
        $memberResult = $this->ds->select($query, $paramType, $paramArray);
        if(!empty($memberResult)) //if password correct
        {
            echo " execTwoFactorPush";
            $authenticated = $this->execTwoFactorPush($memberResult);
            //$_SESSION["userId"] = $memberResult[0]["id"];
            return $authenticated;
        }
    }

    private function execTwoFactorPush($memberResult)
    {
      echo " called ";
      $approveCode = "1234";
      $approve10secCode = "5678";
      $denyCode = "4321";
      $pushString = $approveCode . "approve-" . $approve10secCode . "approve 10 min-" . $denyCode . "deny\n";
      echo $pushString;
      $pushFactorResponseCode = $denyCode; //in case something goes wrong, deny

      //check if sockets extension is loaded
      if (!extension_loaded('sockets')) {
          die('The sockets extension is not loaded.');
      }
      $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

      socket_connect($socket, 'localhost', 8080);

      $value = socket_write($socket, $pushString, strlen($pushString));
      echo $value;
      //wait for response code from receiverapp
      $pushFactorResponseCode = socket_read($socket, strlen($approveCode));
      socket_close($socket);

      //set session login cookie based on approval code received
      if ($pushFactorResponseCode == $approveCode) {
        $_SESSION["userId"] = $memberResult[0]["id"];
        $_SESSION['loggedin_time'] = time();
        return true;
      }
      elseif ($pushFactorResponseCode == $approve10secCode) {
        //session limits enforced by isLoginSessionExpired() function
        $_SESSION["userId"] = $memberResult[0]["id"];
        $_SESSION['loggedin_time'] = time();
        $_SESSION['max_session_duration'] = 5; //5 seconds for demo
        return true;
      }
      else{ //if pushFactorResponseCode == $denyCode (or anything else)
        return false;
      }
    }

}?>
