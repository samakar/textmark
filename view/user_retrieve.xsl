<?xml version="1.0" encoding="UTF-8"?>

<!--
    Created on : May 24, 2012, 3:51 PM
    Author     : Amir Samakar
    Description: to email password or confirmation link
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'X'" />

<xsl:template match="/">
<html>
    <head>
        <title>Retreive Password</title>
        <xsl:copy-of select="$analytics-meta-css" />
    </head>
  
    <body>
        <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <h2 class="title">Retreive Password</h2><br/>
                    <xsl:choose>
                        <xsl:when test="count(Root/Confirmation)=0">
                            <form name="join" method="POST" action="">
                                <label for="email" class="form-label">Email:</label>
                                <input type="text" name="email" id="email" maxlength="128" size="40" class="form-textbox">
                                    <xsl:attribute name="value">
                                        <xsl:value-of select="Root/Email" />
                                    </xsl:attribute>
                                </input>
                                <br />
                                <input type="hidden" name="form-submitted" value="1" />
                                <br />
                                <div  class="form-label">&#xA0;</div>
                                <input type="submit" value="Send Password"  class="form-submit-button"/>
                            </form>

                            <xsl:if test="count(Root/Errors) &gt; 0">
                                <hr />
                                <p  class="form-error"><xsl:value-of select="Root/Errors/Er" /></p>
                                <p>Operation failed. Please check your email and try again.</p>
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
