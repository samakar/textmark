<?php

/**
 * Table data gateway.
 * Debit means left.Credit means right.
 */
require_once 'gateway/Gateway.php';
require_once 'model/Enums.php';

class AccountGateway extends Gateway{
    
    public function insert( $debited_account, $credited_account, $amount, $method, $trade_id, $txn) {  
        
        $db_regdate = self::$db->quote(date("y-m-d h:i:s"));
        $db_txn = self::$db->quote($txn);
        try {
            $count = self::$db->exec("INSERT INTO account (debited_account, credited_account, amount, method, id_trade, note, regdate) 
                    VALUES ($debited_account, $credited_account, $amount, $method, $trade_id, $db_txn, $db_regdate)");
            return $count; 
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">$debited_account, $credited_account, $amount, $method, $trade_id, $txn>>" .  $e->getMessage());
        }
    }
    
    public function select_by_txn($method,$txn) {
        $db_txn = self::$db->quote($txn);
        try {
            return  self::$db->query("SELECT * FROM account WHERE method=$method AND note=$db_txn")->fetch(PDO::FETCH_ASSOC);;
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }

    public function total_debit($account_id) {
        try {
            $query = self::$db->query("SELECT SUM(amount) FROM account WHERE debited_account=$account_id")->fetch(PDO::FETCH_ASSOC);;
            return $query['SUM(amount)'];      
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function total_credit($account_id) {
        try {
            $query = self::$db->query("SELECT SUM(amount) FROM account WHERE credited_account=$account_id")->fetch(PDO::FETCH_ASSOC);;
            return $query['SUM(amount)'];      
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
}

?>