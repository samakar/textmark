<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:include href="book_detail.xsl"/>
<xsl:variable name="menu" select="'M'" />

<xsl:template match="List">
    
<xsl:variable name="summary-table">
    <table id="table-books">
        <tr>
            <th></th>
            <th></th>
            <th>Title</th>
            <th>Sellback Price Amazon</th>
            <th>Sellback Price TextMark</th>
            <th>Rent-out Price TextMark</th>
            <th></th>
            <th></th>
        </tr>

        <xsl:for-each select="ItemLookupResponse/Items/Item">
            <xsl:sort select="Textmark/TradeInValue" data-type="number" order="descending"/>

            <xsl:variable name="pricing">
                <td>
                    <a>
                        <xsl:attribute name="href">#Book<xsl:value-of select="position()" /></xsl:attribute>
                        <xsl:value-of select="Textmark/Title" />
                    </a>
                </td>                    
                <td><xsl:value-of select="ItemAttributes/TradeInValue/FormattedPrice" /></td>
                <td>$<xsl:value-of select="Textmark/TradeInValue" /></td>
                <td>$<xsl:value-of select="Textmark/RentOutValue" /></td>
            </xsl:variable>
            
            <xsl:variable name="info">
                <td>
                    <a href="#" title="Details">
                        <img src='resource/image/information.png' class="imglink">                                    
                            <xsl:attribute name="onclick">
                                dialog('<xsl:value-of select="Textmark/SellerMessage" />')
                            </xsl:attribute>
                        </img>
                    </a>
                </td>
                <xsl:copy-of select="$pricing" />
            </xsl:variable>

            <xsl:choose>
                <xsl:when test="Textmark/TradeStatus='Pending'">                    
                    <tr>
                        <td></td>
                        <xsl:copy-of select="$info" />
                        <td>
                            <img src='resource/image/processing.gif' title="Payment Pending" class="imglink"/>
                        </td>
                        <td></td>
                    </tr>
                </xsl:when>
                <xsl:when test="Textmark/TradeStatus='Lock' and Textmark/WishList='FALSE'">                    
                    <tr>
                        <td>
                            <a title="Cancel Sale">
                                <xsl:attribute name="href">
                                    index.php?op=trade_cancel&amp;wishlist=FALSE&amp;isbn=<xsl:value-of select="ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/cancel.png' class="imglink"/>
                            </a>
                        </td>
                        <xsl:copy-of select="$info" />
                        <td>
                            <img src='resource/image/processing.gif' title="Delivery On Schedule" class="imglink"/>
                        </td>
                        <td></td>
                    </tr>
                </xsl:when>
                <xsl:when test="Textmark/TradeStatus='CancelByBuyer'">                    
                    <tr>
                        <td>
                            <a title="Resolve Cancellation">
                                <xsl:attribute name="href">
                                    index.php?op=trade_resolvecancel&amp;wishlist=FALSE&amp;isbn=<xsl:value-of select="ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/error.png' class="imglink"/>
                            </a>
                        </td>
                        <xsl:copy-of select="$info" />
                        <td>
                            <img src='resource/image/processing.gif' title="Resolving Cancellation" class="imglink"/>
                        </td>
                        <td></td>
                    </tr>
                </xsl:when>
                <xsl:when test="Textmark/TradeStatus='Rent'">                    
                    <tr>
                        <td></td>
                        <xsl:copy-of select="$info" />
                        <td></td>
                        <td>rented</td>
                    </tr>
                </xsl:when>
                <xsl:when test="Textmark/TradeStatus='RentOut'">                    
                    <tr>
                        <td></td>
                        <xsl:copy-of select="$info" />
                        <td></td>
                        <td>
                            <form method="get" action="">
                                <input type="hidden" name="op" value="trade_return" />
                                <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                <input type="submit" value="Confirm Return" class="form-add" style="padding:3px 6px"/>
                            </form>
                        </td>
                    </tr>
                </xsl:when>
                <xsl:otherwise>
                    <tr>
                        <td>
                            <form method="get" action="" onClick="return confirm('Do you want to delete the book from your list?')">
                                <input type="hidden" name="op" value="book_remove" />
                                <input type="hidden" name="wishlist" value="FALSE" />
                                <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                <input type="image" src="resource/image/delete.png" title="Delete the Book" border="0" />
                            </form>
                        </td>
                        <td>
                            <a title="Move to Wish List">
                                <xsl:attribute name="href">
                                    index.php?op=book_swap&amp;wishlist=FALSE&amp;isbn=<xsl:value-of select="ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/folder_go.png'  class="imglink" />
                            </a>
                        </td>
                        <xsl:copy-of select="$pricing" />
                        <td>
                            <xsl:if test="Textmark/TradeStatus='Alert' and Textmark/WishList='FALSE'">                    
                                <form method="get" action="" onClick="return confirm('Do you want to turn the alert off? \nYou will stop receiving offers on your book.')">
                                    <input type="hidden" name="op" value="trade_deletealert" />
                                    <input type="hidden" name="wishlist" value="FALSE" />
                                    <input type="hidden" name="rental" value="FALSE" />
                                    <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                    <xsl:choose>
                                        <xsl:when test="Textmark/Rental='TRUE'">
                                            <input type="image" src="resource/image/bell.png" title="Turn off Alert for Rent" border="0" />
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <input type="image" src="resource/image/bell.png" title="Turn off Alert for Sell" border="0" />
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </form>
                            </xsl:if>
                        </td>
                        <td width="90px">
                            <form method="get" action=""  style="display:inline-block">
                                <input type="hidden" name="op" value="trade_rentout" />
                                <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                <input type="submit" value="Rent" class="form-add" style="padding:3px 6px"/>
                            </form>
                            <form method="get" action="" style="display:inline-block">
                                <input type="hidden" name="op" value="trade_buyback" />
                                <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                <input type="submit" value="Sell" class="form-add" style="padding:3px 6px"/>
                            </form>
                        </td>
                    </tr>  
                </xsl:otherwise>
            </xsl:choose>
        </xsl:for-each>
        <tfoot><tr>
            <td><a href="index.php"><img src='resource/image/add.png' title="Add" class="imglink" /></a></td>  
            <td colspan="2"><a href="index.php">Add more books to your list.</a></td>  

            <xsl:choose>
                <xsl:when test="count(ItemLookupResponse/Items)=1">
                    <td colspan="5" style="text-align:right">When you decide to sell or rent your book, click on 'Sell' or 'Rent' button.</td>
                </xsl:when>
                <xsl:otherwise>
                    <td title="Total Value of Your Books">$<xsl:value-of select="sum(//ItemAttributes/TradeInValue/Amount) div 100"></xsl:value-of></td>
                    <td title="Total Value of Your Books">$<xsl:value-of select="format-number(sum(//Textmark/TradeInValue), '####0.00')"></xsl:value-of></td>
                    <td title="Total Value of Your Books">$<xsl:value-of select="format-number(sum(//Textmark/RentOutValue), '####0.00')"></xsl:value-of></td>
                    <td colspan="2"></td>                            
                </xsl:otherwise>
            </xsl:choose>
        </tr></tfoot>
    </table>
 </xsl:variable>    
    
