<?php

require_once 'gateway/UserHistoryGateway.php';
require_once 'gateway/BooksGateway.php';
require_once 'gateway/UserBooksGateway.php';
require_once 'model/Service.php';
require_once 'model/AmazonService.php';
require_once 'model/TradeService.php';

/**
 * Books API.
 * 
 * Each service call is transaction script that returns xml.
 *
 */
class BooksService extends Service{
    
    private $booksGateway    = NULL;
    private $userbooksGateway  = NULL;
    private $tradeService = NULL;
    const UPDATEINTERVAL = 24; // update interval of books table in hours
    
    public function __construct() {
        $this->booksGateway = new BooksGateway();
        $this->userbooksGateway = new UserBooksGateway();
        $this->tradeService = new TradeService;
    }
             
    public function search( $term, $user_id ) {
        //fetchColumn move the index and there's no way to go back
        // therefore the query is done twice
        if (preg_match_all('/[0-9]/', $term, $matches)>=10){
            $isbn13 = $this->validateISBN($term);
            return $this->findBook($isbn13, $user_id);
        }else{
            $term = str_replace(array('+', '-', '.' , ',', "'"), ' ', $term);
            $result = $this->booksGateway->select_like($term, "AND");
            if ($result->fetchColumn()==false) {
                $result = $this->booksGateway->select_like($term, "OR");
                if ($result->fetchColumn()==false) {
                    throw new Exception("We could not find a book with a similar name in our database. Please enter the book ISBN to find it.");                    
                }else {
                    $result = $this->booksGateway->select_like($term, "OR");
                    return $this->makeListXML($result,$user_id);             
                }
            }else {
                $result = $this->booksGateway->select_like($term, "AND");
                return $this->makeListXML($result,$user_id);  ;              
            }
        }
    }

    public function findBook( $isbn13, $user_id ) {
        $row = $this->booksGateway->select($isbn13);

        if (isset($row['isbn'])){
            $regdate = $row['regdate'];
            if (time() < strtotime($regdate . " + " . self::UPDATEINTERVAL . " hours")){
                $xml = new DomDocument('1.0');
                $xml->loadXML($row['xml']);
            } else {
                $xml = $this->createBookXML($isbn13);
                $this->booksGateway->update( $isbn13, $xml->saveXML());
            }
            $xml = $this->tradeService->add_book_status( $isbn13, $user_id, $xml);            
        }else {
            $xml = $this->createBookXML($isbn13);
            $amazon = new AmazonService;
            $title = $this->omit_brackets($amazon->getBookInfo($xml,'Title'));
            $author = $amazon->getBookInfo($xml,'Author');
            $this->booksGateway->insert( $isbn13, $xml->saveXML(), $title, $author);                
        }

        $xml = $this->tradeService->add_availability( $xml , $user_id);
        return $xml;        
    }
   
    public function removeBookList( $isbn , $user_id , $wishlist) {
        $this->userbooksGateway->insert($isbn , $user_id, $wishlist, BookStatus::Deleted);
    }

    public function swapBookList( $isbn , $user_id , $wishlist) {
        $new_wishlist = ($wishlist=='TRUE') ? 'FALSE' : 'TRUE';
        $exists = $this->userbooksGateway->exist($isbn, $user_id, $new_wishlist);
        if (!$exists){
            $this->userbooksGateway->insert($isbn, $user_id, $wishlist, BookStatus::Deleted);
            $this->userbooksGateway->insert($isbn, $user_id, $new_wishlist, BookStatus::None);
        }
    }

    public function addBookList( $isbn , $user_id , $wishlist) {
        // one instance of book is allowed in both lists
        if ($this->userbooksGateway->exist($isbn, $user_id, "TRUE"))
                throw new Exception('The book is already in your wish list.');;
        if ($this->userbooksGateway->exist($isbn, $user_id, "FALSE"))
                throw new Exception('The book is already in your book list.');;
        
        $this->userbooksGateway->insert($isbn, $user_id, $wishlist, BookStatus::None);
    }

    public function getBookList( $user_id , $wishlist) {
        if ($_SESSION['anonymous'] == 1) $user_id=0;
        $result = $this->userbooksGateway->select($user_id, $wishlist);
        return $this->makeListXML($result,$user_id);
    }

