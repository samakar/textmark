<?php

require_once 'Controller.php';
require_once 'model/AdminService.php';

class AdminController extends Controller{
    
    private $adminService = NULL;

    public function __construct() {
        $this->adminService = new AdminService();
    }
        
    public function handleRequest() {
  
        $op = isset($_GET['op'])?$_GET['op']:'';
        try {
            switch ($op){
                case 'admin_payback':
                    if ($this->check_privacy(UserRole::Admin)) $this->payback();
                    break;
            }
        } catch ( Exception $e ) {
            // some unknown Exception got through here, use application error page to display it
            $this->inform("Application Error", $e->getMessage());
        }
    }
       
    private function payback() {
        try{
            $receiver_id = (isset($_GET['receiver'])) ? $_GET['receiver'] : NULL ;
            if (!$receiver_id) throw new Exception('Input Data Error');
            $submitted = isset($_POST['form-submitted']);
            
            $xml = $this->adminService->payback($receiver_id,$submitted);
            $this->xml_to_html($xml,"admin_payback.xsl");
        } catch (Exception $e) {
            $this->inform("Payback Failed", $e->getMessage());
        }        
    }    
  
}    
?>