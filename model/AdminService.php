<?php
require_once 'model/Service.php';
require_once 'model/AccountService.php';
require_once 'gateway/UsersGateway.php';

class AdminService extends Service{
    
    public function payback($receiver_id, $submitted){
        $userGatway= new UsersGateway;
        $receiver = $userGatway->select_by_id($receiver_id);
        $accountService = new AccountService;
        $payback = $accountService->payback_balance($receiver_id);
        if ($submitted) {
            //update accounts
            $accountService->record_payback_paypal($receiver_id, $payback);
            $payback=0;
            $confirmed="TRUE";
        }else{
            $confirmed="FALSE";
        }
        $xml_string = "<Root><Payback>$payback</Payback><Name>" . $receiver['nickname'] 
                . "</Name><Email>" . $receiver['email'] . "</Email><Confirmation>$confirmed</Confirmation></Root>";
        $xml = new DomDocument;
        $xml->loadXML($xml_string);
        return $xml;            
    }
}

?>