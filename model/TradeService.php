<?php

/**
 * Description of Dealer
 *
 * @author Amir
 */

require_once 'model/Service.php';
require_once 'model/BooksService.php';
require_once 'model/AmazonService.php';
require_once 'model/AccountService.php';
require_once 'gateway/UserBooksGateway.php';
require_once 'gateway/UsersGateway.php';
require_once 'gateway/TradeGateway.php';
require_once 'gateway/TradeLogGateway.php';
require_once 'gateway/Gateway.php';

class TradeService extends Service{
    
    const COMMISION = 0.3;          // textmark commision rate on gross margin %
    const WEIGHT = 0.4;             // textmark divides margin between usedprice and tradein %
    const PROCESSING_FEE = 0.5;     // money processing fee $
    const MIN_DIFF = 0.01;          // the min. amount that TextMark used book is cheaper than other sellers
    const RENT_DIFF = 0.9;          // the min. margin that TextMark rent book is cheaper than TextMark used book or marketplace rent price
    
    public function add_book_status( $isbn13, $user_id, $bookXML) {
        $userBooksGateway = new UserBooksGateway();
        $book= $userBooksGateway->select_by_isbn($isbn13, $user_id);
        // go on if the book is in a list
        if (isset($book['id'])){ 
            $wishlist = ($book['wishlist']==TRUE_MYSQL) ? "TRUE" : "FALSE";
            $rental = ($book['rental']==TRUE_MYSQL) ? "TRUE" : "FALSE";
            $amazon = new AmazonService;
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::WishList, $wishlist);
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::Rental, $rental);
            // these strings are used in xsl templates and trade method
            $bookStatus = array('None','Alert','Inform','Pending', 'Lock', 'CancelByBuyer','CancelBySeller','', 'Rent','RentOut');
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::TradeStatus, $bookStatus[$book['bookstatus']]);
            //die($isbn13."<>".$user_id."<>".$book['bookstatus']."<>".$bookStatus[$book['bookstatus']]);
            // add Message
            if ($_SESSION['anonymous']==FALSE_MYSQL){
                $tradeGateway = new TradeGateway();
                if ($book['bookstatus']==BookStatus::Lock || $book['bookstatus']==BookStatus::CancelByBuyer || $book['bookstatus']==BookStatus::CancelBySeller || $book['bookstatus']==BookStatus::Pending || $book['bookstatus']==BookStatus::RentOut) {
                    if ($book['wishlist']==TRUE_MYSQL){
                        $buyer = $tradeGateway->select_by_buyer($user_id, $isbn13);
                        $bookXML = $amazon->add_textmark_node( $bookXML, Tag::BuyerMessage, $buyer['buyer_message']);
                    }else {
                        $seller = $tradeGateway->select_by_seller($user_id, $isbn13);
                        $bookXML = $amazon->add_textmark_node( $bookXML, Tag::SellerMessage , $seller['seller_message']);                    
                    }                    
                }elseif ($book['bookstatus']==BookStatus::Inform && $book['wishlist']==TRUE_MYSQL){
                    $buyer_message = "You were informed about this offer by email ~ on " . $book['regdate'];
                    $bookXML = $amazon->add_textmark_node( $bookXML, Tag::BuyerMessage, $buyer_message);                    
                }elseif ($book['bookstatus']==BookStatus::Rent){
                    $buyer = $tradeGateway->select_by_buyer($user_id, $isbn13);
                    $bookXML = $amazon->add_textmark_node( $bookXML, Tag::SellerMessage , $buyer['buyer_message']);                    
                }

                // add BuyerConfirmLink
                if ($book['bookstatus']==BookStatus::Lock && $book['wishlist']==TRUE_MYSQL) {
                    $buyer = $tradeGateway->select_by_buyer($user_id, $isbn13);
                    $bookXML = $amazon->add_textmark_node( $bookXML, Tag::BuyerConfirmLink, DOMAIN . "index.php?op=trade_confirmused&token=" . $buyer['token']);                        
                }
            }
        }
        return $bookXML;
    }

    public function add_price( $bookXML ) {
        
        // compute textmark price
        $amazon = new AmazonService;

        $list_price = str_replace('$', '', $amazon->getBookInfo($bookXML,'ListPrice'));
        if ($list_price==0) {
            $list_price = $amazon->getBookInfo($bookXML,'CheggListPrice');
            $bookXML = $amazon->update_listprice($bookXML, $list_price);
        }
        if ($list_price==0) {
            $list_price = str_replace('$', '', $amazon->getBookInfo($bookXML,'AmazonPrice'));
        }
        if ($list_price==0) {
            $list_price = str_replace('$', '', $amazon->getBookInfo($bookXML,'NewPrice'));            
        }
        
        $new_amazon = str_replace('$', '', $amazon->getBookInfo($bookXML,'NewPrice'));
        if ($new_amazon==0) {
            $new_amazon = str_replace('$', '', $amazon->getBookInfo($bookXML,'AmazonPrice'));
        }
        if ($new_amazon==0) {
            $new_amazon = str_replace('$', '', $amazon->getBookInfo($bookXML,'ListPrice'));            
        }

        $used_amazon = str_replace('$', '', $amazon->getBookInfo($bookXML,'UsedPrice'));
        if ($used_amazon==0) {
            $used_amazon = $new_amazon;            
        }
        
        // if we cannot estimate list price, set it as average textbook
        if ($list_price==0) $list_price=100;
        // it's a rare case but has happened! List price has been wrong.
        if ($list_price<$new_amazon) $list_price=$new_amazon;
        
        $buy_amazon = str_replace('$', '', $amazon->getBookInfo($bookXML,'TradeInValue'));
        $priceAfterTradeInAmazon = $used_amazon -$buy_amazon;
        
        if($used_amazon>($buy_amazon+self::PROCESSING_FEE)){
            $margin = (($used_amazon - $buy_amazon) * (1-self::COMMISION))- self::PROCESSING_FEE ;
            $sell_textmark = round($used_amazon - self::WEIGHT * $margin , 2);
            $buy_textmark = round( $buy_amazon + (1-self::WEIGHT) * $margin , 2);
        } else {
            $sell_textmark = round($used_amazon - self::MIN_DIFF , 2);
            $buy_textmark = round($sell_textmark - self::PROCESSING_FEE , 2);
        }
        $priceAfterTradeInTextMark = $sell_textmark -$buy_textmark;
        $youSave = round(100 * (1-($priceAfterTradeInTextMark/$list_price)));

        //TextMark rental
        $rent_market = intval($amazon->getBookInfo($bookXML,'CheggRentPrice'));
        if ($rent_market==0) $rent_market=$priceAfterTradeInAmazon;

        $margin = (($rent_market - $priceAfterTradeInTextMark) * (1-self::COMMISION))- self::PROCESSING_FEE ;
        $rent_textmark = round($rent_market - self::WEIGHT * $margin , 2);
        $rentout_textmark = round( $priceAfterTradeInTextMark + (1-self::WEIGHT) * $margin , 2);
        if ($rent_textmark > $sell_textmark * self::RENT_DIFF){
            $rent_textmark = round($sell_textmark * self::RENT_DIFF , 2);
            $rentout_textmark = round($buy_textmark * self::RENT_DIFF , 2);
        }

        //add textmark price to book info
        $bookXML = $amazon->add_textmark_node( $bookXML, 'UsedPrice', $sell_textmark );
        $bookXML = $amazon->add_textmark_node( $bookXML, 'TradeInValue', $buy_textmark );
        $bookXML = $amazon->add_textmark_node( $bookXML, Tag::PriceAfterTradeIn, $priceAfterTradeInTextMark);
        $bookXML = $amazon->add_textmark_node( $bookXML, Tag::YouSave, "You save " . $youSave . "% of list price." );
        $bookXML = $amazon->add_textmark_node( $bookXML, 'RentPrice', $rent_textmark );
        $bookXML = $amazon->add_textmark_node( $bookXML, 'RentOutValue', $rentout_textmark );
        return $bookXML;
    }
         
    public function add_availability( $bookXML, $user_id) {
        if ($_SESSION['anonymous']==FALSE_MYSQL){
            //find book isbn
            $amazon = new AmazonService;
            $isbn13 = $amazon->getBookInfo($bookXML,'ISBN13');

            if($amazon->getBookInfo($bookXML, Tag::Rental)=="TRUE"){
                $buyerOffer = Offer::Rent;
                $sellerOffer = Offer::RentOut;
            }else{
                $buyerOffer = Offer::Used;
                $sellerOffer = Offer::Buyback;
            }

            //add number_of_buyers (0 or 1)
            $number_of_buyers = $this->traders_exist($isbn13, $user_id, $sellerOffer);
            $bookXML = $amazon->add_textmark_node( $bookXML, "NumberOfTradeIn", $number_of_buyers );

            //add number_of_sellers (0 or 1)
            $number_of_sellers = $this->traders_exist($isbn13, $user_id, $buyerOffer);
            $bookXML = $amazon->add_textmark_node( $bookXML, "NumberOfUsed", $number_of_sellers );
        }    
        return $bookXML;
    }

    public function find_campus_traders( $isbn13, $user_id, $offer) {
        // Who is (trader) wanting the offer?
        // it returns the first trader or nothing
        $other_party_wishlist = ($offer==offer::Used || $offer==offer::Rent)? 'FALSE' : 'TRUE';
        $userbooksGateway = new UserBooksGateway();
        return $userbooksGateway->find_campus($isbn13, $user_id, $other_party_wishlist, $this->isRental($offer));
    }

    public function trade($isbn13, $user_id, $offer, $submitted, $nickname, $cellphone, $payment) {
        $booksService = new BooksService();
        $bookXML = $booksService->findBook($isbn13, $user_id);
        $amazon = new AmazonService;
        $bookStatus = $amazon->getBookInfo($bookXML, 'TextmarkTradeStatus');
        // these strings are used in xsl templates and add_book_status method
        if ($bookStatus=="Pending" || $bookStatus=="Lock" || 
            $bookStatus=="CancelByBuyer" || $bookStatus=="CancelBySeller" || 
            $bookStatus=="Rent"          || $bookStatus=="RentOut" ){
                throw new Exception("Oops!<br/>You have a pending transaction for this book already!");         
        }
        // don't let user buy a book that already owns
        $bookStatus = $amazon->getBookInfo($bookXML, 'WishList');
        if ($bookStatus=="FALSE" && $offer==Offer::Used){
                throw new Exception("You own this book! You've put it on sale.");                     
        }
        // is there any trader for the offer?
        $traders = $this->find_campus_traders($isbn13, $user_id, $offer);
        //currently it shows if there's a trader or not. there's no need to know exact number of traders.
        $number_of_traders = (isset($traders['id_user'])) ? 1 : 0; 

        $bookXML = $amazon->add_textmark_node( $bookXML, Tag::TradersNumber, $number_of_traders );
        // has user put the book in a proper list?
        $wishlist = ($offer == Offer::Buyback || $offer==offer::RentOut) ? "FALSE" : "TRUE";
        $userBooksGateway = new UserBooksGateway();
        $listed = ($userBooksGateway->exist($isbn13, $user_id, $wishlist)===TRUE)? "TRUE" : "FALSE" ;
        $rental = $this->isRental($offer);

        if ($number_of_traders==0){                    
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::BookList, $listed );
            // if buyer was informeed but there's no book when he accepts offer , turn his book status to alert again.
            if ($bookStatus=="Inform")
                $userBooksGateway->insert($isbn13, $user_id, $wishlist, BookStatus::Alert, $rental);                
        }else {
            // if it's not in trader book list, put it
            if (!$listed)
                 $booksService->addBookList($isbn13, $user_id, $wishlist);
            
            $bookXML = $this->update_trade_info($bookXML, $user_id, $offer, $submitted, $nickname, $cellphone, $payment);
            
            if ( $submitted ) {                
                if ($offer == Offer::Buyback || $offer == Offer::RentOut) {
                    $userBooksGateway->insert($isbn13, $user_id, $wishlist, BookStatus::Alert, $rental);                                
                    //email header section
                    $header = $this->create_email_header($bookXML);
                    $this->invite_buyer($isbn13, $traders['id_user'],$header, $rental);
                    $bookXML = $amazon->add_textmark_node( $bookXML, Tag::Confirmation, "TRUE" );
                } else {
                    try{
                        if ($rental==BookTrade::Rental){
                            $buyerPrice = $amazon->getBookInfo($bookXML, Tag::TextmarkRentPrice);          
                            $sellerValue = $amazon->getBookInfo($bookXML, Tag::TextmarkRentOutValue);
                        }else{
                            $buyerPrice = $amazon->getBookInfo($bookXML, Tag::TextmarkUsedPrice);         
                            $sellerValue = $amazon->getBookInfo($bookXML, Tag::TextmarkTradeInValue);
                        }
                        
                        $trade_token = $this->randomString();
                        if ($rental == BookTrade::Rental) $returnDate = $this->calculate_Return_Date();
                        
                        Gateway::start_transaction();
                        // change buyer & seller book to Pending
                        $buyer_id=$user_id;
                        $seller_id=$traders['id_user'];
                        
                        $userBooksGateway->insert($isbn13, $buyer_id, "TRUE", BookStatus::Pending, $rental);
                        $userBooksGateway->insert($isbn13, $seller_id, "FALSE", BookStatus::Pending, $rental);
                        
                        // buyer & seller message
                        $message = "Pending for payment confirmation since " . date("y-m-d h:i:s");
                                
                        //register transaction in database
                        $tradeGateway = new TradeGateway();
                        $trade_id = $tradeGateway->insert($isbn13, $buyer_id, $seller_id, $buyerPrice, $sellerValue, $trade_token, $message, $message, $rental, $returnDate);
                        $tradeLogGateway = new TradeLogGateway();
                        $tradeLogGateway->insert($trade_id, Transaction::Pending);
                        
                        Gateway::commit_transaction();
                    } catch ( Exception $e ) {
                        Gateway::rollback_transaction();
                        throw new Exception($e->getMessage());
                    }
                    
                    $bookXML = $this->make_payment($buyer_id,$payment,$trade_id,$bookXML);
                }
            }
        }
        
        return $bookXML;
    }
    
    public function retry_payment($isbn13, $user_id, $submitted, $nickname, $cellphone, $payment) {
        //find the trade
        $trade = $this->find_trade($isbn13, $user_id, "TRUE");
        $offer = ($trade["rental"]==BookTrade::Rental)? Offer::Rent : Offer::Used;
        $booksService = new BooksService();
        $bookXML = $booksService->findBook($isbn13, $user_id);
        $bookXML = $this->update_trade_info($bookXML, $user_id, $offer, $submitted, $nickname, $cellphone, $payment);

        if ( $submitted ) {
            $amazon = new AmazonService;
            if ($trade["rental"]==BookTrade::Rental){
                $buyerPrice = $amazon->getBookInfo($bookXML, Tag::TextmarkRentPrice);          
                $sellerValue = $amazon->getBookInfo($bookXML, Tag::TextmarkRentOutValue);
            }else{
                $buyerPrice = $amazon->getBookInfo($bookXML, Tag::TextmarkUsedPrice);         
                $sellerValue = $amazon->getBookInfo($bookXML, Tag::TextmarkTradeInValue);
            }

            try{
                Gateway::start_transaction();
                //update amounts in database
                $tradeGateway = new TradeGateway();
                $tradeGateway->update_amounts($trade["id"], $buyerPrice, $sellerValue);
                $tradeLogGateway = new TradeLogGateway();
                $tradeLogGateway->insert($trade["id"], Transaction::Pending);
                Gateway::commit_transaction();
            }catch ( Exception $e ) {
                Gateway::rollback_transaction();
                throw new Exception($e->getMessage());
            }
            
            $bookXML = $this->make_payment($user_id,$payment,$trade["id"],$bookXML);
        }
          
        return $bookXML;
    }

    public function book_delivered( $trade_token) {
        $tradeGateway = new TradeGateway();
        $trade = $tradeGateway->select($trade_token);
        if (!isset($trade['id'])) throw new Exception("Input Data Error.");

        $booksService = new BooksService();
        $bookXML = $booksService->findBook($trade['isbn'], $trade['id_buyer']);
        $this->check_tradeLog($trade['id'], $bookXML, "TRUE"); // if there's a problem, throws an exception
        
        try{
            Gateway::start_transaction();
            // finish trade
            $tradeLogGateway = new TradeLogGateway();
            $tradeLogGateway->insert($trade['id'], Transaction::Deliver);
            // update userbook
            $userBooksGateway = new UserBooksGateway();
            $userBooksGateway->insert( $trade['isbn'] , $trade['id_seller'] , "FALSE", BookStatus::Delivered,$trade["rental"]);
            $userBooksGateway->insert( $trade['isbn'] , $trade['id_buyer'] , "TRUE", BookStatus::Delivered,$trade["rental"]);
            if ($trade["rental"]==BookTrade::Rental){
                $userBooksGateway->insert( $trade['isbn'] , $trade['id_buyer'] , "FALSE", BookStatus::Rent, $trade["rental"]);
                $userBooksGateway->insert( $trade['isbn'] , $trade['id_seller'] , "FALSE", BookStatus::RentOut, $trade["rental"]);                
            }else{
                $userBooksGateway->insert( $trade['isbn'] , $trade['id_buyer'] , "FALSE", BookStatus::None,0);                
            }
            // update accounts
            $accountService = new AccountService;
            $accountService->record_delivery($trade['id'], $trade['id_buyer'], $trade['id_seller'], $trade['amount_seller'], $trade['amount_buyer']);
            Gateway::commit_transaction();
        } catch ( Exception $e ) {
            Gateway::rollback_transaction();
            throw new Exception($e->getMessage());
        }
        
        // send delivery receipt to seller
        $userGatway= new UsersGateway;
        $seller = $userGatway->select_by_id($trade['id_seller']);
        $subject = "Delivery Confirmed";
        $header = $this->create_email_header($bookXML);
        $body =  $header . "<p>Delivery of the book was confirmed.</p>"
                . "<p>The payment of $" . $trade['amount_seller'] 
                . " was transfered to your account. <a href='" . DOMAIN 
                . "index.php?op=user_profile&verified=true'>Click here to view your available balance.</a></p>"
                . "<p>Thank you for using TextMark!</p>";
        $to = $seller['email'];
        $this->send_email($to, $subject, $body);
        
        // buyer confirmation
        return $bookXML;
        
   }
    
    public function update_book_status($isbn13, $user_id, $wishlist, $status, $rental){
        $userBooksGateway = new UserBooksGateway();
        $userBooksGateway->insert($isbn13, $user_id, $wishlist, $status, $rental);
    }

    public function decline_purchase_offer($isbn13, $user_id, $submitted){
        $booksService = new BooksService();
        $bookXML = $booksService->findBook($isbn13, $user_id);
        if ($submitted){
            $amazon = new AmazonService;
            if($amazon->getBookInfo($bookXML, Tag::Rental)=="TRUE"){
                $rental= 1;
                $userOffer = Offer::Used;
                $matchingOffer = Offer::Buyback;
            }else{
                $rental= 0;
                $userOffer = Offer::Rent;
                $matchingOffer = Offer::RentOut;
            }
            
            //add confirmation tag            
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::Confirmation, "TRUE" );       
            //remove book alert
            $this->update_book_status($isbn13, $user_id, "TRUE", BookStatus::None, $rental);
            // is there a book for sale
            $number_of_sellers = $this->traders_exist($isbn13, $user_id, $userOffer);
            // if there is find another buyer
            if ($number_of_sellers>0) {
                $buyers = $this->find_campus_traders($isbn13, $user_id, $matchingOffer);
                if (isset($buyers['id_user'])){
                    $booksService = new BooksService();
                    $email_header = $this->create_email_header($bookXML);
                    $this->invite_buyer($isbn13, $buyers['id_user'], $email_header);
                }
            }
        }
        
        return $bookXML;
    }
    
    public function confirm_mybook_alert($isbn13, $user_id , $submitted, $nickname, $cellphone, $rental){
        $booksService = new BooksService();
        $bookXML = $booksService->findBook($isbn13, $user_id);
        $userOffer = ($rental==TRUE_MYSQL)? Offer::RentOut : Offer::Buyback;

        $bookXML = $this->update_trade_info($bookXML, $user_id, $userOffer, $submitted, $nickname, $cellphone, null);
        if ( $submitted ) {
            $this->update_book_status($isbn13, $user_id, "FALSE", BookStatus::Alert, $rental);
        }
        
        return $bookXML;
    }
    
    public function request_cancel($isbn13, $user_id, $submitted,$reason, $wishlist){
        $booksService = new BooksService();
        $bookXML = $booksService->findBook($isbn13, $user_id);

        if ($submitted){
            //find the trade
            $trade =  $this->find_trade($isbn13, $user_id, $wishlist);
            if($trade['rental']==BookTrade::Rental){
                $userOffer = Offer::RentOut;
                $tradeType = "rental";
            }else{
                $userOffer = Offer::Buyback;
                $tradeType = "sale";
            }
            
            //check trade log:  is it already cancelled by other party?is delivery confirmed?
            $this->check_tradeLog($trade['id'], $bookXML, $wishlist); // if there's a problem, throws an exception
            try{
                Gateway::start_transaction();
                //turn user books status to canceled
                $bookStatus = ($wishlist=="TRUE") ? BookStatus::CancelByBuyer : BookStatus::CancelBySeller;
                $this->update_book_status($isbn13, $trade['id_buyer'], "TRUE", $bookStatus, $trade['rental']);
                $this->update_book_status($isbn13, $trade['id_seller'], "FALSE", $bookStatus, $trade['rental']);

                // insert trade log
                $tradeLogGateway = new TradeLogGateway();
                $transaction= ($wishlist=="TRUE") ? Transaction::CanceledByBuyer : Transaction::CanceledBySeller;
                $tradeLogGateway->insert($trade['id'], $transaction);
                $tradeLogGateway->insert($trade['id'], $reason);
                
                //send an email to the other party
                $userGatway= new UsersGateway;
                $seller = $userGatway->select_by_id($trade['id_seller']);
                $buyer = $userGatway->select_by_id($trade['id_buyer']);
                $nickname= ($wishlist=="TRUE") ? $buyer['nickname'] : $seller['nickname'];
                $other_wishlist= ($wishlist=="TRUE") ? "FALSE" : "TRUE";
                
                $header = $this->create_email_header($bookXML);
                $subject = "Request to Cancel ". ucfirst($tradeType);
                $body = $header . "<p><b>$nickname</b> has requested to cancel the book $tradeType.</p>"
                        . "<p>Please confirm the cancellation or resolve the issue by <a href='" . DOMAIN
                        . "index.php?op=trade_resolvecancel&wishlist=$other_wishlist&isbn=$isbn13&verified=true'>clicking here.</p>"
                        . "<br/><p style='color:grey'>Tip: We highly recommend responding promptly. This makes for a faster, smoother transaction and keeps the conversation moving along. No one likes awkward silences.</p>"
                        . "<p style='color:grey'>Cheers,<br/> <i>TextMark Team.</i></p>";
                $to = ($wishlist=="TRUE") ? $seller['email'] : $buyer['email'];
                $this->send_email($to, $subject, $body);                           
                
                Gateway::commit_transaction();
            } catch ( Exception $e ) {
                Gateway::rollback_transaction();
                throw new Exception($e->getMessage());
            }
        } else {
            $amazon = new AmazonService;
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::WishList, $wishlist );
            return $bookXML;            
        }
    }

    public function undo_cancel($isbn13, $user_id, $wishlist){
        $booksService = new BooksService();
        $bookXML = $booksService->findBook($isbn13, $user_id);

        //find the trade info
        $trade =  $this->find_trade($isbn13, $user_id, $wishlist);

        //check trade log:  is it already cancelled or confirmed by other party?is delivery confirmed?
        $this->check_tradeLog($trade['id'], $bookXML , $wishlist); // if there's a problem, throws an exception

        try{
            Gateway::start_transaction();
            //turn users book status to alert
            $this->update_book_status($isbn13, $trade['id_buyer'], "TRUE", BookStatus::Lock, $trade['rental']);
            $this->update_book_status($isbn13, $trade['id_seller'], "FALSE", BookStatus::Lock, $trade['rental']);

            // insert trade log
            $tradeLogGateway = new TradeLogGateway();
            $tradeLogGateway->insert($trade['id'], Transaction::CancelUndo);

            //send an email to buyer
            $userGatway= new UsersGateway;
            $seller = $userGatway->select_by_id($trade['id_seller']);
            $buyer = $userGatway->select_by_id($trade['id_buyer']);

            $header = $this->create_email_header($bookXML);
            $nickname= ($wishlist=="TRUE") ? $buyer['nickname'] : $seller['nickname'];
            $subject = "Get Your Book As Promised";
            $body = $header . "<p><b>" . $nickname . "</b> has undone cancellation for this book and wants to proceed as promised.</p>"
                         . "<p>Please schedule delivery time.</p>";
            $to = ($wishlist=="TRUE") ? $seller['email'] : $buyer['email'];
            $this->send_email($to, $subject, $body);                           

            Gateway::commit_transaction();
        } catch ( Exception $e ) {
            Gateway::rollback_transaction();
            throw new Exception($e->getMessage());
        }
    }
    
    public function resolve_cancel($isbn13, $user_id, $submitted,$reason, $wishlist){
        $booksService = new BooksService();
        $bookXML = $booksService->findBook($isbn13, $user_id);

        //find the trade
        $trade =  $this->find_trade($isbn13, $user_id, $wishlist);
        if ($submitted){
            //check trade log:  is it already cancelled by other party?is delivery confirmed?
            $this->check_tradeLog($trade['id'], $bookXML , $wishlist, TRUE); // if there's a problem, throws an exception
            try{
                Gateway::start_transaction();
                //turn user book status to alert
                $this->update_book_status($isbn13, $trade['id_buyer'], "TRUE", BookStatus::Alert, $trade['rental']);
                $this->update_book_status($isbn13, $trade['id_seller'], "FALSE", BookStatus::Alert, $trade['rental']);

                // insert trade log
                $tradeLogGateway = new TradeLogGateway();
                $tradeLogGateway->insert($trade['id'], Transaction::CancelConfirmed);
                $tradeLogGateway->insert($trade['id'], $reason);
                
                //refund buyer money
                $accountService = new AccountService;
                $accountService->record_refund($trade['id'], $trade['id_buyer'], $trade['amount_buyer']);

                //send an email to the other party
                $userGatway= new UsersGateway;
                $seller = $userGatway->select_by_id($trade['id_seller']);
                $buyer = $userGatway->select_by_id($trade['id_buyer']);
                
                $nickname= ($wishlist=="TRUE") ? $buyer['nickname'] : $seller['nickname'];
                $header = $this->create_email_header($bookXML);
                $subject = "Cancel Confirmed";
                $body = $header . "<p><b>" . $nickname . "</b> has confirmed cancellation of the book transaction.</p>";
   
                $to = ($wishlist=="TRUE") ? $seller['email'] : $buyer['email'];
                $this->send_email($to, $subject, $body);                           
                
                Gateway::commit_transaction();
            } catch ( Exception $e ) {
                Gateway::rollback_transaction();
                throw new Exception($e->getMessage());
            }
        } else {
            // why other party canceled trade
            $tradeLogGateway = new TradeLogGateway();
            $tradelog = $tradeLogGateway->select($trade['id']);
            foreach ($tradelog as $row) {
                $cancelCode = $row['transaction'];
                if ($cancelCode==Transaction::CanceledForSchedule 
                        || $cancelCode==Transaction::CanceledForDelivery 
                        || $cancelCode==Transaction::CanceledForQuality 
                        || $cancelCode==Transaction::CanceledForPayment 
                        || $cancelCode==Transaction::CanceledForOther) break;
            }
            // add tags
            $amazon = new AmazonService;
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::CancelCode, $cancelCode );
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::WishList, $wishlist );
            return $bookXML;            
        }
    }
    
    public function cancel_payment($isbn13, $user_id){
        //this method can be called by the buyer before paying the book price
        
        //find the trade
        $trade =  $this->find_trade($isbn13, $user_id, "TRUE");
        try{
            Gateway::start_transaction();
            //turn user book status to alert
            $this->update_book_status($isbn13, $trade['id_buyer'], "TRUE", BookStatus::Alert, $trade['rental']);
            $this->update_book_status($isbn13, $trade['id_seller'], "FALSE", BookStatus::Alert, $trade['rental']);

            // insert trade log
            $tradeLogGateway = new TradeLogGateway();
            $tradeLogGateway->insert($trade['id'], Transaction::CancelConfirmed);
            $tradeLogGateway->insert($trade['id'], Transaction::CanceledForPayment);

            Gateway::commit_transaction();
        } catch ( Exception $e ) {
            Gateway::rollback_transaction();
            throw new Exception($e->getMessage());
        }
    }

    public function confirm_buyer_payment($trade_id){
        //this method is called by:
        //1- ipn.php after receiving payment confirmation
        //2- trade method if the buyer available balance is enough and there's no need for payment
        //get trade info
        $tradeGateway = new TradeGateway();
        $trade = $tradeGateway->select_by_id($trade_id);
        $userGatway= new UsersGateway;
        $seller = $userGatway->select_by_id($trade['id_seller']);
        $buyer = $userGatway->select_by_id($trade['id_buyer']);
        $booksService = new BooksService();
        $bookXML = $booksService->findBook($trade['isbn'], $trade['id_seller']);

        //email header section
        $header = $this->create_email_header($bookXML);

        // compose an email to buyer to introduce the other party
        $subject_buyer = "Receive your book";
        $body_buyer = $header . "<p>Thank you for your payment of $" . $trade['amount_buyer'] . ".</p>"
                . "<p>Please make an appointmant with <b>" . $seller['nickname'] . "</b> to receive your book:</p><ul>";
        if ($seller['cellphone']!='') {
            $body_buyer .= "<li>Cellphone: <a href='tel:" . $seller['cellphone'] . "'>"  . $seller['cellphone'] . "</a></li>";
        } 
        $body_buyer .= "<li>Email: <a href='mailto:" . $seller['email'] . "'>" . $seller['email'] . "</a></li></ul>"
                     . "<p>When the book is delivered, <a href='http://textmark.net/"
                     . "index.php?op=trade_confirmused&token=" .$trade['token'] ."&verified=true'>click here to confirm delivery.</a></p>";

        $to_buyer = $buyer['email'];

        // compose buyer message
        $buyer_message = $this->create_message("Seller", $seller, $trade);

        // compose an email to seller to introduce the other party
        $subject_seller = "Deliver your book";
        $body_seller = $header . "<p><b>" . $buyer['nickname'] . "</b> will contact you soon to make an appointment and receive your book.</p>"
                . "<p>If he/she did not contact you within a few hours, you can reach " . $buyer['nickname'] . " through:</p><ul>";
        if ($seller['cellphone']!='') {
                $body_seller .= "<li>Cellphone: <a href='tel:" . $buyer['cellphone'] . "'>" . $buyer['cellphone'] . "</a></li>";
        }
        $body_seller .= "<li>Email: <a href='mailto:" . $buyer['email'] . "'>" . $buyer['email'] . "</a></li></ul>"
                      . "<p>We'll pay you back $" . $trade['amount_seller'] . " as soon as <b>" . $buyer['nickname'] 
                      . "</b> confirms delivery of the book.</p>";
        $to_seller = $seller['email'];

        // compose seller message
        $seller_message = $this->create_message("Buyer", $buyer, $trade);
        
        //register transaction in database
        Gateway::start_transaction();
        $tradeGateway = new TradeGateway();
        $tradeGateway->update($trade_id, $buyer_message, $seller_message);
        $tradeLogGateway = new TradeLogGateway();
        $tradeLogGateway->insert($trade_id, Transaction::Pay);

        // change buyer & seller book to Lock
        $userBooksGateway = new UserBooksGateway();
        $userBooksGateway->insert($trade['isbn'], $trade['id_buyer'], "TRUE", BookStatus::Lock);
        $userBooksGateway->insert($trade['isbn'], $trade['id_seller'], "FALSE", BookStatus::Lock);

        Gateway::commit_transaction();

        //send emails after successful database transactions
        $this->send_email($to_seller, $subject_seller, $body_seller);             
        $this->send_email($to_buyer, $subject_buyer, $body_buyer);                                
    }

    public function buyer_paid($trade_id,$user_id){
        //get trade info
        $tradeGateway = new TradeGateway();
        $trade = $tradeGateway->select_by_id($trade_id);
        
        // security check: if a user wants to play with tradeid in request
        if ($trade['id_buyer']!= $user_id){
            throw new Exception("Access to other users' information is blocked.");            
        }
        
        $userGatway= new UsersGateway;
        $seller = $userGatway->select_by_id($trade['id_seller']);
        $booksService = new BooksService();
        $bookXML = $booksService->findBook($trade['isbn'], $trade['id_seller']);
        //if payment is confirmed, add seller's contact info
        if (is_null($this->unpaid_trade($trade_id))){
            $amazon = new AmazonService;
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::Confirmation, "TRUE" );
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::SellerNickname, $seller['nickname'] );
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::SellerPhone, $seller['cellphone'] );
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::SellerEmail, $seller['email'] );
        }
        
        return $bookXML;
    }
    
    public function unpaid_trade($trade_id){
        // used by ipn.php and TradeService
        $tradeGateway = new TradeGateway();
        $trade = $tradeGateway->find_unpaid($trade_id);
        return isset($trade['id'])? $trade : null;
    }

    private function update_trade_info($bookXML, $user_id, $offer, $submitted, $nickname, $cellphone, $payment){
        $userGatway= new UsersGateway;
        if ( $submitted ) {
            // validate and update nickname & cellphone
            $nickname = $this->validateName($nickname);
            if (is_null($cellphone) || $cellphone==''){
                $cellphone ='';
            }else{
                $cellphone = $this->validatePhoneNumber($cellphone);
            }
            $userGatway->update_contact_info($user_id,$nickname,$cellphone);
        }else {
            $amazon = new AmazonService;
            if ($offer == Offer::Used || $offer == Offer::Rent) {
                if ($offer == Offer::Used){
                    $price = $amazon->getBookInfo($bookXML, Tag::TextmarkUsedPrice);
                    $rental = "FALSE";
                }else{
                    $price = $amazon->getBookInfo($bookXML, Tag::TextmarkRentPrice);
                    $rental = "TRUE";
                }
                $payment = $this->calculate_payment($user_id, $price);
                $bookXML = $amazon->add_textmark_node( $bookXML, Tag::Payment, $payment );
                $bookXML = $amazon->add_textmark_node( $bookXML, Tag::Rental, $rental );
            }

            $user = $userGatway->select_by_id($user_id);
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::Nickname, $user['nickname'] );
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::CellPhone, $user['cellphone'] );
        }
        return $bookXML;
    }
    
    private function make_payment($buyer_id,$payment,$trade_id,$bookXML){
        $amazon = new AmazonService;
        // check price has changed because user waited too long to submit payment
        if ($amazon->getBookInfo($bookXML, Tag::Rental)=="TRUE"){
          $price = $amazon->getBookInfo($bookXML, Tag::TextmarkRentPrice);          
        }else{
          $price = $amazon->getBookInfo($bookXML, Tag::TextmarkUsedPrice);         
        }
        
        $min_payment = $this->calculate_payment($buyer_id, $usedprice);
        // round is used to avoid a PHP bug
        if ( round($payment,2) < round($min_payment,2) ) {
            throw new Exception("Price of the book has changed. Please retry payment.");
        }
        if ($payment>0){
            // send money order to PayPal
            $isbn13 = $amazon->getBookInfo($bookXML, "ISBN13");
            $title = $this->omit_brackets($amazon->getBookInfo($bookXML, "Title"));
            $this->request_PayPal($isbn13, $title, $payment, $trade_id);
        }else {
            // buyer has enough balance. show seller info
            $accountService = new AccountService;
            $accountService->record_payable_unearned($buyer_id, $price, $trade_id);
            $this->confirm_buyer_payment($trade_id);
            $bookXML = $amazon->add_textmark_node( $bookXML, Tag::TradeId, $trade_id );                         
        }
        return $bookXML;
    } 
    
    private function calculate_payment($buyer_id,$price) {
        $accountService = new AccountService;
        $balance = $accountService->payable_balance($buyer_id);
        $payment = $price - $balance; // it can be a negative number
        return $payment;
    }
    
    private function invite_buyer($isbn13, $user_id, $email_header, $rental){
        // send an email to invite buyer to pay
        $userGatway= new UsersGateway;
        $buyer = $userGatway->select_by_id($user_id);
        $subject = "Your book is ready.";
        $body = $email_header . "<p>You're book is ready!</p>"
                . "<p>Please make a payment as soon as possible.</p>"
                . "<p><b><a href='" . DOMAIN ."index.php?op=trade_used&isbn=" 
                . $isbn13 . "&verified=true'>Click here to accept the offer.</a></b></p>"
                . "<p>or</p><p><a href='" . DOMAIN ."index.php?op=trade_decline&isbn=" 
                . $isbn13 . "&verified=true'>Click here to decline the offer.</a></p>"
                . "<br/><p style='color:grey'>Tip: We highly recommend responding promptly. This makes for a faster, smoother transaction and keeps the conversation moving along. No one likes awkward silences.</p>"
                . "<p style='color:grey'>Cheers,<br/> <i>TextMark Team.</i></p>";
        $to = $buyer['email'];
        $this->send_email($to, $subject, $body);

        //change buyer's book status
        $userBooksGateway = new UserBooksGateway();
        $userBooksGateway->insert($isbn13, $buyer['id'], "TRUE", BookStatus::Inform, $rental);
    }
    
    private function create_email_header($bookXML) {
        $amazon = new AmazonService;
        $title = $this->omit_brackets($amazon->getBookInfo($bookXML, "Title"));
        $edition = $amazon->getBookInfo($bookXML, 'Edition') ;
        $header = "<table><tr><td><img height='75' width='60' src='" 
            . $amazon->getBookInfo($bookXML, 'Thumbnail') . "' alt=':'/>" 
            . "</td><td style='padding-left:10px;'><strong>" . $title . "<br/>";
        
        if ($edition!=''){
            $header .= "Edition: $edition" ;
        }
        
        $header .= "</strong></td></tr></table><hr/>";
        return $header;
        
    }
    
    private function create_message($title, $trader, $trade) {
        /* it's not possible to transform \ in XSLT while it's inside quote ''
         *  I use ~ instead of \n and replace it by javascript on client side
         */
        $message =    "$title Contact Information~"
                    . "--------------------------------------~"
                    . "Name:         " . $trader['nickname'] . "~"
                    . "Email:        " . $trader['email'] . "~";
        if ($trader['cellphone']!='') {
            $message .= "Cellphone:  " . $trader['cellphone'] . "~";
        }
        
        $message .= "--------------------------------------~"
                 . "Purchase Date: " . date("y-m-d h:i:s");
        
        if ($trade['rental']==BookTrade::Rental){
            $date = date_create($trade['returndate']);
            $message .= "~Return Date:   " . date_format($date, 'Y-m-d');            
        }
        
        return $message;
    }
    
    private function check_tradeLog($trade_id, $bookXML, $wishlist, $resolve_cancel=FALSE){
        $header = $this->create_email_header($bookXML);
        $tradeLogGateway = new TradeLogGateway();
        $tradelog = $tradeLogGateway->select($trade_id);
  
        foreach ($tradelog as $row) {
            switch ($row['transaction']){
                case Transaction::CancelUndo :
                    break 2;
                case Transaction::Deliver :
                    throw new Exception($header . "<p>The delivery of the book was confirmed on " . $row['regdate'] . ".</p><p>Thank you.</p>");
                case Transaction::CancelConfirmed :
                    throw new Exception($header . "<p>The cancellation request is already confirmed on " . $row['regdate'] . ".</p>");
                case Transaction::CanceledByBuyer :
                    if ($wishlist=="FALSE" && !$resolve_cancel){
                        throw new Exception($header . "<p>The buyer requested to cancel the book purchase on " . $row['regdate'] . ".</p>");
                    }
                    break;
                case Transaction::CanceledBySeller :
                    if ($wishlist=="TRUE" && !$resolve_cancel){
                        throw new Exception($header . "<p>The seller requested to cancel the book sale on " . $row['regdate'] . ".</p>");
                    }
            }
        }
    }
    
    private function find_trade($isbn13, $user_id, $wishlist){
        $tradeGateway = new TradeGateway;
        $trade=null;
        if ($wishlist=="TRUE"){
            $trade =  $tradeGateway->select_by_buyer($user_id, $isbn13);            
        }else{
            $trade =  $tradeGateway->select_by_seller($user_id, $isbn13);
        }
        return $trade;
    }
    
    private function request_PayPal($isbn, $title, $price, $trade_id){
        //PayPal settings https://www.paypal.com/cgi-bin/webscr?cmd=p/pdn/howto_checkout-outside
        $business_email = Email::Service;
        $return_url = "http://textmark.net/index.php?op=trade_paid&tradeid=$trade_id";
        $cancel_url = "http://textmark.net/index.php?op=trade_used&isbn=$isbn";
        $notify_url = "http://textmark.net/ipn.php";
	
        //query string
	$querystring .= "cmd=_xclick&";
        $querystring .= "business=".urlencode($business_email)."&";	
	//$querystring .= "lc=US&";
	$querystring .= "item_name=".urlencode($title)."&";
	$querystring .= "item_number=$isbn&";
	$querystring .= "amount=".urlencode($price)."&";
	$querystring .= "currency_code=USD&";
	//$querystring .= "no_note=0&";
	//$querystring .= "cn=" . urlencode("Please pay now!")."&";
	//$querystring .= "rm=1&";
	//$querystring .= "bn=" . urlencode("PP-BuyNowBF:btn_buynowCC_LG.gif:NonHosted")."&";
	$querystring .= "no_shipping=1&";
	$querystring .= "custom=$trade_id&";
	//$querystring .= "cpp_header_image=". urlencode(stripslashes('http://textmark.net/resource/image/book.png'))."&";
        $querystring .= "return=".urlencode(stripslashes($return_url))."&";
	$querystring .= "cancel_return=".urlencode(stripslashes($cancel_url))."&";
	$querystring .= "notify_url=".urlencode($notify_url);
        // Redirect to paypal
        if (SANDBOX) {
            header('location:https://www.sandbox.paypal.com/cgi-bin/webscr?'.$querystring);     //sandbox           
        }else{
            header('location:https://www.paypal.com/cgi-bin/webscr?'.$querystring);                
        }        
    }
    
    private function traders_exist( $isbn13, $user_id, $offer) {
        // Is there any traders for the offer?
        // find_campus_traders can return max one row
        $traders = $this->find_campus_traders($isbn13, $user_id, $offer);
        return ((isset($traders['id_user'])) ? 1 : 0);
    }
    
    private function isRental($offer) {
        //Is it a rental offer?
        $rental = ($offer==offer::Buyback || $offer==offer::Used) ? 0 : 1;
        return $rental;
    }
    
    private function calculate_Return_Date(){
        // return date of the rented books
        $month = date('m');
        $year = date('Y');
        if ($month<5 || $month==12){
            $returnDate = mktime(0, 0, 0, 5, 21, $year); // end of spring semester
        }elseif ($month>=5 && $month<8){
            $returnDate = mktime(0, 0, 0, 8, 21, $year); // end of summer semester
        }else{
            $returnDate = mktime(0, 0, 0, 12, 21, $year); // end of fall semester           
        }
        return date("Y-m-d",$returnDate);
    }
}
?>