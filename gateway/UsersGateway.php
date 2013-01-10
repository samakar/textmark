<?php

/**
 * Table data gateway.
 */

require_once 'gateway/Gateway.php';

class UsersGateway extends Gateway{
            
    public function select_by_email($email) {
        
        $db_email = self::$db->quote($email);
        try {
            return  self::$db->query("SELECT * FROM users WHERE email=$db_email")->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function select_by_id($user_id) {
        
        try {
            return  self::$db->query("SELECT * FROM users WHERE id=$user_id")->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>>" .  $e->getMessage());
        }
    }
    
    public function insert($regdate) {
        $db_regdate = self::$db->quote($regdate);
        try {
            self::$db->exec("INSERT INTO users (anonymous, regdate) VALUES (1, $db_regdate)");
            return self::$db->lastInsertId(); //($count==0)? FALSE : TRUE; 
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function update_isverified( $verifi_code) {
        
        $db_verifi_code = self::$db->quote($verifi_code);
        try {
            $result = self::$db->exec("UPDATE users SET is_verified=1 WHERE verification_code =$db_verifi_code");
            return $result; 
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }

    public function update_password($user_id, $new_password) {
        
        $db_new_password = self::$db->quote($new_password);
        try {
            $result = self::$db->exec("UPDATE users SET password=$db_new_password WHERE id=$user_id");
            return $result; 
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }

    public function update_contact_info($user_id,$nickname,$cellphone) {
        
        $db_nickname = self::$db->quote($nickname);
        $db_cellphone = self::$db->quote($cellphone);
        try {
            return self::$db->exec("UPDATE users SET nickname=$db_nickname , cellphone=$db_cellphone 
                    WHERE id=$user_id");
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function update_newuser($user_id, $email, $password, $user_salt, $verifi_code, $college_id) {
        
        $db_email = self::$db->quote($email);
        $db_password = self::$db->quote($password);
        $db_user_salt = self::$db->quote($user_salt);
        $db_verifi_code = self::$db->quote($verifi_code);
        try {
            $result = self::$db->exec("UPDATE users SET email=$db_email, password=$db_password,
                    user_salt=$db_user_salt, verification_code=$db_verifi_code, anonymous=0, id_college=$college_id WHERE id=$user_id");
            return $result; 
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }

    public function delete($user_id) {
        // once there was a bug that called this method by error and delected a registered user
        // I added anonymous=1 to make sure that it will never happen again
        try {
            $count = self::$db->exec("DELETE FROM users WHERE id=$user_id AND anonymous=1");
            return ($count==0)? FALSE : TRUE; 
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }    

    public function delete_unverified($verifi_code) { 
        //http://stackoverflow.com/questions/4562787/how-to-delete-from-select-in-mysql
        $db_verifi_code = self::$db->quote($verifi_code);
        try {
            $count = self::$db->exec("DELETE FROM users WHERE is_verified=0 AND email in (SELECT * FROM 
                    (select email from users where verification_code =$db_verifi_code) AS p)");
            return ($count==0)? FALSE : TRUE; 
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }    
}

?>