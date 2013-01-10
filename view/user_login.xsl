<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'X'" />

<xsl:template match="/">
<html xmlns:fb="http://ogp.me/ns/fb#">
    <head>
        <title>Login TextMark</title>
        <script type="text/javascript"><![CDATA[
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
                    <h2 class="title">Login</h2>

                    <div style="width:180px;display:inline-block;vertical-align:top;">
                        <img width="120" height="120" src='resource/image/book.png' style="padding:20px"/>
                    </div>

                    <div style="width:285px;display:inline-block;padding-right:35px;vertical-align:top;">
                        <p style="color: #B58433;font-size:90%;text-align:right">Not a member?<a href='index.php?op=user_join' style="color: #B58433">Join here!</a></p>
                        <br/>
                        <form method="POST" action="index.php?op=user_login">
                            <label for="email">Email:</label>
                            <input type="text" name="email" id="email" maxlength="128" size="40" class="form-textbox">
                                <xsl:attribute name="value">
                                    <xsl:value-of select="Root/Email" />
                                </xsl:attribute>
                            </input>
                            <br />
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" maxlength="40" size="40" class="form-textbox"/>
                            <br />
                            <label for="rememberme">Remember me:</label>
                            <input type="checkbox" name="rememberme" id="rememberme" checked="checked" />
                            <br /><br />
                            <input type="hidden" name="form-submitted" value="1" />
                            <input type="submit" value="Login"  class="form-submit-button"/>
                            <br />
                            <p style="text-align:right"><a href='index.php?op=user_retrieve' style="color: #B58433;font-size:90%">Forgot your password?</a></p>
                        </form>

                        <xsl:if test="count(Root/Errors) &gt; 0">
                            <p class="form-error"><xsl:copy-of select="Root/Errors/Er" /></p>
                            <p>Login failed. Please check your email and password and try again.</p>
                        </xsl:if>

                        <p><xsl:value-of select="Root/Message" /></p>
                    </div>

                    <div style="width:300px;display:inline-block;vertical-align:50px;">
                            <input type="submit" value="Invite Your Friends to TextMark" onclick="FacebookInviteFriends();" class="form-add"/>
                            <p style="color:#b54333;font-size:90%;text-align:left">You save more as more students join us.</p>
                            <br/><br/>
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
