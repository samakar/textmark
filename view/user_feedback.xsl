<?xml version="1.0" encoding="UTF-8"?>

<!--
    Created on : May 24, 2012, 3:51 PM
    Author     : Amir Samakar
    Description: to email password or confirmation link
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'N'" />

<xsl:template match="/">
<html xmlns:fb="http://ogp.me/ns/fb#">
    <head>
        <title>Feedback</title>
        <xsl:copy-of select="$analytics-meta-css" />
    </head>
  
    <body>
        <xsl:copy-of select="$fbSDK" />

        <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <h2 class="title">Feedback: News and Comments</h2>
                    <p><em>We appreciate your taking time to send us your thoughts.  We do use your comments to improve TextMark.</em></p>
                    <div style="width:470px;display:inline-block;padding-right:20px;vertical-align:top">
                        <fb:comments href="http://textmark.net" num_posts="3" width="450"></fb:comments>
                    </div>

                    <div style="width:300px;display:inline-block;vertical-align:top">
                        <fb:like-box href="http://www.facebook.com/textmark" width="300" height="500" show_faces="true" stream="true" header="true"></fb:like-box>
                    </div>
                </div>
            </div>
        </div>
        
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>
</xsl:stylesheet>
