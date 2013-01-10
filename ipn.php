<?php
/*
ipn.php - example code used for the tutorial:
PayPal IPN with PHP
How To Implement an Instant Payment Notification listener script in PHP
http://www.micahcarrick.com/paypal-ipn-with-php.html
http://www.micahcarrick.com/code/paypal-ipn-with-php/ipn.php.txt
(c) 2011 - Micah Carrick
*/

try {


    require_once('lib/ipnlistener.php');
    require_once('model/AccountService.php');
    require_once('model/TradeService.php');
    
    // constants that are used in gateways
    define("TRUE_MYSQL", 1);
    define("FALSE_MYSQL", 0);
    
    // for test
    mail(Email::Archive, 'PayPal IPN Received', "received");

    // tell PHP to log errors to ipn_errors.log in this directory
    //ini_set('log_errors', true);
    //ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');

    // intantiate the IPN listener
    $listener = new IpnListener();

    // tell the IPN listener to use the PayPal test sandbox
    $listener->use_sandbox = false;

    // try to process the IPN POST
    $listener->requirePostMethod();
    $verified = $listener->processIpn();

    mail(Email::Archive, 'PayPal IPN Report', $listener->getTextReport());

    if (!$verified)
        throw new Exception("IPN was not verified by PayPal");
       
    $accountService = new AccountService;
    $tradeService = new TradeService;
    $trade_id = null;
    $payment = $_POST['mc_gross'];

    // 1. Make sure the payment status is "Completed" 
    // simply ignore any IPN that is not completed
    if ($_POST['payment_status'] != 'Completed') 
        die;

    // 2. Make sure we received an unpaid trade_id
    if (isset($_POST['custom'])) { 
        // check it exists
        $trade_id = $_POST['custom'];
        $trade = $tradeService->unpaid_trade($trade_id);
        if (is_null($trade)) {
            throw new Exception("trade already paid or does not exist: " . $_POST['custom']);
        }
    }else {
        throw new Exception("trade id was not received.");         
    }

    // 3. Make sure seller email matches textmark primary account email.
    if ($_POST['receiver_email'] != Email::Service)
        throw new Exception("receiver_email does not match:" . $_POST['receiver_email']);

    // 4. Make sure the currency code matches
    if ($_POST['mc_currency'] != 'USD') 
        throw new Exception("currency does not match: " . $_POST['mc_currency']);  
   
    // 5. Make sure the the transaction is not a duplicate and amount(s) paid is enough. if not throw an exception
    $accountService->process_paypal_payment($trade_id, $trade['id_buyer'], $trade['amount_buyer'], $payment, $_POST['txn_id']);
    
    // now that everything is ok, send emails and update trade database (no webpage is shown)
    $tradeService->confirm_buyer_payment($trade_id);

} catch (Exception $e) {
    error_log("ipn.php failed: " . $e->getMessage());
    // manually investigate errors
    $body = "IPN failed checks: \n" . $e->getMessage() . "\n\n";
    $body .= $listener->getTextReport();
    mail(Email::Admin, 'PayPal IPN Warning', $body);
}
?>
