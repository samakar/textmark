<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >

<!--               BOOK COVER IMAGE            -->
  
<xsl:template match="ItemLookupResponse" mode="td-image" name="td-image">
    <td>
        <a>
            <xsl:attribute name="href">
                index.php?op=book_find&amp;form-submitted=1&amp;isbn=<xsl:value-of select="Items/Item/ItemAttributes/EAN" />
            </xsl:attribute>
            <img class="frontcover">
                <xsl:attribute name="src">
                    <xsl:value-of select="Items/Item/MediumImage/URL" />
                </xsl:attribute>
            </img>
        </a>
    </td>
</xsl:template>

<!--               BOOK TITLE            -->

<xsl:template match="ItemLookupResponse" mode="td-title" name="td-title">
    <td >
        <a style="text-decoration:none;">
            <xsl:attribute name="href">
                index.php?op=book_find&amp;form-submitted=1&amp;isbn=<xsl:value-of select="Items/Item/ItemAttributes/EAN" />
            </xsl:attribute>
            <xsl:value-of select="Items/Item/Textmark/Title" />
        </a>
    </td>
</xsl:template>

<!--               BOOK USED            -->

<xsl:template match="ItemLookupResponse" mode="td-used" name="td-used">
    <td >
        <span>
            <form method="get" action="">
                <input type="hidden" name="op" value="trade_used" />
                <input type="hidden" name="isbn" value="{Items/Item/ItemAttributes/EAN}" />
                <input type="submit" value="Buy for ${Items/Item/Textmark/UsedPrice}" class="form-add" style="width:140px"/>
            </form>              
        </span>    
    </td>
</xsl:template>

<!--               BOOK SELL            -->

<xsl:template match="ItemLookupResponse" mode="td-sell" name="td-sell">
    <td style="padding:0 10px 10px 10px">
        <span>
            <xsl:variable name="info">
                <span>
                    <a href="#" title="Details">
                        <img src='resource/image/information.png' class="imglink">                                    
                            <xsl:attribute name="onclick">
                                dialog('<xsl:value-of select="Items/Item/Textmark/SellerMessage" />')
                            </xsl:attribute>
                        </img>
                    </a>
                </span>
            </xsl:variable>

            <xsl:choose>
                <xsl:when test="Items/Item/Textmark/TradeStatus='Pending'">                    
                    <xsl:copy-of select="$info" />
                    <span>
                        <img src='resource/image/processing.gif' title="Payment Pending" class="imglink"/>
                    </span>
                </xsl:when>
                <xsl:when test="Items/Item/Textmark/TradeStatus='Lock' and Items/Item/Textmark/WishList='FALSE'">                    
                    <span>
                        <a title="Cancel Sale">
                            <xsl:attribute name="href">
                                index.php?op=trade_cancel&amp;wishlist=FALSE&amp;isbn=<xsl:value-of select="Items/Item/ItemAttributes/EAN" />
                            </xsl:attribute>
                            <img src='resource/image/cancel.png' class="imglink"/>
                        </a>
                    </span>
                    <xsl:copy-of select="$info" />
                    <span>
                        <img src='resource/image/processing.gif' title="Delivery On Schedule" class="imglink"/>
                    </span>
                </xsl:when>
                <xsl:when test="Items/Item/Textmark/TradeStatus='CancelByBuyer'">                    
                    <span>
                        <a title="Resolve Cancellation">
                            <xsl:attribute name="href">
                                index.php?op=trade_resolvecancel&amp;wishlist=FALSE&amp;isbn=<xsl:value-of select="Items/Item/ItemAttributes/EAN" />
                            </xsl:attribute>
                            <img src='resource/image/error.png' class="imglink"/>
                        </a>
                    </span>
                    <xsl:copy-of select="$info" />
                    <span>
                        <img src='resource/image/processing.gif' title="Resolving Cancellation" class="imglink"/>
                    </span>
                </xsl:when>
                <xsl:otherwise>
                    <span>
                        <form method="get" action="" onClick="return confirm('Do you want to delete the book  , {Items/Item/Textmark/Title} , from your list?')"  style="display: inline-block">
                            <input type="hidden" name="op" value="book_remove" />
                            <input type="hidden" name="wishlist" value="FALSE" />
                            <input type="hidden" name="isbn" value="{Items/Item/ItemAttributes/EAN}" />
                            <input type="image" src="resource/image/delete.png" title="Delete the Book" border="0" />
                        </form>
                    </span>
                </xsl:otherwise>
            </xsl:choose>
        </span>            
        <span style="text-align:right;float:right;">            
            $<xsl:value-of select="Items/Item/Textmark/TradeInValue" />
        </span>            
    </td>
</xsl:template>

<!--               WISH LIST            -->