    public function add_recommendation($xml) {
        // we need 5 last unique books which have been searched through book_find
        $userHistoryGateway = new UserHistoryGateway;
        $books = $userHistoryGateway->select_top10();
        $xml_string = '';
        $isbn_string = '';
        $counter=0; 
        foreach ($books as $book) {
            try{  
                if ($counter<5){
                    $isbn13 = $this->validateISBN($book['isbn']);
                    if (!strpos($isbn_string,$isbn13) && $isbn13!==FALSE) {
                        $isbn_string = $isbn_string ."," . $isbn13 ;
                        $row = $this->booksGateway->select($isbn13);
                        if (isset($row['isbn'])){
                            $counter++;
                            //remove xml declaration
                            $book_xml_string = trim(preg_replace('/<\?xml.*\?>/', '', $row['xml'], 1));
                            $xml_string .= $book_xml_string;
                        }
                    }
                }
            }catch ( Exception $e ) {
                // next row
            }
        }
        $list = new DomDocument;
        $list->loadXML('<root><Recommendation>' . $xml_string . '</Recommendation></root>');
        $node = $list->getElementsByTagName('root');
        $node = $list->getElementsByTagName('root')->item(0);
        $xmlContent = $xml->importNode($node,TRUE);
        $doc_root = $xml->getElementsByTagName('ItemLookupResponse')->item(0);
        $doc_root->appendChild($xmlContent);
        return $xml;
    }

    public function showcase($college_id) {
        // we show all the books in database
        $books = $this->userbooksGateway->select_all("FALSE",$college_id);        
        $xml_string_buyback = '';
        foreach ($books as $row) {
            //echo( $row['isbn'] . ">>>>>" . "</br>");
            $book_xml_string = trim(preg_replace('/<\?xml.*\?>/', '', $row['xml'], 1));
            $xml_string_buyback .= $book_xml_string;
        }
        
        $books = $this->userbooksGateway->select_all("TRUE",$college_id);
        $xml_string_used = '';
        foreach ($books as $book) {
            //echo( $book['isbn'] . "<<<<<<" . "</br>");
            $book_xml_string = trim(preg_replace('/<\?xml.*\?>/', '', $book['xml'], 1));
            $xml_string_used .= $book_xml_string;
        }
        $list = new DomDocument;
        $list->loadXML('<Root><Buyback>' . $xml_string_buyback . '</Buyback><Used>' . $xml_string_used . '</Used></Root>');
        return $list;
    }
    
    public function record_current_prices() {
        // called only by pricehistory.php
        require_once 'gateway/PriceHistoryGateway.php';
        
        $amazon = new AmazonService;
        $priceHistoryGateway = new PriceHistoryGateway;
        $books = $this->booksGateway->select_all();
        $i=0;
        foreach ($books as $book) {
            if (strtotime($book['regdate'] . " + " . self::UPDATEINTERVAL . " hours") > time()){
                $xml = new DomDocument('1.0');
                $xml->loadXML($book['xml']);
            } else {
                $xml = $this->createBookXML($book['isbn']);
                $this->booksGateway->update( $book['isbn'], $xml->saveXML());
            }
            
            $new_amazon = str_replace('$', '', $amazon->getBookInfo($xml,'NewPrice'));
            $used_amazon = str_replace('$', '', $amazon->getBookInfo($xml,'UsedPrice'));
            $buy_amazon = str_replace('$', '', $amazon->getBookInfo($xml,'TradeInValue'));
                       
            // if the book is not sold anymore don't record anything.
            //if (!is($new_amazon) || !is_null($used_amazon) || !is_null($buy_amazon)){
                $priceHistoryGateway->insert($book['isbn'],$new_amazon,$used_amazon,$buy_amazon);
            //}
            $i+=1;
        }
        
        return $i;
    }

