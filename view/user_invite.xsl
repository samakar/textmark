<?xml version="1.0" encoding="UTF-8"?>

<!--
    Document   : trade_invite.xsl
    Created on : June 9, 2012, 9:51 PM
    Author     : Amir Samakar
    Description:
        Purpose of transformation follows.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'X'" />

<xsl:template match="/">
<html>
    <head>
        <title>Please join or login</title>
        <style>
            .form-label{ display:inline-block;float:left;width:130px;padding-right:5px;text-align:right;}
        </style>
        <script type="text/javascript"><![CDATA[
            function validateForm() {
                var email =document.forms["register"]["email"].value;
                var password =document.forms["register"]["password"].value;
                var retype_password =document.forms["join"]["retype_password"].value;
                var error = '';
                if (email==null || email==""){
                    error = "Email must be filled out.\n";
                }
                if (password==null || password==""){
                    error += "Password must be filled out.\n";
                }
                if (password!=retype_password){
                    error += "Passwords don't match.";
                }
                if (error!='') {
                    alert(error);
                    return false;
                }
            }
        ]]></script>    
        <xsl:copy-of select="$analytics-meta-css" />
    </head>
  
    <body>
         <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <h2 class="title">Welcome!</h2>
                <div class="post">
                <div style="width:250px;display:inline-block;vertical-align:bottom">
                    <p style="color: #b54333">It is free and easy to join!<br/> Just enter your college email and a password.</p>
                </div>
                <div style="width:250px;display:inline-block;vertical-align:bottom">
                     <p style="color: #b54333">Already a member?</p>
                </div>
                <br/>
                <div style="width:250px;display:inline-block;vertical-align:top">
                    <h2 class="title">Join</h2>
                    <form id="register" method="POST" action="index.php?op=user_join" onsubmit="return validateForm()">
                        <label for="email">College email:</label>
                        <input type="text" name="email" id="email" maxlength="128" size="30" class="form-textbox" />
                        <br />
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" maxlength="40" size="30" class="form-textbox" />
                        <br />
                        <label for="retype_password">Retype password:</label>
                        <input type="password" name="retype_password" id="retype_password" maxlength="128" size="30" class="form-textbox"/>
                        <br /><br />
                        <input type="hidden" name="form-submitted" value="1" />
                        <input type="submit" value="Join"  class="form-submit-button"/>
                    </form>
                </div>

                <div style="width:250px;display:inline-block;vertical-align:top">
                    <h2 class="title">Login</h2>
                   <form method="POST" action="index.php?op=user_login">
                        <label for="email">College email:</label>
                        <input type="text" name="email" id="email" maxlength="128" size="30" class="form-textbox" />
                        <br />
                        <label for="password" >password:</label>
                        <input type="password" name="password" id="password" maxlength="40" size="30" class="form-textbox"/>
                        <br />
                        <label for="rememberme" >Remember me:</label>
                        <input type="checkbox" name="rememberme" id="rememberme" value="TRUE" />
                        <br /><br /><br />
                        <input type="hidden" name="form-submitted" value="1" />
                        <input type="submit" value="Login"  class="form-submit-button"/>
                    </form>
                </div>

                <div style="width:250px;display:inline-block;vertical-align:50px;">
                    <iframe src="//www.facebook.com/plugins/facepile.php?href=http%3A%2F%2Fwww.facebook.com%2Ftextmark&amp;action&amp;size=medium&amp;max_rows=4&amp;width=300&amp;colorscheme=light" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:250px;" allowTransparency="true"></iframe>
                </div>
                </div>
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>
</xsl:stylesheet>
