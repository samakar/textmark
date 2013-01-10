<?xml version="1.0" encoding="UTF-8"?>

<!--
    Document   : contact-form.xsl
    Created on : May 24, 2012, 3:51 PM
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
        <title>Change Password</title>
        <style type="text/css">
            .form-label{ display:inline-block;float:left;width:130px;padding-right:5px;text-align:right;}
        </style>
        <script type="text/javascript"><![CDATA[
            function validateForm() {
                var email =document.forms["change"]["email"].value;
                var old_password =document.forms["change"]["old_password"].value;
                var password =document.forms["change"]["password"].value;
                var retype_password =document.forms["change"]["retype_password"].value;
                var error = '';
                if (email==null || email==""){
                    error = "Email must be filled out.\n";
                }
                if (old_password==null || old_password==""){
                    error += "Current password must be filled out.\n";
                }
                if (password==null || password==""){
                    error += "New password must be filled out.\n";
                }
                if (password!=retype_password){
                    error += "New passwords don't match.";
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
                <div class="post">
                    <h2 class="title">Change Password</h2>
                    <xsl:choose>
                        <xsl:when test="count(Root/Confirmation)=0">
                            <form name="change" method="POST" action="" onsubmit="return validateForm()">
                                <label for="email" class="form-label">email:</label>
                                <input type="text" name="email" id="email" maxlength="128" size="40" class="form-textbox">
                                    <xsl:attribute name="value">
                                        <xsl:value-of select="Root/Email" />
                                    </xsl:attribute>
                                </input>
                                <br />
                                <label for="old_password" class="form-label">Current password:</label>
                                <input type="password" name="old_password" id="old_password" maxlength="40" size="40" class="form-textbox"/>
                                <br />
                                <label for="password" class="form-label">New password:</label>
                                <input type="password" name="password" id="password" maxlength="40" size="40" class="form-textbox"/>
                                <br />
                                <label for="retype_password" class="form-label">Retype new password:</label>
                                <input type="password" name="retype_password" id="retype_password" maxlength="128" size="40" class="form-textbox"/>
                                <br />
                                <input type="hidden" name="form-submitted" value="1" />
                                <br /><hr />
                                <input type="submit" value="Change"  class="form-submit-button"/>
                            </form>

                            <xsl:if test="count(Root/Errors) &gt; 0">
                                <hr />
                                <p  class="form-error"><xsl:value-of select="Root/Errors/Er" /></p>
                                <p>Operation failed. Please check your email and password and try again.</p>
                            </xsl:if>
                        </xsl:when>
                        <xsl:otherwise>
                            <p><xsl:value-of select="Root/Confirmation" /></p>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>
</xsl:stylesheet>