<!--                    Page  Structure                     -->
    
<html>
    <head>
        <title>My Book List</title>
        <script type="text/javascript"><![CDATA[
            function dialog(text) {
                alert(text.replace(/~/g,'\n'));
            }
        ]]></script>
        <xsl:copy-of select="$analytics-meta-css" />
    </head>
    <body>
    <div id="wrapper">   
        <xsl:copy-of select="$header" />
        <div id="page">
            <div class="post">
                <h2 class="title">My Book List</h2>
                    <xsl:choose>
                        <xsl:when test="count(ItemLookupResponse/Items) &gt; 0">
                            <xsl:copy-of select="$summary-table" />           
                        </xsl:when>
                        <xsl:otherwise>
                                <div class="helpbox">        
                                <table><tr>
                                    <td width="200"><img width="120" height="120" src='resource/image/booklist.png' style="padding:20px;"/></td>
                                    <td width="450">
                                        <p><b><a href="index.php"><img src='resource/image/add.png' class="imglink"/>&#160; Add the Books You Have</a></b></p>
                                        <p>Book List is the list of the books you have.  It’s a good idea to keep track of your textbooks by putting them in this list.</p>
                                        <p> Textbooks lose their value very fast.  If you don’t need them, it’s better to put them on sale or rent them out.</p>
                                        <p>Your list is empty!</p>
                                </td></tr></table>
                                </div>

                        </xsl:otherwise>
                    </xsl:choose>

                    <xsl:if test="count(ItemLookupResponse/Items/Item) &gt; 0">
                        <hr/>
                    </xsl:if>

                    <xsl:for-each select="ItemLookupResponse/Items/Item">
                        <xsl:sort select="Textmark/TradeInValue" data-type="number" order="descending"/>
                        <div>
                            <xsl:attribute name="id">Book<xsl:value-of select="position()" /></xsl:attribute>
                            <xsl:call-template name="book_detail">
                                <xsl:with-param name="show_buttons" select="'no'" />
                                <xsl:with-param name="wishlist" select="'FALSE'" />
                            </xsl:call-template>
                            <a href="#table-books" title='Back to Top' style='margin-left:45px;'><img src='resource/image/top.png' class="imglink"/></a>
                        </div>
                    </xsl:for-each>
                </div>
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>
</xsl:stylesheet>
