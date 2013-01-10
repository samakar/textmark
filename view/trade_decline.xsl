<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:func="http://exslt.org/functions" extension-element-prefixes="func" xmlns:myfunc="my:myfunc">
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:include href="book_detail.xsl"/>
<xsl:variable name="menu" select="'X'" />

<xsl:template match="/">
<html>
    <head>
        <title><xsl:value-of select="myfunc:parentheses(//ItemAttributes/Title)" /></title>
        <xsl:copy-of select="$analytics-meta-css" />
    </head>  
    <body>
         <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <h2 class="title">Decline Offer</h2>
  
                    <table id="table-detail">
                        <tr><th colspan="2"><xsl:value-of select="myfunc:parentheses(//ItemAttributes/Title)" /></th></tr>
                        <tr>
                            <td>
                                <img height='160' width='128' class="frontcover">
                                    <xsl:attribute name="src">
                                        <xsl:value-of select="//MediumImage/URL" />
                                    </xsl:attribute>
                                </img>
                            </td>
                            <td>
                                <xsl:choose>
                                    <xsl:when test="//Textmark/Confirmation='TRUE'">
                                        The alert for this book is turned off. 
                                        <br/>You will not be informed of any offer for this book.
                                    </xsl:when>
                                    <xsl:otherwise>
                                        We sent you an offer for this book because you reserved a place for it.
                                        <br/>Please note that if you decline this offer, you may not be able to buy or rent this book at this great price in future.
                                        <br/><br/>If you still want to decline the offer, press the below button:
                                        <br/><br/>
                                        <form method="post" action="">
                                            <input type="hidden" name="form-submitted" value="1" />
                                            <input type="submit" value="Confirm Decline" class="form-submit-button"/>
                                        </form>
                                    </xsl:otherwise>
                                </xsl:choose>
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