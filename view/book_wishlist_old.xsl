<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:include href="book_detail.xsl"/>
<xsl:variable name="menu" select="'W'" />
<xsl:template match="List">

<xsl:variable name="summary-table">
    <table id="table-books">
        <tr>
            <th></th>
            <th></th>
            <th>Title</th>
            <th style="width:80px" id="buy">New Price MarketPlace</th>
            <th style="width:80px" id="buy">Used Price MarketPlace</th>
            <th style="width:80px" id="buy">Used Price TextMark</th>
            <th style="width:120px" id="rent">Rent Price<br/>Chegg</th>
            <th style="width:120px" id="rent">Rent Price TextMark</th>
            <th></th>
            <th></th>
        </tr>

        <xsl:for-each select="ItemLookupResponse/Items/Item">
            <xsl:sort select="Textmark/UsedPrice" data-type="number" order="ascending"/>

            <xsl:variable name="pricing">
                <td>
                    <a>
                        <xsl:attribute name="href">#Book<xsl:value-of select="position()" /></xsl:attribute>
                        <xsl:value-of select="Textmark/Title" />
                    </a>
                </td>
                <td id="buy"><xsl:value-of select="OfferSummary/LowestNewPrice/FormattedPrice" /></td>
                <td id="buy"><xsl:value-of select="OfferSummary/LowestUsedPrice/FormattedPrice" /></td>                    
                <td id="buy">$<xsl:value-of select="Textmark/UsedPrice" /></td>
                <td id="rent">$<xsl:value-of select="Chegg/RentPrice" /></td>
                <td id="rent">$<xsl:value-of select="Textmark/RentPrice" /></td>
            </xsl:variable>

            <xsl:variable name="info">
                <td>
                    <a href="#" title="Details">
                        <img src='resource/image/information.png' class="imglink">                                    
                            <xsl:attribute name="onclick">
                                dialog('<xsl:value-of select="Textmark/BuyerMessage" />')
                            </xsl:attribute>
                        </img>
                    </a>
                </td>
                <xsl:copy-of select="$pricing" />
            </xsl:variable>

            <xsl:choose>
                <xsl:when test="Textmark/TradeStatus='Inform' and Textmark/WishList='TRUE'">                    
                    <tr>
                        <td>
                            <a title="Decline Offer">
                                <xsl:attribute name="href">
                                    index.php?op=trade_decline&amp;isbn=<xsl:value-of select="ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/cross.png' class="imglink"/>
                            </a>
                        </td>
                        <xsl:copy-of select="$info" />
                        <td>
                            <img src='resource/image/processing.gif' title="Wating for Acceptance" class="imglink"/>
                        </td>
                        <td>
                            <form method="get" action="">
                                <xsl:choose>
                                    <xsl:when test="Textmark/Rental='TRUE'">
                                        <input type="hidden" name="op" value="trade_rent" />
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <input type="hidden" name="op" value="trade_used" />
                                    </xsl:otherwise>
                                </xsl:choose>
                                <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                <input type="submit" value="Accept Offer" class="form-add"/>
                            </form>
                        </td>
                    </tr>
                </xsl:when>
                <xsl:when test="Textmark/TradeStatus='Pending' and Textmark/WishList='TRUE'">                    
                    <tr>
                        <td>
                            <a title="Cancel Purchase">
                                <xsl:attribute name="href">
                                    index.php?op=trade_cancelpayment&amp;wishlist=TRUE&amp;isbn=<xsl:value-of select="ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/cancel.png' class="imglink"/>
                            </a>
                        </td>
                        <xsl:copy-of select="$info" />
                        <td>
                            <img src='resource/image/processing.gif' title="Payment Pending" class="imglink"/>
                        </td>
                        <td>
                            <input type="submit" value="Retry Payment" class="form-add">
                                <xsl:attribute name="onclick">
                                    parent.location="index.php?op=trade_retrypayment&amp;isbn=<xsl:value-of select="ItemAttributes/EAN" />"
                                </xsl:attribute>
                            </input>
                        </td>
                    </tr>
                </xsl:when>
                <xsl:when test="Textmark/TradeStatus='Lock' and Textmark/WishList='TRUE'">                    
                    <tr>
                        <td>
                            <a title="Cancel Purchase">
                                <xsl:attribute name="href">
                                    index.php?op=trade_cancel&amp;wishlist=TRUE&amp;isbn=<xsl:value-of select="ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/cancel.png' class="imglink"/>
                            </a>
                        </td>
                        <xsl:copy-of select="$info" />
                        <td>
                            <img src='resource/image/processing.gif' title="Delivery On Schedule" class="imglink"/>
                        </td>
                        <td>
                            <input type="submit" value="Confirm Delivery" class="form-add">
                                <xsl:attribute name="onclick">
                                    parent.location='<xsl:value-of select="Textmark/BuyerConfirmLink" />'
                                </xsl:attribute>
                            </input>
                        </td>
                    </tr>
                </xsl:when>
                <xsl:when test="Textmark/TradeStatus='CancelByBuyer'">                    
                    <tr>
                        <td>
                            <a title="Undo Cancel">
                                <xsl:attribute name="href">
                                    index.php?op=trade_undocancel&amp;wishlist=TRUE&amp;isbn=<xsl:value-of select="ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/arrow_undo.png' class="imglink"/>
                            </a>
                        </td>
                        <xsl:copy-of select="$info" />
                        <td>
                            <img src='resource/image/processing.gif' title="Resolving Cancellation" class="imglink"/>
                        </td>
                        <td>
                            <input type="submit" value="Confirm Delivery" class="form-add">
                                <xsl:attribute name="onclick">
                                    parent.location='<xsl:value-of select="Textmark/BuyerConfirmLink" />'
                                </xsl:attribute>
                            </input>
                        </td>
                    </tr>
                </xsl:when>
                <xsl:when test="Textmark/TradeStatus='CancelBySeller'">                    
                    <tr>
                        <td>
                            <a title="Resolve Cancellation">
                                <xsl:attribute name="href">
                                    index.php?op=trade_resolvecancel&amp;wishlist=TRUE&amp;isbn=<xsl:value-of select="ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/error.png' class="imglink"/>
                            </a>
                        </td>
                        <xsl:copy-of select="$info" />
                        <td>
                            <img src='resource/image/processing.gif' title="Resolving Cancellation" class="imglink"/>
                        </td>
                        <td>
                            <input type="submit" value="Confirm Delivery" class="form-add">
                                <xsl:attribute name="onclick">
                                    parent.location='<xsl:value-of select="Textmark/BuyerConfirmLink" />'
                                </xsl:attribute>
                            </input>
                        </td>
                    </tr>
                </xsl:when>
                <xsl:otherwise>
                    <tr>
                        <td>
                            <form method="get" action="" onClick="return confirm('Do you want to delete the book from your list?')">
                                <input type="hidden" name="op" value="book_remove" />
                                <input type="hidden" name="wishlist" value="TRUE" />
                                <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                <input type="image" src="resource/image/delete.png" title="Delete the Book" border="0" />
                            </form>
                        </td>
                        <td>
                            <a title="Move to My Book List.">
                                <xsl:attribute name="href">
                                    index.php?op=book_swap&amp;wishlist=TRUE&amp;isbn=<xsl:value-of select="ItemAttributes/EAN" />
                                </xsl:attribute>
                                <img src='resource/image/folder_go.png'  class="imglink" />
                            </a>
                        </td>
                        <xsl:copy-of select="$pricing" />
                        <td>
                            <xsl:choose>
                                <xsl:when test="Textmark/TradeStatus='Alert' and Textmark/WishList='TRUE'">                    
                                    <form method="get" action="" onClick="return confirm('Do you want to turn the alert off? \nYou will stop receiving offers on this book.')">
                                        <input type="hidden" name="op" value="trade_deletealert" />
                                        <input type="hidden" name="rental" value="FALSE" />
                                        <input type="hidden" name="wishlist" value="TRUE" />
                                        <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                        <xsl:choose>
                                            <xsl:when test="Textmark/Rental='TRUE'">
                                                <input type="image" src="resource/image/bell.png" title="Turn off Alert for Rent" border="0" />
                                            </xsl:when>
                                            <xsl:otherwise>
                                                <input type="image" src="resource/image/bell.png" title="Turn off Alert for Buy" border="0" />
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </form>
                                </xsl:when>
                            </xsl:choose>
                        </td>
                        <td>
                            <form method="get" action="" id="buy">
                                <input type="hidden" name="op" value="trade_used" />
                                <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                <input type="submit" value="Buy" class="form-add"/>
                            </form>
                            <form method="get" action="" id="rent">
                                <input type="hidden" name="op" value="trade_rent" />
                                <input type="hidden" name="isbn" value="{ItemAttributes/EAN}" />
                                <input type="submit" value="Rent" class="form-add"/>
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
                    <td colspan="6" style="text-align:right">When you decide to buy a book, click on 'Best Buy' button.</td>
                </xsl:when>
                <xsl:otherwise>
                    <td id="buy" title="Total Price of Your Books">$<xsl:value-of select="sum(//OfferSummary/LowestNewPrice/Amount) div 100" /></td>
                    <td id="buy" title="Total Price of Your Books">$<xsl:value-of select="sum(//OfferSummary/LowestUsedPrice/Amount) div 100" /></td>
                    <td id="buy" title="Total Price of Your Books">$<xsl:value-of select="format-number(sum(//Textmark/UsedPrice), '####0.00')" /></td>
                    <td id="rent" title="Total Price of Your Books">$<xsl:value-of select="format-number(sum(//Chegg/RentPrice), '####0.00')" /></td>
                    <td id="rent" title="Total Price of Your Books">$<xsl:value-of select="format-number(sum(//Textmark/RentPrice), '####0.00')" /></td>
                    <td colspan="2" onclick="alert('This is the amount you pay \n if you buy your books from TextMark \n and sell them back to TextMark again.')"> 
                        <a style="cursor:help">
                            Read them for $<xsl:value-of select="format-number(sum(//Textmark/PriceAfterTradeIn), '####0.00')" />
                            <img src='resource/image/emoticon_smile.png'  class="imglink"/>
                        </a>
                    </td>                            
                </xsl:otherwise>
            </xsl:choose>

        </tr></tfoot>

    </table>
