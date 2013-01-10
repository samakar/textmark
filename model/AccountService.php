<?php
require_once 'model/Service.php';
require_once 'gateway/AccountGateway.php';

class AccountService extends Service{

    private $accountGateway = NULL;
    
    //chart of accounts
    const CASH                      = 1;
    const COG                       = 2;
    const REVENUE                   = 3;
    const UNEARNED_REVENUE_START    = 1000000000;
    const ACCOUNT_PAYABLE_START     = 2000000000;
    const PAYBACK_START             = 3000000000;

    public function __construct() {
        $this->accountGateway = new AccountGateway();
    }
    
    public function payable_balance($user_id){
        $account_payable = self::ACCOUNT_PAYABLE_START + $user_id;
        return $this->account_balance($account_payable);
    }   
    
    public function payback_balance($user_id){
        $account_payback = self::PAYBACK_START + $user_id;
        return $this->account_balance($account_payback);
    }

    public function request_payback_user($user_id){
        $balance = $this->payable_balance($user_id);
        if ($balance>0){
            $account_payable = self::ACCOUNT_PAYABLE_START + $user_id;
            $account_payback = self::PAYBACK_START + $user_id;
            $this->accountGateway->insert($account_payable, $account_payback, $balance, Method::None, 0, "");            
        }
        return $balance;
    }

    public function record_payback_paypal($user_id, $amount){
        $account_payback = self::PAYBACK_START + $user_id;
        $this->accountGateway->insert($account_payback, self::CASH, $amount, Method::PayPal, 0, "");            
    }
  
    public function record_payback_unearned($buyer_id, $amount, $trade_id){
        $account_payback = self::PAYBACK_START + $buyer_id;
        $unearned_revenue = self::UNEARNED_REVENUE_START + $buyer_id;
        $this->accountGateway->insert($unearned_revenue, $account_payback, $amount, Method::None, $trade_id, "");
    }
    
    public function record_delivery($trade_id,$buyer_id, $seller_id, $purchase_amount, $sale_amount){
        $unearned_revenue = self::UNEARNED_REVENUE_START + $buyer_id;
        $account_payable = self::ACCOUNT_PAYABLE_START + $seller_id;
        $this->accountGateway->insert(self::COG, $account_payable, $purchase_amount, Method::None, $trade_id, "");
        $this->accountGateway->insert($unearned_revenue, self::REVENUE, $sale_amount, Method::None, $trade_id, "");        
    }
 
    public function record_refund($trade_id, $buyer_id, $sale_amount){
        $unearned_revenue = self::UNEARNED_REVENUE_START + $buyer_id;
        $account_payable = self::ACCOUNT_PAYABLE_START + $buyer_id;
        $this->accountGateway->insert($unearned_revenue,$account_payable , $sale_amount, Method::None, $trade_id, "");        
        $balance = $this->payable_balance($buyer_id);
        $this->notify_refund($buyer_id, $sale_amount, $balance);
  }

    public function process_paypal_payment($trade_id, $buyer_id, $price, $payment, $txn){
        // Ensure the transaction is not a duplicate.
        if ($this->payment_txn_exist(Method::PayPal, $txn)){
            throw new Exception("'txn_id' has already been processed: $txn");
        }
        // user accounts
        $account_payable = self::ACCOUNT_PAYABLE_START + $buyer_id;
        $unearned_revenue = self::UNEARNED_REVENUE_START + $buyer_id;
        
        $balance = $this->payable_balance($buyer_id);
        $remainder = $price - $payment;
        if ( $balance < $remainder){
            $this->accountGateway->insert(self::CASH, $account_payable, $payment, Method::PayPal, 0, $txn);
            $balance = $balance + $payment; //new balance after payment
            $this->notify_underpayment($buyer_id, $payment, $balance);
            throw new Exception("$$payment was received. But user's current balance is not sufficient: $$balance");
        }else{
            // we assume that payment is never greater than price
            $this->accountGateway->insert(self::CASH, $unearned_revenue, $payment, Method::PayPal, $trade_id, $txn);
            if ($remainder>0){
                $this->record_payable_unearned($buyer_id, $remainder, $trade_id);
            }
        }
    }

    public function record_payable_unearned($buyer_id, $amount, $trade_id){
        $account_payable = self::ACCOUNT_PAYABLE_START + $buyer_id;
        $unearned_revenue = self::UNEARNED_REVENUE_START + $buyer_id;
        $this->accountGateway->insert($account_payable, $unearned_revenue, $amount, Method::None, $trade_id, '');
    }

    private function payment_txn_exist($method, $txn){
        $account = $this->accountGateway->select_by_txn($method, $txn);
        return isset($account['id']);
    }
    
    private function notify_underpayment($buyer_id, $payment, $balance){
        require_once 'gateway/UsersGateway.php';
        $userGatway= new UsersGateway;
        $buyer = $userGatway->select_by_id($buyer_id);

        $subject_buyer = "Underpayment Alert";
        $body_buyer = "<p>Thank you for your payment of $$payment</p>"
                . "<p>Your available balance is <b>$$balance</b>. Unfortunately it is insufficient to cover the book price</p>"
                . "<p><a href='http://textmark.net/index.php?op=trade_retrypayment&isbn=" 
                . $trade['isbn'] . "'>Please retry payment.</a></p>";
        $to_buyer = $buyer['email'];
        
        $this->send_email($to_buyer, $subject_buyer, $body_buyer);       
    }
    
    private function notify_refund($buyer_id, $payment, $balance){
        require_once 'gateway/UsersGateway.php';
        $userGatway= new UsersGateway;
        $buyer = $userGatway->select_by_id($buyer_id);

        $subject_buyer = "Refund Alert";
        $body_buyer = "<p>Your payment of $$payment was totally refunded.</p>"
                . "<p>Your available balance is <b>$$balance</b>.</p>" 
                . "<p><a href='" . DOMAIN . "index.php?op=user_profile&verified=true'>Click here to view your available balance.</a></p>";
        $to_buyer = $buyer['email'];
        
        $this->send_email($to_buyer, $subject_buyer, $body_buyer);       
    }

    private function account_balance($account){
        $debit = $this->accountGateway->total_debit($account);
        $credit = $this->accountGateway->total_credit($account);
        return ($credit - $debit);
    }  
}
?>