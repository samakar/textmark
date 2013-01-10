<?php

/**
 * Table data gateway.
 * http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
 * http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
 */
require_once 'gateway/Gateway.php';

class LoggedUsersGateway  extends Gateway{
        
    public function select($session_id) {
       $db_session_id = self::$db->quote($session_id);
       try {
            return  self::$db->query("SELECT userlog.*, users.anonymous, users.email 
                    , users.nickname, users.id_college, users.role FROM userlog 
                    INNER JOIN users ON  users.id=userlog.user_id 
                    WHERE userlog.session_id=$db_session_id")->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function insert( $user_id, $session_id , $token, $rememberme) {
        $db_session_id = self::$db->quote($session_id);
        $db_token = self::$db->quote($token);
        $db_regdate = self::$db->quote(date("y-m-d h:i:s"));
        try {
            $count = self::$db->exec("INSERT INTO userlog (user_id, session_id, token, rememberme, regdate) 
                    VALUES ($user_id, $db_session_id, $db_token, $rememberme, $db_regdate)");
            return ($count==0)? FALSE : TRUE; 
        }catch(PDOException $e){
            echo "INSERT INTO userlog (user_id, session_id, token, rememberme, regdate) 
                    VALUES ($user_id, $db_session_id, $db_token, $rememberme, $db_regdate)";
            throw new Exception( get_class($this) . ">>>" . __FUNCTION__ . ">>>" . $e->getMessage());
        }
    }
 
    public function delete($user_id) {
  
        try {
            $count = self::$db->exec("DELETE FROM userlog WHERE user_id=$user_id");
            return ($count==0)? FALSE : TRUE; 
        }catch(PDOException $e){
            throw new Exception( get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage()) . ">>>$user_id<<<" ;
        }
    }    
}

?>