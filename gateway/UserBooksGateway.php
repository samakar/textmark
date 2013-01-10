<?php

/**
 * Table data gateway.
 * 
 * http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
 */
require_once 'gateway/Gateway.php';
require_once 'model/Enums.php';

class UserBooksGateway extends Gateway{
               
    public function select($user_id, $wishlist) {
        $db_wishlist = $this->evaluate($wishlist);
        $deleted = BookStatus::Deleted;
        $delivered = BookStatus::Delivered;
        try {   
            return self::$db->query(
                    "select * from userbooks inner join 
                        (
                        select MAX(id) as maxid from userbooks 
                        WHERE id_user=$user_id AND wishlist=$db_wishlist 
                        GROUP BY isbn
                        ) AS q1 
                    ON userbooks.id = q1.maxid 
                    WHERE userbooks.bookstatus!=$deleted AND userbooks.bookstatus!=$delivered");
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function exist($isbn, $user_id, $wishlist){
        
        $db_wishlist = $this->evaluate($wishlist);
        $deleted = BookStatus::Deleted;
        $delivered = BookStatus::Delivered;
        try {
            $row = self::$db->query(
                    "select * from userbooks inner join 
                        (
                        select MAX(id) as maxid from userbooks 
                        WHERE id_user=$user_id AND wishlist=$db_wishlist AND isbn=$isbn
                        ) AS q1 
                    ON userbooks.id = q1.maxid 
                    WHERE userbooks.bookstatus!=$deleted AND userbooks.bookstatus!=$delivered"
                    )->fetch(PDO::FETCH_ASSOC);
            return (isset($row['isbn']))? TRUE : FALSE;    
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">$isbn>$user_id>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function insert( $isbn, $user_id, $wishlist, $status, $rental=0) {
        $db_wishlist = $this->evaluate($wishlist);
        $db_regdate = self::$db->quote(date("y-m-d h:i:s"));
        try {
            $count = self::$db->exec("INSERT INTO userbooks (isbn, id_user, wishlist, bookstatus, rental, regdate) 
                VALUES ($isbn, $user_id, $db_wishlist, $status, $rental, $db_regdate)");
            return $count; 
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function find_campus($isbn, $user_id, $wishlist, $rental) {
        // find other students in the user campus who sell/want that book:
        // first find latest book status for the book for every student in that university
        // second check filter out the books with the desired status
        // note: fetch(PDO::FETCH_ASSOC) only returns the first row
        $db_wishlist = $this->evaluate($wishlist);
        $alert = BookStatus::Alert;
        try {
            return  self::$db->query(
                    "SELECT * FROM userbooks INNER JOIN 
                        (
                        SELECT id_user, MAX(userbooks.id) AS maxid FROM userbooks 
                        INNER JOIN users ON users.id=userbooks.id_user 
                        WHERE users.id_college IN (SELECT id_college FROM users WHERE id=$user_id) 
                        AND userbooks.isbn=$isbn AND userbooks.id_user!=$user_id GROUP BY id_user
                        ) AS q1 
                    ON userbooks.id=q1.maxid WHERE wishlist=$db_wishlist AND rental=$rental 
                    AND userbooks.bookstatus=$alert ORDER BY userbooks.id")
                    ->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }

    public function select_by_isbn($isbn, $user_id) {
        // the query result can have maximum one row 
        $deleted = BookStatus::Deleted;
        $delivered = BookStatus::Delivered;
        try {
            return self::$db->query(
                    "SELECT * FROM userbooks INNER JOIN 
                        (
                        SELECT MAX(id) AS maxid FROM userbooks WHERE isbn=$isbn AND id_user=$user_id
                        ) AS q1 
                    ON userbooks.id=q1.maxid WHERE 
                    userbooks.bookstatus!=$deleted AND userbooks.bookstatus!=$delivered")
                    ->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function select_all($wishlist, $college_id) {
        // select 20 most recent books that are on sale or wanted
        // q1 finds the last status of all the user's books in the college 
        // q2 finds the 20 most recent books that are in alert status
        // the last query gets books info
        $db_wishlist = $this->evaluate($wishlist);
        $alert = BookStatus::Alert;
        try {
            return  self::$db->query(
                    "SELECT * FROM books INNER JOIN 
                        (
                        SELECT userbooks.isbn, MAX(userbooks.id) AS lastid FROM userbooks INNER JOIN 
                            (
                            SELECT isbn, id_user, MAX(userbooks.id) AS maxid FROM userbooks 
                            INNER JOIN users ON users.id=id_user 
                            WHERE id_college=$college_id AND wishlist=$db_wishlist 
                            GROUP BY userbooks.isbn, id_user
                            ) AS q1 
                        ON userbooks.id=q1.maxid WHERE userbooks.bookstatus=$alert 
                        GROUP BY userbooks.isbn LIMIT 20
                        ) AS q2
                    ON q2.isbn=books.isbn ORDER BY lastid");
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }   
    
    private function evaluate($wishlist) {
        return (($wishlist=="TRUE") ? TRUE_MYSQL : FALSE_MYSQL);
    }
    
}

?>