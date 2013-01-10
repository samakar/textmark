<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'model/Enums.php';

abstract class Service {

    protected function send_email($to,$subject, $body){
        if (SANDBOX) {
            $this->send_email_sandbox($to,$subject, $body);
        }else{
            $this->send_email_web($to,$subject, $body);
        }        
    }
    
    protected function send_email_web($to,$subject, $body){
        
        // Send Email from server
        $from = Email::Service;
        
        $body = "<html><body><div style='max-width:400px;min-width:200px;background:#FFFFEA;padding:20px;font-family: Arial, Helvetica, sans-serif;border:3px solid #DDE5AA;border-radius:5px;'>" 
                . "<table><tr><td style='background:#CAA765;font-size:24px;color:#FFFFFF;border:3px solid #DDE5AA;font-weight:bold;padding:15px;;text-align:center'>TextMark</td></tr>" 
                . "<tr><td style='background:#FFFFFF;font-size:14px;color:#8D7518;border:3px solid #DDE5AA;padding:10px;'>$body</td></tr>"
                . "<tr><td style='background:#CAA765;font-size:12px;color:#FFFFFF;border:3px solid #DDE5AA;padding:10px;text-align:center'>Follow us: <a href='http://facebook.com/textmark' style='color:#FFFFFF;text-decoration:none'>facebook.com/textmark</a></td></tr></div></body></html>";

        $headers = 'Content-type: text/html; charset=\"iso-8859-1\"' . "\r\n"; 
        $headers .= 'From: TextMark <' . $from . ">\r\n"; 
        //$headers .= 'Bcc: ' . $from . "\r\n"; 
        $headers .= 'Reply-To: ' . $from . "\r\n"; 
        $headers .= 'Return-Path: ' . $from . "\r\n"; 
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n"; 
        $headers .= 'MIME-Version: 1.0' . "\r\n"; 
        
        if (mail($to,$subject,$body,$headers)==FALSE) {
            error_log("Service.php send_email failed: PHP mail() returned FALSE");
            throw new Exception("ERROR Sending Email: PHP mail() returned FALSE");
        }

        // Make a copy in outbox folder

        if ($stream= imap_open("{mail.textmark.net/novalidate-cert}INBOX.Sent", $from, Email::Password )){
            // Had to do this as server and me are in different time
            $dmy=date("d-M-Y H:i:s O", strtotime('+1 hour'));
            imap_append($stream, "{mail.textmark.net/novalidate-cert}INBOX.Sent"
                            , $headers
                            . "To:$to\r\n"
                            . "Date: $dmy\r\n"
                            . "Subject: $subject\r\n"
                            . "\r\n$body\r\n"
                            );
            if (!imap_close($stream)) error_log("Service.php send_email failed: imap_close returned FALSE");
        }else{
            error_log("Service.php send_email failed: imap_open returned FALSE");
        }        

  }

    protected function validateName($name){
        $name = trim($name);
        $result = preg_match("/^[a-zA-Z]'?[- a-zA-Z]*$/", $name, $matches);
        if ($result){
            return ucfirst($name);
        }else {
            throw new Exception("The name has invalid characters");
        }
    }
        
    protected function validatePhoneNumber($phone){
        $phone = trim($phone);
        $result = preg_match("/^\(?([2-9][0-8][0-9])\)?[ ]*[-.]?[ ]*([2-9][0-9]{2})[ ]*[-.]?[ ]*([0-9]{4})$/", $phone, $matches);
        if ($result){
            return "(" . $matches[1] . ") " . $matches[2]  . "-" . $matches[3];
        }else {
            throw new Exception("The phone number is invalid or in wrong format");
        }
    }
    
    protected function randomString($length = 50){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string = '';    

        for ($p = 0; $p < $length; $p++) {
                $string .= substr($characters,mt_rand(0, strlen($characters)),1);
        }
        return $string;
    }
    
    protected function omit_brackets($title) {
        return current(explode("(", $title));
    }

    protected function send_email_sandbox($to,$subject, $body){
        // local host script
        require_once "Mail.php";

        $host = "smtp.gmail.com";
        $username = "samakar";
        $password = "2949780";
        $from = "TextMark";
        
        $headers = array ('MIME-Version'=> '1.0', 
            'Content-type' => 'text/html; charset=iso-8859-1',
            'From' => $from,
            'To' => $to,
            'Subject' => $subject);
        
        $smtp = Mail::factory('smtp',
            array ('host' => $host,
                'auth' => true,
                'username' => $username,
                'password' => $password));
        $body = "<html><body><div style='max-width:400px;min-width:200px;background:#F4EDD3;padding:20px;font-family: Arial, Helvetica, sans-serif;font-size:14px;color:#8D7518;border:3px solid #EFE5BD;border-radius:15px;box-shadow: 10px 10px 5px #888888;'>" 
                . "<h2>TextMark</h2><br/>" . $body . "</div></body></html>";
        
        $mail = $smtp->send($to, $headers, $body);

        if (PEAR::isError($mail)) {
            throw new Exception($mail->getMessage());
        }
    }

}

?>