    private function validateISBN( $isbn ) {
        /*
         *  Acknowledgment: Based on 'ISBN Checker' V.2.0 by Erika Q. Stokes - http://erikastokes.com/php/checkisbn.php
         *  Function:       This method accepts for input a string of characters and checks
         *                  the string to determine if it is a valid 10 or 13 digit ISBN. 
         *                  The method returns 13 digit ISBN in numeric format even if 10 digit ISBN is provided
         */
   
        if ( !isset($isbn) || empty($isbn) ) throw new Exception('ISBN is required');
 
        /* Remove spaces, dots, and hyphens from the ISBN */
        $isbn = str_replace(array(' ', '-', '.'), '', $isbn);

        /* Figure out how long the remaining string is */
        $length = strlen($isbn);

        /* Set the checkdigit to the last character in the string */
        $checkdigit = substr($isbn, -1);

        switch ($length) {

            case "10":

                /* Check to see if the first 9 digits are numeric */
                if (!is_numeric(substr($isbn, -10, 9))) throw new Exception("First 9 letters must be numeric.");

                /* Check to see if the checkdigit is a number -- if it is,leave it alone.  If it is not, convert it to uppercase. */
                $checkdigit = (!is_numeric($checkdigit)) ? $checkdigit : strtoupper($checkdigit);

                /* Check to see if the checkdigit is X -- if it is, set it to  10, if it isn't, leave it alone. */
                $checkdigit = ($checkdigit == "X") ? "10" : $checkdigit;

                /* Initialize $sum to 0 */
                $sum = 0;

                /* Cycle through, adding up the numbers */
                for ($i = 0; $i < 9; $i++) {
                        $sum = $sum + ($isbn[$i] * (10 - $i));
                }

                /* Add the checkdigit at the end */
                $sum = $sum + $checkdigit;

                /* Does it divide like it is supposed to? */
                $mod = $sum % 11;

                if ($mod == 0) {	// If so, the number is correct
                    /*  figure out the 13 digit equivalent */
                    $newisbn = substr($isbn, -10, 9);	// drop the checkdigit
                    $newisbn = "978" . $newisbn; // Add 978 prefix

                    /* Run through the new first 12 digits, weighting them alternately by 1 and 3, sum them up */
                    $sum = $newisbn[0] + ($newisbn[1] * 3) + $newisbn[2] + ($newisbn[3] * 3) +
                            $newisbn[4] + ($newisbn[5] * 3) + $newisbn[6] + ($newisbn[7] * 3) +
                            $newisbn[8] + ($newisbn[9] * 3) + $newisbn[10] + ($newisbn[11] * 3);

                    /* Get the remainder when the sum is divided by 10 */
                    $mod = $sum % 10;

                    /* Calculate the new checkdigit */
                    $checkdigit = 10 - $mod;

                    /* If $checkdigit is 10, change it to 0. Otherwise leave as-is. */
                    $checkdigit = ($checkdigit == "10") ? "0" : $checkdigit;

                    /* Append it to the first 12 digits */
                    $newisbn = $newisbn . $checkdigit;
                    return $newisbn;
                }
                else {
                    throw new Exception('ISBN is not valid.');
                }
                break;

                case "13":

                    /* Run through the new first 12 digits, weighting them   alternately by 1 and 3, sum them up */
                    $sum = $isbn[0] + ($isbn[1] * 3) + $isbn[2] + ($isbn[3] * 3) +
                                            $isbn[4] + ($isbn[5] * 3) + $isbn[6] + ($isbn[7] * 3) +
                                            $isbn[8] + ($isbn[9] * 3) + $isbn[10] + ($isbn[11] * 3);

                    /* Get the remainder when the sum is divided by 10 */
                    $mod = $sum % 10;

                    /* Calculate the new checkdigit */
                    $correct_checkdigit = 10 - $mod;

                    /* If $checkdigit is 10, change it to 0. Otherwise leave as-is. */
                    $correct_checkdigit = ($correct_checkdigit == "10") ? "0" : $correct_checkdigit;

                    /* Compare the two checkdigits */
                    if ($checkdigit == $correct_checkdigit) {
                        return $isbn;
                    } else {
                        throw new Exception("ISBN is invalid.");
                    }
                    break;
                default:	// If the isbn is neither 10 nor 13 digits, display an error
                    throw new Exception('ISBN length is not correct.');
        }
}

    private function createBookXML( $isbn13) {
        $amazon = new AmazonService;
        $xml = $amazon->getBook($isbn13);

        $xml = $this->tradeService->add_price($xml);
        return $xml;
    }

    private function makeListXML( $isbn_list,$user_id) {
        $xml_string = '';
        foreach ($isbn_list as $row) {
            $book_xml_string = $this->findBook($row['isbn'],$user_id)->saveXML();
            //remove xml declaration
            $book_xml_string = trim(preg_replace('/<\?xml.*\?>/', '', $book_xml_string, 1));
            $xml_string .= $book_xml_string;
        }
        $xml = new DomDocument;
        $xml->loadXML('<List>' . $xml_string . '</List>');

        return $xml;
    }
    
} 
?>