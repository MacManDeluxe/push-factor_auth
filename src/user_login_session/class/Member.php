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
            //echo " execTwoFactorPush";
            $authenticated = $this->execTwoFactorPush($memberResult);
            return $authenticated;
        }
    }

    private function execTwoFactorPush($memberResult)
    {
      //echo " called ";
      //
      $approveCode = random_int(1000,9999);
      $usedCodes = array($approveCode);

      $approve10secCode = $this->randomNumberExcluding(1000,9999,$usedCodes);
      $usedCodes[1] = $approve10secCode;

      $denyCode = $this->randomNumberExcluding(1000,9999,$usedCodes);
      $usedCodes[2] = $denyCode;

      $cancelCode = $this->randomNumberExcluding(1000,9999,$usedCodes);

      $pushString = $approveCode."Approve-".
                    $approve10secCode."Approve 10 Minutes-".
                    $denyCode."Deny-".
                    $cancelCode."Cancel Login-".session_id()."\n";
      //echo $pushString;
      $pushFactorResponseCode = $denyCode; //in case something goes wrong, deny

      //connect to receiverapp, send auth codes
      if (!extension_loaded('sockets')) {
          die('The sockets extension is not loaded.');
      }
      $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
      socket_connect($socket, 'localhost', 8080);
      socket_write($socket, $pushString, strlen($pushString));
      //wait for response code from receiverapp
      $pushFactorResponseCode = socket_read($socket, strlen($approveCode));
      socket_close($socket);

      //set session login session based on approval code received
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

      /*
      * @param int   $from     From number
      * @param int   $to       To number
      * @param array $excluded Additionally exclude numbers
      * @return int
      */
    private function randomNumberExcluding($from, $to, array $excluded = [])
    {
      $func = function_exists('random_int') ? 'random_int' : 'mt_rand';

      do {
        $number = $func($from, $to);
      } while (in_array($number, $excluded, true));

      return $number;
    }

}?>
