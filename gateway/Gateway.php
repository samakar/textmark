<?php

/**
 * Table data gateway.
 * http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
 * http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
 */

abstract class Gateway {
   
    protected static $db = NULL;

    public function __construct() {
        if (!isset(self::$db)){
            if (SANDBOX) {
                $hostname = 'localhost';
                $username = 'root';
                $password = '';
                $dbname = 'textmark';
            }else{
                $hostname = "localhost";
                $dbname = "dkekylsd_textmark";
                $username = "dkekylsd_samakar";
                $password = "sama1351";
            }
             
            try {
                self::$db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
                self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
            }catch(PDOException $e){
                echo $e->getMessage();
            }
        }
    }   

    public function start_transaction() {
        self::$db->beginTransaction();
    }   
        
    public function commit_transaction() {
        self::$db->commit();
    }   
        
    public function rollback_transaction() {
        self::$db->rollback();
    }   
        
}
?>