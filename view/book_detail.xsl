<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:template name="book_detail" match="ItemLookupResponse/Items/Item">
<xsl:param name="show_buttons" select="'yes'" />
<xsl:param name="wishlist" select="'TRUE'" />

<!--               Book Trade Detail            -->

<xsl:variable name="trade-detail">
    <div class="answer">
        <xsl:if test="Textmark/UsedPrice!=''">
            <div class="price-block">
                <p><span class="save-title">TextMark </span><span> $<xsl:value-of select="Textmark/UsedPrice"/></span></p>
                <xsl:if test="ItemAttributes/ListPrice/FormattedPrice!=''">
                    <p><span class="save-title" title="New">Amazon </span><span style="text-decoration:line-through"><xsl:value-of select="//OfferListing/Price/FormattedPrice"/></span></p>
                    <p>
                        <xsl:if test="//OfferListing/Price/Amount div 100 &gt; Textmark/UsedPrice">
                            <span class="save-title">You save </span>
                            <span>$<xsl:value-of select="format-number(//OfferListing/Price/Amount div 100 - Textmark/UsedPrice, '0.00')"/></span>
                            <span> ( %<xsl:value-of select="format-number(100*(//OfferListing/Price/Amount - Textmark/UsedPrice * 100) div //OfferListing/Price/Amount, '00')"/> )</span>
                        </xsl:if>
                    </p>
                </xsl:if>
                <xsl:choose>
                    <xsl:when test="Textmark/WishList='TRUE' or Textmark/WishList='FALSE'">
                        <input type="submit" value="Buy for ${Textmark/UsedPrice}"  class="form-fozen" style="width:160px" disabled="true"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:choose>
                            <xsl:when test="Textmark/NumberOfUsed=0">
                                <form method="get" action="">
                                    <input type="hidden" name="op" value="trade_addtolist" />
                                    <input type="hidden" name="wishlist" value="TRUE" />
                                    <input type="hidden" name="rental" value="FALSE" />
                                    <input type="hidden" name="isbn" value="{//ItemAttributes/EAN}" />
                                    <input type="submit" value="Reserve for ${Textmark/UsedPrice}"  class="form-submit-button" style="width:160px"/>
                                </form>
                            </xsl:when>
                            <xsl:otherwise>
                                <form method="get" action="">
                                    <input type="hidden" name="op" value="trade_used" />
                                    <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                    <input type="submit" value="Buy Now for ${Textmark/UsedPrice}" class="form-submit-button" style="width:160px"/>
                                </form>              
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:otherwise>
                </xsl:choose>
                <br/>
           </div>
        </xsl:if>
        
        <xsl:if test="Textmark/TradeInValue!=''">
                <div class="save-block">
                    <p><span class="save-title">TextMark </span><span> $<xsl:value-of select="Textmark/TradeInValue"/></span></p>
                    <xsl:choose>
                        <xsl:when test="ItemAttributes/TradeInValue/Amount!=''">
                            <p><span class="save-title" title="Shipment Included">Amazon</span><span style="text-decoration:line-through"><xsl:value-of select="ItemAttributes/TradeInValue/FormattedPrice" /></span></p>
                            <p>
                                <span class="save-title">You gain </span>
                                <span>$<xsl:value-of select="format-number(Textmark/TradeInValue - ItemAttributes/TradeInValue/Amount div 100, '0.00')"/></span>
                                <span> ( %<xsl:value-of select="format-number(100* 100 * (Textmark/TradeInValue - ItemAttributes/TradeInValue/Amount  div 100) div ItemAttributes/TradeInValue/Amount, '00')"/> )</span>
                            </p>
                        </xsl:when>
                        <xsl:otherwise>
                            <p><span class="price-title">Amazon</span><span style="text-decoration:line-through;text-align:left">$0</span></p>
                            <p>
                                <span class="save-title">You gain </span>
                                <span>$<xsl:value-of select="Textmark/TradeInValue"/></span>
                            </p>
                        </xsl:otherwise>
                    </xsl:choose>
                    
                    <xsl:choose>
                        <xsl:when test="Textmark/WishList='TRUE' or Textmark/WishList='FALSE'">
                            <input type="submit" value="Sell for ${Textmark/TradeInValue}"  class="form-fozen" style="width:160px" disabled="disabled"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:choose>
                                <xsl:when test="Textmark/NumberOfTradeIn=0">
                                    <form method="get" action="">
                                        <input type="hidden" name="op" value="trade_addtolist" />
                                        <input type="hidden" name="wishlist" value="FALSE" />
                                        <input type="hidden" name="rental" value="FALSE" />
                                        <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                        <input type="submit" value="Put on Sale for ${Textmark/TradeInValue}"  class="form-submit-button" style="width:160px"/>
                                    </form>
                                </xsl:when>
                                <xsl:otherwise>
                                    <form method="get" action="">
                                        <input type="hidden" name="op" value="trade_buyback" />
                                        <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                        <input type="submit" value="Sell Now for ${Textmark/TradeInValue}" class="form-submit-button" style="width:160px"/>
                                    </form>
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:otherwise>
                    </xsl:choose>
                    <br/>
                </div>
        </xsl:if>
        <xsl:choose>
            
        <xsl:when test="Textmark/WishList='TRUE'">
            <br/>
            <p>This book is in your wishlist.</p>
        </xsl:when>
        <xsl:when test="Textmark/WishList='FALSE'">
                <br/>
            <p>You've put this book on sale.</p>
        </xsl:when>
        </xsl:choose>
    </div>

