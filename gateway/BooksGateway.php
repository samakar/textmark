<?php

/**
 * Table data gateway.
 * http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
 * http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
 */
require_once 'gateway/Gateway.php';

class BooksGateway extends Gateway{
       
    public function select($isbn) {
        
        try {
            return  self::$db->query("SELECT * FROM books 
                    WHERE isbn=$isbn")->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }

    public function select_all() {        
        try {
            return  self::$db->query("SELECT * FROM books");
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function select_like($term, $operator) {
        $searchTerms = explode(' ', $term);
        $searchTermTitle = array();
        $commonWords = array("and", "for", "the", "she", "not", "was","more","from","with","than","that","but","how","when","where","till");

        foreach ($searchTerms as $term) {
            $term = strtolower(trim($term));
            if (strlen($term)>2 && !in_array($term, $commonWords)) {
                $searchTermAuthor[] = "author LIKE '$term%'";
                $searchTermTitle[] = "title LIKE '$term%'";
            }
        }
        if (count($searchTermAuthor)>0){
            $whereClause = '(' . implode(' ' .$operator.' ', $searchTermAuthor) . ') OR (' . implode(' ' .$operator.' ', $searchTermTitle) . ')';
        } else {
            $whereClause = 'author=123';
        }

        try {
            return  self::$db->query("SELECT isbn FROM books WHERE $whereClause ORDER BY isbn LIMIT 10");
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }

    public function insert( $isbn, $xml, $title, $author) {
       
        $db_xml = self::$db->quote($xml);
        $db_title = self::$db->quote($title);
        $db_author = self::$db->quote($author);
        $db_regdate = self::$db->quote(date("y-m-d h:i:s"));
        try {
            $count = self::$db->exec("INSERT INTO books (isbn, xml, title, author, regdate) 
                    VALUES ($isbn, $db_xml, $db_title, $db_author, $db_regdate)");
            return $count; 
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
    public function update( $isbn, $xml) {
        
        $db_xml = self::$db->quote($xml);
        $db_regdate = self::$db->quote(date("y-m-d h:i:s"));
        try {
            $result = self::$db->exec("UPDATE books SET xml=$db_xml, regdate=$db_regdate WHERE isbn=$isbn");
            return $result; 
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
    
}

/* it seems that students search for book names not authers so in the new version of func. I deleted $searchTermAuthor
   public function select_like($term, $operator) {
        $searchTerms = explode(' ', $term);
        $searchTermAuthor = array();
        $searchTermTitle = array();
        foreach ($searchTerms as $term) {
            $term = trim($term);
            if (strlen($term)>2) {
                //$searchTermAuthor[] = "author LIKE '%$term%'";
                $searchTermTitle[] = "title LIKE '%$term%'";
            }
        }
        if (count($searchTermAuthor)>0){
            $whereClause = '(' . implode(' ' .$operator.' ', $searchTermAuthor) . ') OR (' . implode(' ' .$operator.' ', $searchTermTitle) . ')';
        } else {
            $whereClause = 'author=123';
        }
        //$db_term = self::$db->quote('%' . $term . '%');
        try {
            return  self::$db->query("SELECT isbn FROM books WHERE $whereClause ORDER BY isbn LIMIT 10");
        }catch(PDOException $e){
            throw new Exception(get_class($this) . ">>>" . __FUNCTION__ . ">>>" .  $e->getMessage());
        }
    }
*/
?>