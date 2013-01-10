<?php

/**
 * Table data gateway.
 * http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
 * http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
 */
require_once 'gateway/Gateway.php';
require_once 'model/Enums.php';

class TradeGateway extends Gateway{
    
    public function insert( $isbn, $buyer_id, $seller_id, $buyer_amount, $seller_amount, $token, $buyer_message, $seller_message, $rental, $returnDate) {
        //echo $isbn, $buyer_id, $seller_id, $buyer_amount, $seller_amount, $token, $buyer_message, $seller_message;
        //die;
        $db_isbn = self::$db->quote($isbn);
        $db_amount_buyer = $buyer_amount;
        $db_amount_seller = $seller_amount;
        $db_token = self::$db->quote($token);
        $db_buyer_message = self::$db->quote($buyer_message);
        $db_seller_message = self::$db->quote($seller_message);
        $db_returnDate = self::$db->quote($returnDate);

        try {
            self::$db->exec("INSERT INTO trade (isbn, id_buyer, id_seller,amount_buyer, amount_seller, token, buyer_message, seller_message,rental,returndate) 
                VALUES ($db_isbn, $buyer_id, $seller_id, $db_amount_buyer, $db_amount_seller, $db_token, $db_buyer_message, $db_seller_message,$rental,$db_returnDate)");
            return self::$db->lastInsertId();
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function update( $trade_id, $buyer_message, $seller_message) {
        //echo $isbn, $buyer_id, $seller_id, $buyer_amount, $seller_amount, $token, $buyer_message, $seller_message;
        //die;
        $db_buyer_message = self::$db->quote($buyer_message);
        $db_seller_message = self::$db->quote($seller_message);

        try {
            self::$db->exec("UPDATE trade SET buyer_message=$db_buyer_message, seller_message=$db_seller_message WHERE id=$trade_id");
        }catch(PDOException $e){
            throw new Exception( __FUNCTION__ . ">>>" . $e->getMessage());
        }
    }
    
    public function update_amounts( $trade_id, $buyer_amount, $seller_amount) {
        try {
            self::$db->exec("UPDATE trade SET amount_buyer=$buyer_amount, amount_seller=$seller_amount WHERE id=$trade_id");
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }

    public function select($token) {
        
        $db_token = self::$db->quote($token);
        try {
            return  self::$db->query("SELECT * FROM trade WHERE token=$db_token")->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }

    public function select_by_buyer($buyer_id, $isbn) {
        // 'ORDER By id DESC LIMIT 1' is added because in a rare case buyer may have bought the same book before
        
        try {
            return  self::$db->query("SELECT * FROM trade WHERE isbn=$isbn AND id_buyer=$buyer_id ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function select_by_seller($seller_id, $isbn) {
        // 'ORDER By id DESC LIMIT 1' is added because in a rare case seller may have sold the same book before
        
        try {
            return  self::$db->query("SELECT * FROM trade WHERE isbn=$isbn AND id_seller=$seller_id  ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function select_by_id($trade_id) {      
        try {
            return  self::$db->query("SELECT * FROM trade WHERE id=$trade_id")->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }   
    
    public function find_unpaid($trade_id) {
        $paid= Transaction::Pay;
        try {
            return  self::$db->query("SELECT * FROM trade WHERE id=$trade_id AND id NOT IN (SELECT id_trade FROM tradelog WHERE id_trade=$trade_id AND transaction=$paid)")->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }

}
?>