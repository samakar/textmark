<?php

require_once 'Controller.php';
require_once 'model/TradeService.php';
require_once 'model/AmazonService.php';

class TradeController extends Controller{
    
    private $user_id = NULL;       
    private $tradeService = NULL;       

    public function __construct() {
        $this->user_id = $_SESSION['user_id']; // set by UserControler during call by index.php
        $this->tradeService = new TradeService;
    }
        
    public function handleRequest() {
        
        $op = isset($_GET['op'])?$_GET['op']:'';
        try {
            switch ($op){
                case 'trade_used':
                    if ($this->check_privacy())$this->trade(offer::Used);
                    break;
                case 'trade_buyback':
                    if ($this->check_privacy()) $this->trade(offer::Buyback);
                    break;
                case 'trade_rent':
                    if ($this->check_privacy()) $this->trade(offer::Rent);
                    break;
                case 'trade_rentout':
                    if ($this->check_privacy()) $this->trade(offer::RentOut);
                    break;
                case 'trade_confirmused':
                    $this->book_delivered();
                    break;
                case 'trade_paid':
                    if ($this->check_privacy()) $this->buyer_paid();
                    break;
                case 'trade_addalert':
                    if ($this->check_privacy()) $this->update_alert(BookStatus::Alert);
                    break;
                case 'trade_deletealert':
                    if ($this->check_privacy()) $this->update_alert(BookStatus::None);
                    break;
                case 'trade_addtolist':
                    if ($this->check_privacy()) $this->addBookList();
                    break;
                case 'trade_decline':
                    if ($this->check_privacy()) $this->decline_offer();
                    break;
                case 'trade_confirmalert':
                    if ($this->check_privacy()) $this->confirm_alert();
                    break;
                case 'trade_cancel':
                    if ($this->check_privacy()) $this->cancel_trade();
                    break;
                case 'trade_undocancel':
                    if ($this->check_privacy()) $this->undo_cancel_trade();
                    break;
                case 'trade_resolvecancel':
                    if ($this->check_privacy()) $this->resolve_cancel_trade();
                    break;
                case 'trade_cancelpayment':
                    if ($this->check_privacy()) $this->cancel_payment();
                    break;
                case 'trade_retrypayment':
                    if ($this->check_privacy()) $this->retry_payment();
                    break;
                case 'trade_return':
                    //if ($this->check_privacy()) ; to do later
                    break;
            }
        } catch ( Exception $e ) {
            // some unknown Exception got through here, use application error page to display it
            $this->inform("Application Error", $e->getMessage());
        }
    }
      
    private function trade($offer) { 
        try {
            if (isset($_POST['isbn'])){
                $isbn13 = $_POST['isbn'];
            }else {
                $isbn13 = (isset($_GET['isbn'])) ? $_GET['isbn'] : NULL ;  
            }
            if (!$isbn13) throw new Exception('Input Error.');   
            $submitted = isset($_POST['form-submitted']);
            $nickname = isset($_POST['name']) ? $_POST['name'] :NULL;
            $cellphone = isset($_POST['cellphone']) ? $_POST['cellphone'] :NULL;
            $payment = isset($_POST['payment']) ? $_POST['payment'] :NULL;
            if ($submitted && $nickname==NULL) throw new Exception('Input Data Error.');
            //*                     End of Input Validation                             */
            
            $bookXML = $this->tradeService->trade($isbn13, $this->user_id , $offer, $submitted, $nickname, $cellphone, $payment);
            $amazon = new AmazonService;
            if ($payment<=0 && $submitted && ($offer==Offer::Used || $offer==Offer::Rent) ) {
                $trade_id = $amazon->getBookInfo($bookXML, Tag::TradeId);
                $this->redirect("index.php?op=trade_paid&tradeid=$trade_id");
            }else {
                $number_of_traders = $amazon->getBookInfo($bookXML, "TradersNumber");

                if ($number_of_traders==0){
                    switch ($offer){
                        case offer::Buyback:
                            $xsl_file_name = "trade_buyback.xsl";
                            break;
                        case offer::Used:
                            $this->redirect("index.php?op=trade_addalert&rental=false&wishlist=TRUE&isbn=$isbn13");
                            break;
                        case Offer::Rent:
                            $xsl_file_name = "trade_rent.xsl";
                            break;
                        case Offer::RentOut:
                            $xsl_file_name = "trade_rentout.xsl";
                    }    
                }else {
                    $xsl_file_name = ($offer==Offer::Used || $offer==Offer::Rent) ? "trade_pay.xsl" : "trade_ownerconfirm.xsl";
                }

                $this->xml_to_html($bookXML,$xsl_file_name);
            }
        } catch ( Exception $e ) {
            $this->inform("Oops!", $e->getMessage());
        }
    }