</xsl:variable>    

<!--                    Page  Structure                     -->

<html>
    <head>
        <title>Wish List</title>
        <script type="text/javascript"><![CDATA[
            function dialog(text) {
                alert(text.replace(/~/g,'\n'));
            }    
            
            function displayTagId(tag,showId, hideId) {
                 allElements = document.getElementsByTagName(tag);       
                 for (var i=0; i < allElements.length; i++) {
                     if (allElements[i].id==showId)  {
                        allElements[i].style.display = 'table-cell';
                     }
                     if (allElements[i].id==hideId)  {
                        allElements[i].style.display = 'none';
                     }
                 }
            }
                
            function showRent() {
                displayTagId('th', 'rent', 'buy');
                displayTagId('td', 'rent', 'buy');
                displayTagId('form', 'rent', 'buy');
            }
            
            function showBuy() {
                displayTagId('th', 'buy', 'rent');
                displayTagId('td', 'buy', 'rent');
                displayTagId('form', 'buy', 'rent');
            }
            
            function show() {
                var radioObj = document.getElementById("radio-button");
                if (radioObj[0].checked)
                    showBuy();
                else
                    showRent();
            }
            
        ]]></script>
        <xsl:copy-of select="$analytics-meta-css" />
    </head>
    <body onload="show()">
        <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <h2 class="title">Wish List</h2>
                    
                    <xsl:choose>
                        <xsl:when test="count(ItemLookupResponse/Items) &gt; 0">                    
                            <form style="padding-left:40px" id="radio-button">
                                <label>Show price:</label> 
                                <input type="radio" name="buy-rent" onclick="showBuy()" checked="1"/> Buy
                                <input type="radio" name="buy-rent" onclick="showRent()"/> Rent
                            </form>                    
                            <xsl:copy-of select="$summary-table" />
                        </xsl:when>
                        <xsl:otherwise>
                                <div class="helpbox">        
                                    <table><tr>
                                        <td width="200"><img width="120" height="120" src='resource/image/booklist.png' style="padding:20px;"/></td>
                                        <td width="450">
                                            <p><b><a href="index.php"><img src='resource/image/add.png'  class="imglink"/>Add the Books You Want</a></b></p>
                                            <p>Wish List is the list of the books you want to buy.  If you find a book and prefer to purchase it later, put it in ‘Wish List’ to save time on typing in its ISBN again.</p>
                                            <p>Your list is empty!</p>
                                    </td></tr></table>
                                </div>
                        </xsl:otherwise>
                    </xsl:choose>

                    <xsl:if test="count(ItemLookupResponse/Items/Item) &gt; 0">
                        <hr/>
                    </xsl:if>

                    <xsl:for-each select="ItemLookupResponse/Items/Item">
                        <xsl:sort select="Textmark/UsedPrice" data-type="number" order="ascending"/>
                        <div>
                            <xsl:attribute name="id">Book<xsl:value-of select="position()" /></xsl:attribute>
                            <xsl:call-template name="book_detail">
                                <xsl:with-param name="wishlist" select="'TRUE'" />
                                <xsl:with-param name="show_buttons" select="'no'" />
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
