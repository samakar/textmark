<?php
/*
 * http://www.dreamincode.net/forums/topic/247188-user-authentication-class/
 * and open the template in the editor.
 */

require_once 'gateway/UsersGateway.php';
require_once 'gateway/LoggedUsersGateway.php';
require_once 'gateway/UserHistoryGateway.php';
require_once 'gateway/CollegeGateway.php';
require_once 'model/Service.php';
require_once 'model/AccountService.php';
require_once 'lib/Whois.php';

class UsersService extends Service{
    private $_siteKey;
    private $usersGateway = NULL;
    private $loggedUsersGateway = NULL;
    
    public function __construct(){
        $this->siteKey = 'gsdyfgoisuvchogisudfgoiuegpgioudtsgfiodsunbdsgn50';
        $this->usersGateway = new UsersGateway();
        $this->loggedUsersGateway = new LoggedUsersGateway();
        session_start();
    }
    
    public function register_user($email, $password){
        $this->validate_email($email);
        $this->validate_password($password);

        // make sure there's no verified user with this email already in db
        $user = $this->usersGateway->select_by_email($email);
        if (isset($user['email'])){
            if ($user['is_verified']==1){
                throw new Exception('An account with this email already exists.');
            }
        }

        //Salt and Hash the password
        $user_salt = $this->randomString();
        $password = $user_salt . $password;
        $password = $this->hashData($password);

        //Create and send verification code
        $verifi_code = $this->randomString();
        $this->send_verifi_code($email, $verifi_code);
        //get college id and set its cookie
        $college_id = $this->find_college_id($email);
        $this->set_tracking_cookies($college_id);

        //Commit values to database here. 
        $user_id = $this->usersGateway->insert(date("y-m-d h:i:s"));
        $count = $this->usersGateway->update_newuser($user_id, $email, $password, $user_salt, $verifi_code, $college_id);
        if ($count==0){
            throw new Exception('Registration failed. Please try again.');
        }
        $this->set_register_cookie();
    }
    
    public function login_user($email, $password, $rememberme){
        $this->validate_email($email);
        $this->validate_password($password);
       //Select users row from database base on $email
        $user = $this->usersGateway->select_by_email($email);
       
       // make sure there's a user with this email already in db
        if (!isset($user['email'])){
            throw new Exception("An account with this email does not exist.");
        }

        //Salt and hash password for checking
        $password = $user['user_salt'] . $password;
        $password = $this->hashData($password);

        // we don't need the anonymous user anymore
        //$this->usersGateway->delete($_SESSION['user_id']);
        //$this->loggedUsersGateway->delete($_SESSION['user_id']);

        //Check email and password hash match database row
        if($password == $user['password']) {            
            if($user['is_verified']==1) {
                    //Email&Password combination exists, set sessions
                    $this->set_register_cookie();
                    // remember me
                    $user['rememberme'] = $rememberme;
                    if ($rememberme==TRUE_MYSQL) {
                        $lifetime = 30 * 24 * 60 * 60 + time();//one month
                    } else {
                        $lifetime = time() - 30*24*3600; //kill
                    }
                    setcookie('user',$user['id'], $lifetime);
                    
                    $this->set_session($user['id'], $user);
            } else {
                throw new Exception("Your account is not verfied yet.<br/> You can ask for confirmation code <a href='index.php?op=user_resend_verifi'>here</a>");
            }
        }else {
            throw new Exception('The password is not correct.');
        }
    }

    public function logout_user(){
        //Delete old logged_in_member records for user
        if (isset($_SESSION['user_id'])) {
            $this->loggedUsersGateway->delete($_SESSION['user_id']);
        }
        // delete 'user' tracking cookie
        $lifetime = time() - 30*24*3600 ;//one month ago
        setcookie('user','', $lifetime);
        session_destroy();
    }

    public function verify_user($verifi_code){
        //Select user row from database base on $verifi_code
        $count = $this->usersGateway->update_isverified($verifi_code);
        if ($count==0){
            throw new Exception('The verification code is wrong.');
        }
        // if user has accidentally created multiple unverified accounts with same email, delete them.
        //$this->usersGateway->delete_unverified($verifi_code);
   }

    public function change_password($email, $old_password, $new_password){
        $this->validate_password($new_password);
        $user = $this->usersGateway->select_by_email($email);
        // make sure there's a user with this email already in db
        if (!isset($user['email'])){
            throw new Exception("An account with this email does not exist.");
        }

        //Salt and hash passwords for checking
        $old_password = $user['user_salt'] . $old_password;
        $old_password = $this->hashData($old_password);

        $new_password = $user['user_salt'] . $new_password;
        $new_password = $this->hashData($new_password);

        if($old_password == $user['password']) {            

            $count = $this->usersGateway->update_password($user['id'], $new_password);
            if ($count==0){
                throw new Exception('Operation error. Please try again.');
            }
        }else {
            throw new Exception('The old password is not correct.');
        }

   }

