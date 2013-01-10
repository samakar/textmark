<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'X'" />

<xsl:template match="/">
<html>
    <head>
        <title><xsl:value-of select="//Textmark/Title" /></title>
        <xsl:copy-of select="$analytics-meta-css" />
    </head>
  
    <body>
         <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <h2 class="title">Your book is not in campus</h2>
   
                    <table id="table-detail">
                        <tr><th colspan="2"><xsl:value-of select="//Textmark/Title" /></th></tr>
                        <tr>
                            <td>
                                <img height='160' width='128' class="frontcover" src="{//MediumImage/URL}" />
                            </td>
                            <td>
                                <p>
                                    Your book is not currently available for sale in campus.
                                    <xsl:choose>
                                        <xsl:when test="//Textmark/BookList='TRUE'">
                                            <xsl:choose>
                                                <xsl:when test="//Textmark/TradeStatus='None' or //Textmark/Rental='TRUE'">
                                                    <br/>Please turn on alert for this book to reserve your place.
                                                    <br/>We inform by email as soon as it's available for purchase.
                                                    <br/>You can buy it soon for <strong>$<xsl:value-of select="//Textmark/UsedPrice" /></strong>.
                                                    <form method="get" action="">
                                                        <input type="hidden" name="op" value="trade_addalert" />
                                                        <input type="hidden" name="rental" value="FALSE" />
                                                        <input type="hidden" name="wishlist" value="TRUE" />
                                                        <input type="hidden" name="isbn" value="{//ItemAttributes/EAN}" />
                                                        <input type="submit" value="Turn On Alert" class="form-submit-button"/>
                                                    </form>
                                                </xsl:when>
                                                <xsl:otherwise>
                                                    <br/>We inform you by email as soon as it's available.
                                                    <br/>You can buy it soon for <strong>$<xsl:value-of select="//Textmark/UsedPrice" /></strong>.
                                                </xsl:otherwise>
                                            </xsl:choose>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <br/>Please add it to your wish list to reserve your place.
                                            <br/>we inform you by email as soon as it's available.
                                            <br/>You can buy it soon for <strong>$<xsl:value-of select="//Textmark/UsedPrice" /></strong>.
                                            <form method="get" action="">
                                                <input type="hidden" name="op" value="trade_addtolist" />
                                                <input type="hidden" name="wishlist" value="TRUE" />
                                                <input type="hidden" name="rental" value="FALSE" />
                                                <input type="hidden" name="isbn"  value="{//ItemAttributes/EAN}" />
                                                <input type="submit" value="Add to Wish List"  class="form-submit-button"/>
                                            </form>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </p>
                                <xsl:if test="//OfferSummary/LowestUsedPrice/FormattedPrice!=''">
                                    <p>Or</p>
                                    <p>Buy from MarketPlace a used book for <strong><xsl:value-of select="//OfferSummary/LowestUsedPrice/FormattedPrice" /></strong>.</p>
                                </xsl:if>    
                                <xsl:if test="//OfferSummary/LowestNewPrice/FormattedPrice!=''">
                                    <p>Or</p>
                                    <p>Buy from MarketPlace a new book for <strong><xsl:value-of select="//OfferSummary/LowestNewPrice/FormattedPrice" /></strong>.</p>
                                </xsl:if>    
                                <xsl:if test="//OfferSummary/LowestNewPrice/FormattedPrice!='' or //OfferSummary/LowestUsedPrice/FormattedPrice!=''">
                                    <p>
                                        <form method="post" target="_blank" action="{//DetailPageURL}">
                                            <input type="submit" value="Buy from MarketPlace"  class="form-submit-button"/>
                                        </form>
                                    </p>
                                </xsl:if>    
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