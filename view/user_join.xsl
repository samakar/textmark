<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'X'" />

<xsl:template match="/">
<html xmlns:fb="http://ogp.me/ns/fb#">
    <head>
        <title>Join TextMark</title>
        <script type="text/javascript"><![CDATA[
            function validateForm() {
                var email =document.forms["register"]["email"].value;
                var password =document.forms["register"]["password"].value;
                var password2 =document.forms["register"]["password2"].value;
                var error = '';
                if (email==null || email==""){
                    error = "Email must be filled out.\n";
                }
                if (password==null || password==""){
                    error += "Password must be filled out.\n";
                }
                if (password!==password2){
                    error += "Passwords don't match.";
                }
                if (error!='') {
                    alert(error);
                    return false;
                }
            }
            // FB functions
            
            FB.init({ 
            appId:'167262410055462', cookie:true, 
            status:true, xfbml:true 
            });

            function FacebookInviteFriends()
            {
            FB.ui({ method: 'apprequests', 
            message: "Join TextMark to save big bucks on textbooks. It's easy and free!"});
            }
        ]]></script>
        <xsl:copy-of select="$analytics-meta-css" />
   </head>
  
    <body>
        <xsl:copy-of select="$fbSDK" />
        <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    
                    <xsl:choose>
                        <xsl:when test="Root/Confirmation='TRUE'">
                            <h2 class="title">Welcome!</h2>
                        </xsl:when>
                        <xsl:otherwise>
                            <h2 class="title"><em>Join TextMark to Get Best Offers in Your Campus</em>!</h2>
                            <br/>
                        </xsl:otherwise>
                    </xsl:choose>
                    
                    <div style="width:160px;display:inline-block;padding-right:30px;vertical-align:top">
                        <xsl:choose>
                            <xsl:when test="Root/Confirmation='TRUE'">
                            </xsl:when>
                            <xsl:otherwise>
                                <p style="color:#b54333;font-size:90%;text-align:center">It's free and easy to join!
                                <br/>Just enter your college email and a password.</p>
                            </xsl:otherwise>
                        </xsl:choose>
                        <img width="120" height="120" src='resource/image/book.png' style="padding:20px"/>
                    </div>

                    <div style="width:290px;display:inline-block;padding-right:35px;vertical-align:top">
                        <xsl:choose>
                            <xsl:when test="Root/Confirmation='TRUE'">
                                <p style="color:#b54333;">The registration email was sent to <b><xsl:value-of select="Root/Email" /></b></p>
                                <p style="color:#b54333;">Check your email for the link to complete registration.</p>
                                <p style="color:#b54333;">If you don't see this email in your inbox within 15 minutes, look for it in your junk-mail folder. 
                                If you find it there, please mark the email as Not Junk and add @textmark.net to your address book.</p>
                            </xsl:when>
                            <xsl:otherwise>
                                <p style="color: #B58433;font-size:90%;text-align:right">Already a member?<a href='index.php?op=user_login' style="color: #B58433"> Login here!</a></p>
                                <br/>
                                <form name="register" method="POST" action="" onsubmit="return validateForm()">
                                    <label for="email">College email:</label>
                                    <input type="text" name="email" id="email" maxlength="128" size="40" class="form-textbox" title="you@yourcollege.edu">
                                        <xsl:attribute name="value">
                                            <xsl:value-of select="Root/Email" />
                                        </xsl:attribute>
                                    </input>
                                    <br />
                                    <label for="password">Password:</label>
                                    <input type="password" name="password" id="password" maxlength="40" size="40" class="form-textbox" title="Min. 6 letters or numbers."/>
                                    <br />
                                    <label for="password2">Retype Password:</label>
                                    <input type="password" name="password2" id="password2" maxlength="128" size="40" class="form-textbox"/>
                                    <br />
                                    <input type="hidden" name="form-submitted" value="1" />
                                    <input type="submit" value="Join"  class="form-submit-button"/>
                                </form>
                                <xsl:if test="count(Root/Errors) &gt; 0">
                                    <hr />
                                    <p  class="form-error"><xsl:value-of select="Root/Errors/Er" /></p>
                                    <p>Oops!<br/>Registration failed. Please check your email and password and try again.</p>
                                </xsl:if>
                            </xsl:otherwise>
                        </xsl:choose>
                    </div>

                    <div style="width:280px;display:inline-block;">
                        <xsl:if test="Root/Confirmation='TRUE'">
                            <input type="submit" value="Invite Your Friends to TextMark" onclick="FacebookInviteFriends();" class="form-submit-button" />
                            <p style="color:#b54333;font-size:90%;text-align:left">You save more as more students join us.</p>
                            <br/><br/>
                        </xsl:if>
                        <iframe src="//www.facebook.com/plugins/facepile.php?href=http%3A%2F%2Fwww.facebook.com%2Ftextmark&amp;action&amp;size=medium&amp;max_rows=4&amp;width=300&amp;colorscheme=light" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:300px;" allowTransparency="true"></iframe>
                    </div>
                </div>
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>
</xsl:stylesheet>