    private function retry_payment() { 
        try {
            if (isset($_POST['isbn'])){
                $isbn13 = $_POST['isbn'];
            }else {
                $isbn13 = (isset($_GET['isbn'])) ? $_GET['isbn'] : NULL ;  
            }
            if (!$isbn13) throw new Exception('Input Error.');   
            $submitted = isset($_POST['form-submitted']);
            $nickname = isset($_POST['name']) ? $_POST['name'] :NULL;
            $cellphone = isset($_POST['cellphone']) ? $_POST['cellphone'] :NULL;
            $payment = isset($_POST['payment']) ? $_POST['payment'] :NULL;
            if ($submitted && $nickname==NULL ) throw new Exception('Input Data Error.');
            //*                     End of Input Validation                             */
        
            $bookXML = $this->tradeService->retry_payment($isbn13, $this->user_id, $submitted, $nickname, $cellphone, $payment);           
            $this->xml_to_html($bookXML,"trade_pay.xsl");
            
        } catch ( Exception $e ) {
            $this->inform("Transaction Failed", $e->getMessage());
        }
    }

    private function book_delivered() { 
        try {
            $token = isset($_GET['token'])?$_GET['token'] : NULL;
            if (!$token) throw new Exception('Input Error.');   
            //*                     End of Input Validation                             */

            $xml = $this->tradeService->book_delivered($token);
            $this->xml_to_html($xml,"trade_delivered.xsl");           
        } catch ( Exception $e ) {
            $this->inform("TextMark Message", $e->getMessage());
        }
    }

    private function buyer_paid() { 
        try {
            $trade_id = isset($_GET['tradeid'])?$_GET['tradeid'] : NULL;
            if (!$trade_id) throw new Exception('Input Data Error.');   
            //*                     End of Input Validation                             */

            $xml = $this->tradeService->buyer_paid($trade_id,$this->user_id);
            $this->xml_to_html($xml,"trade_paid.xsl");
        } catch ( Exception $e ) {
            $this->inform("TextMark Message", $e->getMessage());
        }
    }

    private function update_alert($alert) { 
        try {
            $wishlist = isset($_GET['wishlist'])? $_GET['wishlist'] : NULL;
            $isbn13 = isset($_GET['isbn'])? $_GET['isbn'] : NULL;
            $rentalStatus = isset($_GET['rental'])? $_GET['rental'] : NULL;
            if ( !$wishlist || !$isbn13 || !$rentalStatus) throw new Exception('Input Error.');
            //*                     End of Input Validation                                    */

            $rental = ($rentalStatus=='TRUE')? TRUE_MYSQL : FALSE_MYSQL;
            if ($alert==BookStatus::None){
                $this->tradeService->update_book_status($isbn13, $this->user_id, $wishlist, $alert, $rental);
                $this->redirect('index.php?op=book_show&wishlist=' . $wishlist);       
            }else {
                if ($rentalStatus=='TRUE'){
                    $offer = ($wishlist=="TRUE") ? offer::Rent : offer::RentOut;
                }else{
                    $offer = ($wishlist=="TRUE") ? offer::Used : offer::Buyback;
                }
                
                //is there any matching trader?
                $traders = $this->tradeService->find_campus_traders($isbn13, $this->user_id, $offer);
                if (isset($traders['id_user'])){
                    // if there's a matching trader proceed to finish trade.
                    $this->tradeService->update_book_status($isbn13, $this->user_id, $wishlist, $alert, $rental);
                    if ($rentalStatus=='TRUE'){
                        $op = ($wishlist=="TRUE") ? "trade_rent" : "trade_rentout";
                    }else{
                        $op = ($wishlist=="TRUE") ? "trade_used" : "trade_buyback";
                    }
                    $this->redirect("index.php?op=$op&isbn=$isbn13");                    
                }else {
                    if ($wishlist=="TRUE"){
                        $this->tradeService->update_book_status($isbn13, $this->user_id, $wishlist, $alert, $rental);
                        $this->redirect("index.php?op=book_show&wishlist=$wishlist");                        
                    } else {
                        $this->redirect("index.php?op=trade_confirmalert&isbn=$isbn13&wishlist=$wishlist&rental=$rentalStatus");                        
                    }
                }
            }         
        } catch ( Exception $e ) {
            $this->inform("Application Error", $e->getMessage());
        }
    }

    private function addBookList() {  
        $wishlist = isset($_GET['wishlist'])?$_GET['wishlist']:NULL;
        $isbn13 = isset($_GET['isbn'])?$_GET['isbn'] : NULL;
        $rentalStatus = isset($_GET['rental'])? $_GET['rental'] : NULL;
        if ( !$wishlist || !$isbn13 || !$rentalStatus) throw new Exception('Input Error.');
        //*                     End of Input Validation                                    */

        $rental = ($rentalStatus=='TRUE')? TRUE_MYSQL : FALSE_MYSQL;
        $booksService = new BooksService();
        $booksService->addBookList( $isbn13, $this->user_id, $wishlist);
        $this->tradeService->update_book_status($isbn13, $this->user_id, $wishlist, BookStatus::Alert, $rental);
        $this->redirect('index.php?op=book_show&wishlist=' . $wishlist);       
    }

