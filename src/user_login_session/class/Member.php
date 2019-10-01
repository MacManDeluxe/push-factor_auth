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
        $memberResult = $this->ds->select($query, $paramType, $paramArray);
        if(!empty($memberResult)) //if password correct
        {
            echo " execTwoFactorPush";
            //$pushFactorResponseCode = shell_exec('./auth2factor.sh');
            $authenticated = $this->execTwoFactorPush($memberResult);
            //$_SESSION["userId"] = $memberResult[0]["id"];
            return $authenticated;
        }
    }

    private function execTwoFactorPush($memberResult)
    {
      echo " called ";
      $approveCode = "1234";
      $approve10minCode = "5678";
      $denyCode = "4321";
      $pushString = $approveCode . "approve-" . $approve10minCode . "approve 10 min-" . $denyCode . "deny\n";
      echo $pushString;
      $pushFactorResponseCode = $denyCode; //in case something goes wrong, deny

      //connect to receiverapp using http_post_data ( string $url , string $data)
      //$pushFactorResponseCode = http_post_data("http://localhost:8080", $pushString);

      //connect to receiverapp using file_get
      //$pushFactorResponseCode = file_get_contents("http://localhost:8080" . $pushString);

      //connect to receiverapp using sockets
      //check if sockets extension is loaded
      if (!extension_loaded('sockets')) {
          die('The sockets extension is not loaded.');
      }
      $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
      //socket_bind($socket, 'localhost');
      //socket_listen($socket);
      //socket_accept($socket);
      //socket_set_block($socket);  //forces script to wait for connections to finish

      socket_connect($socket, 'localhost', 8080);

      $value = socket_write($socket, $pushString, strlen($pushString));
      echo $value;
      //die($value);
      //socket_sendto($socket, $pushString, strlen($pushString), 0, 'localhost', 8080);
      //socket_listen($socket);
      /*do {
        $buffer = socket_read($socket, strlen("1234"));
        $pushFactorResponseCode .= $buffer;
      } while (!empty($buffer));*/
      //socket_recv($socket, $pushFactorResponseCode, 2048, MSG_WAITALL);
      $pushFactorResponseCode = socket_read($socket, strlen($approveCode));
      socket_close($socket);
      //$pushFactorResponseCode = shell_exec('./auth2factor.sh');

      if ($pushFactorResponseCode == $approveCode) {
        $_SESSION["userId"] = $memberResult[0]["id"];
        return true;
      }
      elseif ($pushFactorResponseCode == $approve10minCode) {
        //10 minutes = 600 seconds
        //destroys current session cookie so new time-limit can be set
        session_unset();
        session_destroy();
        session_set_cookie_params(3,"/");
        session_start();
        //session_start(['cookie_lifetime' => 3,]); //10 seconds for testing
        $_SESSION["userId"] = $memberResult[0]["id"];
        return true;
      }
      elseif ($pushFactorResponseCode == $denyCode) {
        return false;
      }
    }

}?>
