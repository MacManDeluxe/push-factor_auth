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
        if(!empty($memberResult))
        {
            echo " execTwoFactorPush";
            $pushFactorResponseCode = shell_exec('./auth2factor.sh');

            //$this->execTwoFactorPush($memberResult);
            $_SESSION["userId"] = $memberResult[0]["id"];
            return true;
        }
    }

    function execTwoFactorPush($memberResult)
    {
      echo " called";
      $approveCode = "1234";
      $approve10minCode = "5678";
      $denyCode = "4321";
      $pushString = $approveCode . "approve-" . $approve10minCode . "approve 10 min-" . $denyCode . "deny";

      echo "shellExec";
      $pushFactorResponseCode = shell_exec('./auth2factor.sh');

      if ($pushFactorResponseCode == $approveCode)
      {
        $_SESSION["userId"] = $memberResult[0]["id"];
        return true;
      }
    }

}?>
