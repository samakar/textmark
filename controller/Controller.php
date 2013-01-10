<?php

abstract class Controller {
        
    protected function redirect($location) {
        header('Location: '.$location);
    }
      
    protected function inform($title, $message) {
        $xml = new DomDocument;
        $xml->loadXML('<Root><Title>' . $title . '</Title><Message>' . $message . '</Message></Root>') ;
        echo $this->xml_to_html($xml,"message.xsl");
    } 
    
    protected function xml_to_html($xml,$xsl_file_name){
        // http://www.tonymarston.net/php-mysql/xsl.html
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load("view/" . $xsl_file_name);

        // import the XSL styelsheet into the XSLT process

        $logged = ($_SESSION['anonymous']==TRUE_MYSQL) ? 'FALSE' : 'TRUE';
        $showcasehide = ($_SESSION['college_id']>0) ? 'FALSE' : 'TRUE';
        
        if (isset($_SESSION['user_name'])) 
            $username =$_SESSION['user_name'];
        else
            $username ='';
        
        $xp = new XsltProcessor();
        $xp->importStylesheet($xsl);
        $xp->setParameter('', 'logged', $logged);
        $xp->setParameter('', 'username', $username);
        $xp->setParameter('', 'collegename', $_SESSION['college_name']);
        $xp->setParameter('', 'collegedomain', $_SESSION['college_domain']);
        $xp->setParameter('', 'showcasehide', $showcasehide);
       
        // transform the XML into HTML using the XSL file
        $html = $xp->transformToXML($xml);
        if ($html!==false) {
            echo $html;
        } else {
            throw new Exception('XSLT Error.');
        }
        die();
    }   
    
    protected function check_privacy($role=UserRole::Student) {
        //error_log("check_privacy:>>>user_name" . $_SESSION['user_name'] . "<UserRole::Admin>" . UserRole::Admin . "<SESSION_role>". $_SESSION['role'] . "<anonymous>" . $_SESSION['anonymous']  . "<role>" . $role  . "<");
        if (($_SESSION['anonymous']==1)){
            $allow = false;
        }else {
            if ($role==UserRole::Student){
                $allow = true;             
            } elseif ( ($role==UserRole::Admin) && ($_SESSION['role']==UserRole::Admin) ) {
                $allow = true;
            } else {
                $allow = false;
            }
        }
        
        if($allow) {
            return true;
        }else {
            $_SESSION['denied_uri']=$_SERVER['REQUEST_URI']; // save it to redirect user after login
            $xml = new DomDocument('1.0');
            // if user is registered go to login page else it's a new user.
            if (isset($_COOKIE['registered'])) {
                $this->xml_to_html($xml,"user_login.xsl");                
            }else{
                $this->xml_to_html($xml,"user_join.xsl");                
            }
            return false;
        }
    }
    
    abstract function handleRequest();

}
?>