    private function decline_offer() {  
        $isbn13 = isset($_GET['isbn'])?$_GET['isbn'] : NULL;
        if (!$isbn13) throw new Exception('Input Error.');
        //*                     End of Input Validation                             */
        
        $submitted = isset($_POST['form-submitted']);
        $xml = $this->tradeService->decline_purchase_offer($isbn13, $this->user_id, $submitted);
        $this->xml_to_html($xml,"trade_decline.xsl");
    }
    
    private function confirm_alert() { 
        try {
            $wishlist = isset($_GET['wishlist'])? $_GET['wishlist'] : NULL;
            $isbn13 = isset($_GET['isbn'])? $_GET['isbn'] : NULL;
            $rentalStatus = isset($_GET['rental'])? $_GET['rental'] : NULL;
            if ( !$wishlist || !$isbn13 || !$rentalStatus) throw new Exception('Input Error.');
            $submitted = isset($_POST['form-submitted']);
            $cellphone = isset($_POST['cellphone']) ? $_POST['cellphone'] :NULL;
            $nickname = isset($_POST['name']) ? $_POST['name'] :NULL;
            if ($submitted && $nickname==NULL ) throw new Exception('Input Data Error.');
            //*                     End of Input Validation                             */
            
            $rental = ($rentalStatus=='TRUE')? TRUE_MYSQL : FALSE_MYSQL;          
            $bookXML = $this->tradeService->confirm_mybook_alert($isbn13, $this->user_id , $submitted, $nickname, $cellphone, $rental);
            if ($submitted){
                $this->redirect('index.php?op=book_show&wishlist=FALSE');                
            }else {
                $this->xml_to_html($bookXML,"trade_ownerconfirm.xsl");
            }
        } catch ( Exception $e ) {
            $this->inform("Application Error", $e->getMessage());
        }
    }
    
    private function cancel_trade() { 
        try {
            $wishlist = isset($_GET['wishlist'])? $_GET['wishlist'] : NULL;
            $isbn13 = isset($_GET['isbn'])? $_GET['isbn'] : NULL;
            if ( !$wishlist || !$isbn13) throw new Exception('Input Data Error.');
            $submitted = isset($_POST['form-submitted']);
            $reason = isset($_POST['reason']) ? $_POST['reason'] :NULL;
            if ($submitted && $reason==NULL) throw new Exception('Input Data Error.');
            //*                     End of Input Validation                             */
           
            $bookXML = $this->tradeService->request_cancel($isbn13, $this->user_id, $submitted,$reason, $wishlist);

            if ($submitted){
                $this->redirect('index.php?op=book_show&wishlist=' . $wishlist);                
            }else {
                $this->xml_to_html($bookXML,"trade_cancel.xsl");
            }
        } catch ( Exception $e ) {
            $this->inform("Cancellation Denied", $e->getMessage());
        }
    }
    
    private function cancel_payment() { 
        try {
            $wishlist = isset($_GET['wishlist'])? $_GET['wishlist'] : NULL;
            $isbn13 = isset($_GET['isbn'])? $_GET['isbn'] : NULL;
            if ( !$wishlist || !$isbn13) throw new Exception('Input Data Error.');
            //*                     End of Input Validation                             */
            
            $this->tradeService->cancel_payment($isbn13, $this->user_id);
            $this->redirect('index.php?op=book_show&wishlist=' . $wishlist);                
        } catch ( Exception $e ) {
            $this->inform("Cancellation Denied", $e->getMessage());
        }
    }

    private function undo_cancel_trade() { 
        try {
            $wishlist = isset($_GET['wishlist'])? $_GET['wishlist'] : NULL;
            $isbn13 = isset($_GET['isbn'])? $_GET['isbn'] : NULL;
            if ( !$wishlist || !$isbn13) throw new Exception('Input Data Error.');
            //*                     End of Input Validation                             */
           
            $this->tradeService->undo_cancel($isbn13, $this->user_id, $wishlist);
            $this->redirect('index.php?op=book_show&wishlist=' . $wishlist);  
        } catch ( Exception $e ) {
            $this->inform("Cancellation Undo Was Denied", $e->getMessage());
        }
    }

    private function resolve_cancel_trade() { 
        try {
            $wishlist = isset($_GET['wishlist'])? $_GET['wishlist'] : NULL;
            $isbn13 = isset($_GET['isbn'])? $_GET['isbn'] : NULL;
            $submitted = isset($_POST['form-submitted']);
            $reason = isset($_POST['reason']) ? $_POST['reason'] :NULL;
            if ( !$wishlist || !$isbn13) throw new Exception('Input Data Error.');
            if ($submitted && $reason==NULL) throw new Exception('Input Data Error.');
            //*                     End of Input Validation                             */

            $bookXML = $this->tradeService->resolve_cancel($isbn13, $this->user_id, $submitted, $reason, $wishlist);

            if ($submitted){
                $this->redirect('index.php?op=book_show&wishlist=' . $wishlist);                
            }else {
                $this->xml_to_html($bookXML,"trade_resolvecancel.xsl");
            }
        } catch ( Exception $e ) {
            $this->inform("Cancel Confirmation Denied", $e->getMessage());
        }
    }
    
}

?>