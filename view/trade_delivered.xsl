<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'X'" />

<xsl:template match="/">
<html>
    <head>
        <title>Delivery Confirmed: <xsl:value-of select="//Textmark/Title" /></title>
        <xsl:copy-of select="$analytics-meta-css" />
</head>
  
    <body onload="init()">
         <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div  class="post">
                    <h2 class="title">Delivery Confirmed</h2>
                    <table id="table-detail" style="width:450px;margin:0px">
                        <tr><th colspan="2"><xsl:value-of select="//Textmark/Title" /></th></tr>
                        <tr>
                            <td>
                                <img class="frontcover">
                                    <xsl:attribute name="src">
                                        <xsl:value-of select="//MediumImage/URL" />
                                    </xsl:attribute>
                                </img>
                            </td>
                            <td>
                                <p>You have confirmed delivery of the book.</p>
                                <p>Thank you for using TextMark.</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>
</xsl:stylesheet>