    public function check_session(){
        //this is the 1st function called for every page visit
        $user =  $this->loggedUsersGateway->select(session_id());
        if (isset($user['user_id'])){           
            //if (is_hacker($user)) $this->set_session($this->createNewUser()); else
            //$this->set_session($user['user_id'], $user);
        } else {
            // create anonymous user
            $this->set_session($this->createNewUser());
        }
        $this->save_history();
    }

    private function set_session($user_id,$user = null){
//        echo($user_id . " set_session");
     
        if (is_null($user)){          
            $token = '';
            $_SESSION['anonymous'] = 1;
            $rememberme=1;
            $id_college=0;
        //error_log("set_session-1-" . session_id() . "<>" . $user_id . "<>" . $_SESSION['anonymous']  . "<>" .$rememberme);
        } else {
            //First, generate a random string.
            $random = $this->randomString();
            //Build the token
            $token = $_SERVER['HTTP_USER_AGENT'] . $random;
            $token = $this->hashData($token);
            $_SESSION['token'] = $token;

            $_SESSION['user_name'] = ($user['nickname']=='')? $user['email'] : $user['nickname'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['anonymous'] = 0;
            $rememberme = $user['rememberme'];// check if it's used at all
            $id_college = $user['id_college'];
            
            //insert new record for user
            $this->loggedUsersGateway->delete($user_id);
            $inserted = $this->loggedUsersGateway->insert( $user_id, session_id(), $token, $rememberme);
            //if database falls
            if($inserted == FALSE) {
                session_destroy();
                throw new Exception('Set session failed.Please login again.');
            }

            //error_log("set_session-2-" . session_id() . "<>" . $user_id . "<>" . $_SESSION['anonymous']  . "<>" .$rememberme);
        }

//        if (is_null($user)) echo("is_null(user)"); 
//        else { die("not_null(user)".$_SESSION['user_name']);}

        //Setup public sessions vars
        $_SESSION['user_id'] = $user_id;
        $this->find_college($id_college);
        $this->set_tracking_cookies($_SESSION['college_id']);
    }
    
    public function retrieve_password($email){
        $this->validate_email($email);
        //Select users row from database base on $email
        $user = $this->usersGateway->select_by_email($email);
       
        // make sure there's a user with this email in db
        if (!isset($user['email'])){
            throw new Exception("An account with this email does not exist.");
        }

        //Salt and hash a new password
        $new_password = $this->randomString(8);
        $encripted_password = $this->hashData($user['user_salt'] . $new_password);

        //send new password
        $subject = "Your TextMark Account";
        $body = "Please <a href='" . DOMAIN ."index.php?op=user_login'>login</a> with your new password.<br/>Your new password:  " . $new_password;      
        $this->send_email($email, $subject, $body);
        
        $count = $this->usersGateway->update_password($user['id'], $encripted_password);
        if ($count==0){
            throw new Exception('Operation error. Please try again.');
        }
    }

    public function resend_verification($email){
        $this->validate_email($email);
        //Select users row from database base on $email
        $user = $this->usersGateway->select_by_email($email);
       
        // make sure there's a user with this email in db
        if (!isset($user['email'])){
            throw new Exception("An account with this email does not exist.");
        }
        //send verification code
        $this->send_verifi_code($email, $user['verification_code']);
    }

    public function get_profile_info(){
        $user = $this->usersGateway->select_by_id($_SESSION['user_id']);
        $accountService = new AccountService;
        $payable = $accountService->payable_balance($_SESSION['user_id']);
        $xml_string = "<Root><Name>" . $user['nickname'] . "</Name><Email>" . $user['email'] 
                . "</Email><CellPhone>" . $user['cellphone'] . "</CellPhone><Date>" 
                . $user['regdate'] . "</Date><Payable>" . $payable . "</Payable></Root>";
        $xml = new DomDocument;
        $xml->loadXML($xml_string);
        return $xml;
    }
        
    public function request_payback(){
        $user_id = $_SESSION['user_id'];
        $accountService = new AccountService;
        $payback = $accountService->request_payback_user($user_id);
        if ($payback>0){
            $html = "<p>$$payback will be sent to you through PayPal within 24 hours.</p><p>We will inform you by email when transaction is done.</p>";
            $this->send_email(Email::Accountant, "Payback", 
                    "<a href='" . DOMAIN . "index.php?op=admin_payback&receiver=$user_id'>Payback $$payback to user #$user_id</a>");             
        }else{
            $html = "<p>There's no money in your account to pay back.</p>";
        }
        return $html;
    }
       
    private function set_tracking_cookies($college_id, $user_id=null){
        $lifetime = 365 * 24 * 60 * 60 + time();//one year
        // 'user' is used for tracking
        if (is_null($user_id)) $user_id = $_SESSION['user_id'];
        setcookie('user',$user_id, $lifetime);

        // 'college' is used for template and showcase view
        setcookie('college',$college_id, $lifetime);
    }
    
    private function set_register_cookie(){
        // 'registered' is used to decide between login or join view
        $lifetime = 365 * 24 * 60 * 60 + time();//one year
        setcookie('registered',date("G:i - m/d/y"), $lifetime);           
    }

    private function hashData($data){
        return hash_hmac('sha512', $data, $this->_siteKey);
    }
            
    private function send_verifi_code($to, $verifi_code){
        $subject = "Welcome to TextMark";
        
        $body = "<p>You're almost done!</p><p><b><a href='" . DOMAIN ."index.php?op=user_verify&verifi_code=" 
                . $verifi_code . "'>Click here to complete your registration on textmark.net</a></b><br/>Textmark team.<p>";
            
        $this->send_email($to, $subject, $body);
    }

    private function validate_email($email){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            throw new Exception('Invalid Email Address.');
        }
        if (strtolower(substr($email, -4))!='.edu'){
            throw new Exception("Invalid Email Address. Only '.edu' domains are acceptable.");
        }         
    }

