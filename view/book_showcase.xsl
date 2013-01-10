<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:include href="book_td.xsl"/>
<xsl:variable name="menu" select="'B'" />

<xsl:template match="/">
<html>
    <head>
        <title>Buy Textbooks</title>
        <xsl:copy-of select="$analytics-meta-css" />
   </head>
  
    <body>
        <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <h3 class="title">Textbooks Available on Campus</h3>
                    <table  id="table-recommendation">          
                        <tbody>            
                        <xsl:apply-templates select="Root/Buyback/ItemLookupResponse[position() mod 5 = 1]" mode="tr"/>
                        </tbody>        
                    </table> 
                    <xsl:if test="count(Root/Buyback/ItemLookupResponse)=0">
                        <p>There's no book in the list at present.</p>
                    </xsl:if>
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
    <xsl:apply-templates select="../ItemLookupResponse[position() &gt;= $pos and position() &lt; $pos + 5]" mode="td-used"/>    
</tr>  
</xsl:template>  

</xsl:stylesheet>