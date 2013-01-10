<?php
require_once 'gateway/Gateway.php';

class PriceHistoryGateway extends Gateway{
    
    public function insert( $isbn, $new_price, $used_price, $buyback_price ) {  
        
        $db_new_price = self::$db->quote($new_price);
        $db_used_price = self::$db->quote($used_price);
        $db_buyback_price = self::$db->quote($buyback_price);
        $db_regdate = self::$db->quote(date("y-m-d h:i:s"));
        try {
            $count = self::$db->exec("INSERT INTO pricehistory (isbn, new_price, used_price, buyback_price, regdate) 
                    VALUES ($isbn, $db_new_price, $db_used_price, $db_buyback_price, $db_regdate)");
            return $count; 
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
        
}

?>