    private function validate_password($password){
        if (strlen($password) <6 || strlen($password)>40){
             throw new Exception('Invalid password.Password must be between 6 to 40 characters');
        }
    }
    
    private function save_history(){
        if (isset($_POST['ISBN'])){
            $isbn = $_POST['ISBN'];
        }else {
            $isbn = (isset($_GET['isbn'])) ? $_GET['isbn'] : NULL ;  
        }
        $op = isset($_GET['op'])?$_GET['op']:'book_find';
        
        $userHistoryGateway = new UserHistoryGateway;
        $userHistoryGateway->insert($_SESSION['user_id'], $op, $isbn, $_SESSION['anonymous'] );
    }

    private function find_college_id($email){
        $email_list = explode('@' , $email);
        $domain_list = explode('.', $email_list[1]);
        $domain_name = strtolower($domain_list[count($domain_list)-2]);
        $domain =  $domain_name . ".edu";
        
        $collegeGateway = new CollegeGateway;
        $college = $collegeGateway->select($domain);
        
        if (isset($college['name'])){
            $college_id = $college['id'];
            // if we have not got name from Whois because of time out, try again
            if ($college['name']==''){
                $whois = new Whois();
                $college_name = $whois->domain_registrant($domain);
                $collegeGateway->update_name($college_id, $college_name);   
            }
        }else{
            // if college is not registered, register it
            $whois = new Whois();
            $college_name = $whois->domain_registrant($domain);
            $college_id = $collegeGateway->insert($college_name, $domain);
        }
        
        return $college_id;
    }
    
    private function find_college($id_college){
        if ($id_college==0){
            if(isset($_COOKIE['college'])) $id_college = $_COOKIE['college']; 
        }
        
        $_SESSION['college_id']= $id_college; // it's only used in showcase(). showcase in not shown if id_college=0.

        if ($id_college==0){
            // we may predict by ip?
            $_SESSION['college_name']= "Buy Smart, Save Green!";
            $_SESSION['college_domain']= "textmark.net";
        }else{
            // get college info
            $collegeGateway = new CollegeGateway;
            $college = $collegeGateway->select_by_id($id_college);
            $_SESSION['college_name']= $college['name'];
            $_SESSION['college_domain']= $college['domain'];
        }
    }

    private function createNewUser(){      
        // if user has visited textmark before, use their past id
        if(isset($_COOKIE['user']))
            $user_id = $_COOKIE['user']; 
        else
            $user_id = 0;//$this->usersGateway->insert(date("y-m-d h:i:s"));

        // if college is known in an ad link, set cookie
        if (isset($_GET['collegeid'])){
            $college_id = $_GET['collegeid'];            
            $this->set_tracking_cookies($college_id, $user_id);
            $this->find_college($college_id);
        }

        return $user_id;
    }
    
    private function is_hacker($user){
        return false;
        /* work on security kickout issue later
            * http://www.wikihow.com/Create-a-Secure-Login-Script-in-PHP-and-MySQL
        // session_regenerate_id does not destroy session data associated with previus UID
        // So if an intuder with old or correct random user UID enters, he will pass the above test.
        //error_log("check_session-1-" . session_id());
        session_regenerate_id();
        if ($user['anonymous']==1){
                    //error_log("check_session-2-" . session_id());
            $this->set_session($user['user_id']);
        } elseif (isset($_SESSION['user_id'])){
            //error_log("check_session-3-" . session_id());
            // An intuder with old or correct random UID will pass the above test if user has not logged out
            //Check ID and Token
            if($_SESSION['user_id'] == $user['user_id'] && $_SESSION['token']==$user['token']) {
                // An intuder with old or random UID will pass the above test if user has not visited a new page yet
                // I don't know why a token is needed. I think it was created to prevent someone from inside read the tables and enter as a user
                //If uid and token match, refresh the session for the next request
                $this->set_session($user['user_id'], $user);
            }else {
                // create anonymous user
                //error_log("check_session-4-" . session_id());
                $this->createNewUser();
            }
        } elseif ($user['rememberme']==1){
            $this->set_session($user['user_id'], $user);
        } else {
            //error_log("check_session-5-" . session_id());
            $this->loggedUsersGateway->delete($user['user_id']);            
            $this->createNewUser();
        }
        */       
    }

 }
?>