</xsl:variable>    

<!--               Book Detail            -->

    <table id="table-detail">
        <tr>
            <th style="font-size:16px"><xsl:value-of select="Textmark/Title" /></th>
            <th style="letter-spacing: 1px;font-size:90%;text-align:right;color: #666600;">
                <xsl:if test="$wishlist='TRUE'">
                    <a style="cursor:help"  title="{Textmark/YouSave}">
                        <xsl:attribute name="onclick">
                            alert('TextMark Price \n --------------------------- \n Used Price:           $<xsl:value-of select="Textmark/UsedPrice" /> \n Buyback Price:     $<xsl:value-of select="Textmark/TradeInValue" /> \n --------------------------- \n Price after Buyback:  $<xsl:value-of select="Textmark/PriceAfterTradeIn" />')
                        </xsl:attribute>
                        Read it for $<xsl:value-of select="format-number(sum(Textmark/PriceAfterTradeIn), '####0.00')" />
                        <img src='resource/image/emoticon_smile.png'  class="imglink"/>
                    </a>
                 </xsl:if>
            </th>
        </tr>
        <tr><td colspan="2">
            <div>
                <div style="width:150px;display:inline-block;padding-right:5px;vertical-align:middle">
                    <img height='160' width='128' class="frontcover" src="{MediumImage/URL}" />
                </div>
                <div id="tabs" style="width:500px;display:inline-block;vertical-align:top;text-align:center">
                    <xsl:copy-of select="$trade-detail" />
                </div>
            </div>
         </td></tr>
         
         <tr><td colspan="2">
            <div class="text-content short-text">
                <table border="0">
                    <tr>
                        <td width="25%"  style="border:none">Edition:</td><td  style="border:none" width="25%"><xsl:value-of select="ItemAttributes/Edition" /></td>
                        <td style="border:none">List Price:</td><td style="border:none"><xsl:value-of select="ItemAttributes/ListPrice/FormattedPrice" /></td>
                    </tr>
                    <tr>
                        <td style="border:none">Publication Date:</td><td style="border:none"><xsl:value-of select="ItemAttributes/PublicationDate" /></td>
                        <td  style="border:none" width="25%">Author:</td><td  style="border:none" width="25%"><xsl:value-of select="ItemAttributes/Author" /></td>
                    </tr>
                    <tr>
                        <td style="border:none" >ISBN-10:</td><td style="border:none"><xsl:value-of select="ItemAttributes/ISBN" /></td>
                        <td style="border:none">ISBN-13:</td><td style="border:none"><xsl:value-of select="ItemAttributes/EAN" /></td>
                    </tr>
                    <tr><td  style="border:none" colspan='4' class="entry"><xsl:value-of select="EditorialReviews/EditorialReview/Content" disable-output-escaping="yes"/></td></tr>
                </table>
            </div>
            <div class="show-more">
                <a href="#">Show more</a>
            </div>
         </td></tr>
   </table>

<!--               Book Ad            -->

    <div style="width:700px;margin: 0 auto;">
        <div class="price-block">
            <div><a href="{//DetailPageURL}" target="_blank"><xsl:value-of select="Textmark/Title" /></a></div>
            <div>www.amazon.com/</div>
            <div title="Shipment Included"><span class="price-title">Amazon New</span><span><xsl:value-of select="//OfferListing/Price/FormattedPrice" /></span></div>
            <xsl:if test="OfferSummary/LowestNewPrice/FormattedPrice!=''">
                <div title="Shipment Included"><span class="price-title">MarketPlace New</span><span><xsl:value-of select="OfferSummary/LowestNewPrice/FormattedPrice" /></span></div>
            </xsl:if>
            <xsl:if test="OfferSummary/LowestUsedPrice/FormattedPrice!=''">
                <div title="Shipment Included"><span class="price-title">MarketPlace Used</span><span><xsl:value-of select="OfferSummary/LowestUsedPrice/FormattedPrice" /></span></div>
            </xsl:if>
            <xsl:if test="ItemAttributes/TradeInValue/FormattedPrice!=''">
                <div title="Shipment Included"><span class="price-title">Amazon BuyBack</span><span><xsl:value-of select="ItemAttributes/TradeInValue/FormattedPrice" /></span></div>
            </xsl:if>
        </div>

        <div class="price-block">
            <xsl:if test="//Chegg/RentPrice!=''">
                <div><a href="{//Chegg/RentURL}" target="_blank"><xsl:value-of select="Textmark/Title" /></a></div>
                <div>www.chegg.com/</div>
                <div title="Shipment Included"><span class="price-title">Rent</span><span>$<xsl:value-of select="//Chegg/RentPrice" /></span></div>
            </xsl:if>
        </div>
    </div>
    
</xsl:template>
</xsl:stylesheet>
