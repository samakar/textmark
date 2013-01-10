<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:include href="book_td.xsl"/>
<xsl:variable name="menu" select="'W'" />

<xsl:template match="List">
<html>
    <head>
        <title>Textbooks Wishlist</title>
        <xsl:copy-of select="$analytics-meta-css" />
   </head>
  
    <body>
        <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <h3 class="title">Your Reserved Textbooks</h3>
                    <xsl:choose>
                        <xsl:when test="count(ItemLookupResponse/Items) &gt; 0">                    
                            <table  id="table-recommendation">          
                                <tbody>            
                                    <xsl:apply-templates select="ItemLookupResponse[position() mod 5 = 1]" mode="tr"/>
                                </tbody>        
                            </table> 
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
                </div>
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>

<xsl:template match="ItemLookupResponse" mode="tr">    
    <xsl:variable name="pos" select="(position() - 1) * 5 + 1"/>    
    <tr>      
        <xsl:apply-templates select="../ItemLookupResponse[position() &gt;= $pos and position() &lt; $pos + 5]" mode="td-image"/>    
    </tr>
    <tr>      
        <xsl:apply-templates select="../ItemLookupResponse[position() &gt;= $pos and position() &lt; $pos + 5]" mode="td-wishlist"/>    
    </tr>  
</xsl:template>  

</xsl:stylesheet>