<xsl:template match="ItemLookupResponse" mode="td-wishlist" name="td-wishlist">
    <td style="padding:0 10px 10px 10px">
        <span>

            <xsl:variable name="info2">
                <span>
                    <a href="#" title="Details">
                        <img src='resource/image/information.png' class="imglink">                                    
                            <xsl:attribute name="onclick">
                                dialog('<xsl:value-of select="Items/Item/Textmark/BuyerMessage" />')
                            </xsl:attribute>
                        </img>
                    </a>
                </span>
            </xsl:variable>
            
            <xsl:choose>
                <xsl:when test="Items/Item/Textmark/TradeStatus='Inform' and Items/Item/Textmark/WishList='TRUE'">                    
                        <span>
                            <a title="Decline Offer">
                                <xsl:attribute name="href">
                                    index.php?op=trade_decline&amp;isbn=<xsl:value-of select="Items/Item/ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/cross.png' class="imglink"/>
                            </a>
                        </span>
                        <xsl:copy-of select="$info2" />
                        <span>
                            <img src='resource/image/processing.gif' title="Wating for Acceptance" class="imglink"/>
                        </span>
                        <span>
                            <form method="get" action="">
                                <input type="hidden" name="op" value="trade_used" />
                                <input type="hidden" name="isbn" value="{Items/Item/ItemAttributes/EAN}" />
                                <input type="submit" value="Accept Offer" class="form-add"/>
                            </form>
                        </span>
                </xsl:when>
                <xsl:when test="Items/Item/Textmark/TradeStatus='Pending' and Items/Item/Textmark/WishList='TRUE'">                    
                        <span>
                            <a title="Cancel Purchase">
                                <xsl:attribute name="href">
                                    index.php?op=trade_cancelpayment&amp;wishlist=TRUE&amp;isbn=<xsl:value-of select="Items/Item/ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/cancel.png' class="imglink"/>
                            </a>
                        </span>
                        <xsl:copy-of select="$info2" />
                        <span>
                            <img src='resource/image/processing.gif' title="Payment Pending" class="imglink"/>
                        </span>
                        <span>
                            <input type="submit" value="Retry Payment" class="form-add">
                                <xsl:attribute name="onclick">
                                    parent.location="index.php?op=trade_retrypayment&amp;isbn=<xsl:value-of select="Items/Item/ItemAttributes/EAN" />"
                                </xsl:attribute>
                            </input>
                        </span>
                </xsl:when>
                <xsl:when test="Items/Item/Textmark/TradeStatus='Lock' and Items/Item/Textmark/WishList='TRUE'">                    
                        <span>
                            <a title="Cancel Purchase">
                                <xsl:attribute name="href">
                                    index.php?op=trade_cancel&amp;wishlist=TRUE&amp;isbn=<xsl:value-of select="Items/Item/ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/cancel.png' class="imglink"/>
                            </a>
                        </span>
                        <xsl:copy-of select="$info2" />
                        <span>
                            <img src='resource/image/processing.gif' title="Delivery On Schedule" class="imglink"/>
                        </span>
                        <span>
                            <input type="submit" value="Confirm Delivery" class="form-add">
                                <xsl:attribute name="onclick">
                                    parent.location='<xsl:value-of select="Items/Item/Textmark/BuyerConfirmLink" />'
                                </xsl:attribute>
                            </input>
                        </span>
                </xsl:when>
                <xsl:when test="Items/Item/Textmark/TradeStatus='CancelByBuyer'">                    
                        <span>
                            <a title="Undo Cancel">
                                <xsl:attribute name="href">
                                    index.php?op=trade_undocancel&amp;wishlist=TRUE&amp;isbn=<xsl:value-of select="Items/Item/ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/arrow_undo.png' class="imglink"/>
                            </a>
                        </span>
                        <xsl:copy-of select="$info2" />
                        <span>
                            <img src='resource/image/processing.gif' title="Resolving Cancellation" class="imglink"/>
                        </span>
                        <span>
                            <input type="submit" value="Confirm Delivery" class="form-add">
                                <xsl:attribute name="onclick">
                                    parent.location='<xsl:value-of select="Items/Item/Textmark/BuyerConfirmLink" />'
                                </xsl:attribute>
                            </input>
                        </span>
                </xsl:when>
                <xsl:when test="Items/Item/Textmark/TradeStatus='CancelBySeller'">                    
                    <span>
                        <a title="Resolve Cancellation">
                            <xsl:attribute name="href">
                                index.php?op=trade_resolvecancel&amp;wishlist=TRUE&amp;isbn=<xsl:value-of select="Items/Item/ItemAttributes/EAN" />
                            </xsl:attribute>
                            <img src='resource/image/error.png' class="imglink"/>
                        </a>
                    </span>
                    <xsl:copy-of select="$info2" />
                    <span>
                        <img src='resource/image/processing.gif' title="Resolving Cancellation" class="imglink"/>
                    </span>
                    <span>
                        <input type="submit" value="Confirm Delivery" class="form-add">
                            <xsl:attribute name="onclick">
                                parent.location='<xsl:value-of select="Items/Item/Textmark/BuyerConfirmLink" />'
                            </xsl:attribute>
                        </input>
                    </span>
                </xsl:when>
                <xsl:otherwise>
                    <span>
                        <form method="get" action="" onClick="return confirm('Do you want to delete the book , {Items/Item/Textmark/Title} , from your list?')"  style="display: inline-block">
                            <input type="hidden" name="op" value="book_remove" />
                            <input type="hidden" name="wishlist" value="TRUE" />
                            <input type="hidden" name="isbn" value="{Items/Item/ItemAttributes/EAN}" />
                            <input type="image" src="resource/image/delete.png" title="Delete the Book" border="0" />
                        </form>
                    </span>
                </xsl:otherwise>
            </xsl:choose>
        </span>            
        <span style="text-align:right;float:right;">            
            $<xsl:value-of select="Items/Item/Textmark/UsedPrice" />
        </span>            
    </td>
</xsl:template>

</xsl:stylesheet>
