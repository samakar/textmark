<?php

/**
 * Table data gateway.
 * http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
 * http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
 */
require_once 'gateway/Gateway.php';

class TradeLogGateway extends Gateway{
    
    public function insert( $trade_id, $transaction ) {
        $db_regdate = self::$db->quote(date("y-m-d h:i:s"));
        try {
            $count = self::$db->exec("INSERT INTO tradelog (id_trade, transaction, regdate) VALUES ($trade_id, $transaction, $db_regdate)");
            return $count; 
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function select($trade_id) {    
        try {
            return  self::$db->query("SELECT * FROM tradelog WHERE id_trade=$trade_id ORDER BY regdate DESC");
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
}

?>