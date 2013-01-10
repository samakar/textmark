<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:include href="book_td.xsl"/>
<xsl:variable name="menu" select="'S'" />

<xsl:template match="List">
<html>
    <head>
        <title>Sell Textbooks</title>
        <xsl:copy-of select="$analytics-meta-css" />
   </head>
  
    <body>
        <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <h3 class="title">Your Textbooks on Sale</h3>
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
                                        <p><b><a href="index.php"><img src='resource/image/add.png' class="imglink"/>&#160; Add the Books You Have</a></b></p>
                                        <p>This is the list of your books that are on sale.</p>
                                        <p> Textbooks lose their value very fast.  If you don’t need them, it’s better to put them on sale or rent them out.</p>
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
        <xsl:apply-templates select="../ItemLookupResponse[position() &gt;= $pos and position() &lt; $pos + 5]" mode="td-sell"/>    
    </tr>  
</xsl:template>  

</xsl:stylesheet>