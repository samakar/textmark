<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'X'" />

<xsl:template match="/Root">
 <html>
    <head>
        <title><xsl:value-of select="Title" /></title>
        <xsl:copy-of select="$analytics-meta-css" />
   </head>
    <body>
        <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <h2 class="title"><xsl:value-of select="Title" /></h2>
                    <div style="width:400px;"><xsl:copy-of select="Message" /></div>
                    <br/>
                    <input type="image" src='resource/image/back.png' title="Back" onclick="javascript: window.history.back()"/>
                </div>
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>
</xsl:stylesheet>
