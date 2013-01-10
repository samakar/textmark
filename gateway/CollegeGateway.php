<?php

/**
 * Table data gateway.
 * http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
 * http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
 */
require_once 'gateway/Gateway.php';

class CollegeGateway extends Gateway{
    
    public function insert( $name, $domain ) {  
        
        $db_name = self::$db->quote($name);
        $db_domain = self::$db->quote($domain);        
        $db_regdate = self::$db->quote(date("y-m-d h:i:s"));
        try {
            self::$db->exec("INSERT INTO college (name, domain, regdate) VALUES ($db_name, $db_domain, $db_regdate)");
            return self::$db->lastInsertId();
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function select($domain) {
        $db_domain = self::$db->quote($domain);        
        try {
            return  self::$db->query("SELECT * FROM college WHERE domain=$db_domain")->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function select_by_id($id) {
        //die ($id);
        try {
            return  self::$db->query("SELECT * FROM college WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }

    public function update( $id, $name) {
        $db_name = self::$db->quote($name);

        try {
            self::$db->exec("UPDATE college SET name=$db_name WHERE id=$id");
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
}

?>