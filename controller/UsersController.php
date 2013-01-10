<?php

require_once 'model/UsersService.php';
require_once 'Controller.php';

class UsersController extends Controller{
    
    private $usersService = NULL;
    
    public function __construct() {
        $this->usersService = new UsersService();
    }
        
    public function handleRequest() {
        try {        
            $this->usersService->check_session();
            if (isset($_GET['op'])) 
                $op =$_GET['op'];
            else
                $op ='';
            
            switch ($op){
                case 'user_login':
                    $this->login();
                    break;
                case 'user_join':
                    $this->join();
                    break;
                case 'user_logout':
                    $this->logout();
                    break;
                case 'user_verify':
                    $this->verify();
                    break;
                case 'user_change':
                    $this->change_password();
                    break;
                case 'user_retrieve':
                    $this->retrieve_password();
                    break;
                case 'user_resend_verifi':
                    $this->resend_verification();
                    break;
                case 'user_profile':
                    if ($this->check_privacy()) $this->profile();
                    break;
                case 'user_faq':
                    $this->show_faq();
                    break;
                case 'user_feedback':
                    $this->feedback();
                    break;
            }
        } catch ( Exception $e ) {
            // some unknown Exception got through here, use application error page to display it
            die($e->getMessage());
            $this->inform("Application error", $e->getMessage());
        }
    }
       
    private function login() {
        try{
            if ( isset($_POST['form-submitted']) ) {
                $email = isset($_POST['email']) ? $_POST['email'] :NULL;
                $password = isset($_POST['password']) ? $_POST['password'] :NULL;
                $rememberme = (isset($_POST['rememberme'])) ? TRUE_MYSQL : FALSE_MYSQL;
                $this->usersService->login_user($email, $password, $rememberme);
                $link = ($_SESSION['denied_uri']=='') ? 'index.php' : $_SESSION['denied_uri'];
                $_SESSION['denied_uri']='';
                $this->redirect($link);       
            }else {
                $xml = new DomDocument;
            }    
        } catch (Exception $e) {
            $xml = new DomDocument;
            $xml->loadXML(  '<Root><Email>' . $email . '</Email>' . 
                    '<Errors><Er>' . $e->getMessage() . '</Er></Errors></Root>') ;
        }
        
         $this->xml_to_html($xml,"user_login.xsl");
    }    
    
    private function join() {
        $xml = new DomDocument;
        try{
            if ( isset($_POST['form-submitted']) ) {
                $email = isset($_POST['email']) ? $_POST['email'] :NULL;
                $password = isset($_POST['password']) ? $_POST['password'] :NULL;
                $this->usersService->register_user($email, $password);
                $xml->loadXML(  '<Root><Email>' . $email . '</Email>' . 
                    '<Confirmation>TRUE</Confirmation></Root>') ;
            } 
        } catch (Exception $e) {
            $xml->loadXML(  '<Root><Email>' . $email . '</Email>' . 
                    '<Errors><Er>' . $e->getMessage() . '</Er></Errors></Root>') ;
        }
        
         $this->xml_to_html($xml,"user_join.xsl");
    }    

    private function logout() {
        $this->usersService->logout_user();
        $this->redirect('index.php');       
    }

    private function verify() {
        try{
            $verifi_code = isset($_GET['verifi_code']) ? $_GET['verifi_code'] :'';
            $this->usersService->verify_user($verifi_code);
            $this->inform("Welcome", "Thank you for confirming your account.<br/><a href='index.php?op=user_login'>Please login.</a>.");
        } catch (Exception $e) {
            $this->inform("Verification Failed", $e->getMessage());
        }
   }
    
    private function retrieve_password() {
        $xml = new DomDocument;
        try{
            if ( isset($_POST['form-submitted']) ) {
                $email = isset($_POST['email']) ? $_POST['email'] :NULL;
                $this->usersService->retrieve_password($email);
                $xml->loadXML(  '<Root><Email>' . $email . '</Email>' . 
                    '<Confirmation>Your New Password Has Been Sent To Your Email Address.</Confirmation></Root>') ;
            } 
        } catch (Exception $e) {
            $xml->loadXML(  '<Root><Email>' . $email . '</Email>' . 
                    '<Errors><Er>' . $e->getMessage() . '</Er></Errors></Root>') ;
        }
        
         $this->xml_to_html($xml,"user_retrieve.xsl");
    }

    private function resend_verification() {
        $xml = new DomDocument;
        try{
            if ( isset($_POST['form-submitted']) ) {
                $email = isset($_POST['email']) ? $_POST['email'] :NULL;
                $this->usersService->resend_verification($email);
                $xml->loadXML(  '<Root><Email>' . $email . '</Email>' . 
                    '<Confirmation>Your Confirmation Code Has Been Sent To Your Email Address.</Confirmation></Root>') ;
            } 
        } catch (Exception $e) {
            $xml->loadXML(  '<Root><Email>' . $email . '</Email>' . 
                    '<Errors><Er>' . $e->getMessage() . '</Er></Errors></Root>') ;
        }
        
         $this->xml_to_html($xml,"user_retrieve.xsl");
    }    
        
    private function change_password() {
        $xml = new DomDocument;
        try{
            if ( isset($_POST['form-submitted']) ) {
                $email = isset($_POST['email']) ? $_POST['email'] :NULL;
                $old_password = isset($_POST['old_password']) ? $_POST['old_password'] :NULL;
                $new_password = isset($_POST['password']) ? $_POST['password'] :NULL;
                $this->usersService->change_password($email, $old_password, $new_password);
                $xml->loadXML(  '<Root><Confirmation>Your Password Is Changed Successfully.</Confirmation></Root>') ;
            }   
        } catch (Exception $e) {
            $xml->loadXML(  '<Root><Email>' . $email . '</Email>' . 
                    '<Errors><Er>' . $e->getMessage() . '</Er></Errors></Root>') ;
        }
        
         $this->xml_to_html($xml,"user_password.xsl");
    }    

    private function profile() {
        try{
            if ( isset($_POST['form-submitted']) ) {
                $html = $this->usersService->request_payback();  
                $this->inform("Profile",$html );
            } else {
                $xml = $this->usersService->get_profile_info();
                $this->xml_to_html($xml,"user_profile.xsl");       
            }  
        } catch (Exception $e) {
            $this->inform("Profile Page Failed", $e->getMessage());
        }        
    }

    private function show_faq() {
        try{            
            $xml = new DomDocument;
            $this->xml_to_html($xml,"user_faq.xsl");       
        } catch (Exception $e) {
            $this->inform("Profile Page Failed", $e->getMessage());
        }        
    }

    private function feedback() {
        try{            
            $xml = new DomDocument;
            $this->xml_to_html($xml,"user_feedback.xsl");       
        } catch (Exception $e) {
            $this->inform("Feedback Page Failed", $e->getMessage());
        }        
    } 

 }
?>