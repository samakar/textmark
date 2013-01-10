<?php

/**
 * Table data gateway.
 * http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
 * http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
 */
require_once 'gateway/Gateway.php';

class UserHistoryGateway extends Gateway{
    
    public function insert( $user_id, $op, $isbn, $anonymous ) {  
        
        $db_op = self::$db->quote($op);
        $db_isbn = self::$db->quote(str_replace(array(' ', '-', '.'), '', $isbn));
        $db_regdate = self::$db->quote(date("y-m-d h:i:s"));
        try {
            $count = self::$db->exec("INSERT INTO userhistory (id_user, op, isbn, anonymous, regdate) 
                    VALUES ($user_id, $db_op, $db_isbn, $anonymous, $db_regdate)");
            return $count; 
        }catch(PDOException $e){
            throw new Exception( __FUNCTION__ . ">>>" . $e->getMessage());
        }
    }
    
    public function select_top10() {
        
        try {
            return  self::$db->query("SELECT DISTINCT isbn FROM userhistory WHERE op='book_find' AND isbn!=0 ORDER BY id DESC LIMIT 10");
        }catch(PDOException $e){
            throw new Exception( __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
}

?>