<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
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
                    <h2 class="title">Resolve Cancellation</h2>
  
                    <table id="table-detail">
                        <tr><th colspan="2"><xsl:value-of select="//Textmark/Title" /></th></tr>
                        <tr>
                            <td><img class="frontcover" src="{//MediumImage/URL}" /></td>
                            <td>
                                <xsl:choose>
                                    <xsl:when test="//Textmark/WishList='TRUE'"> 
                                        <!-- user is buyer -->
                                        We're sorry for the inconvenience.
                                        <br/>The other student has requested to cancel the transsaction for the below reason. 
                                        <br/><br/>Please confirm cancellation.You can select your own the reason if you have a different opinion.
                                        <br/>We will refund your money totally.
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <!-- user is seller -->
                                        We're sorry for the inconvenience.
                                        <br/>The buyer has requested to cancel the transsaction If you have already delivered the book, please ask him to return it first.                                            
                                        <br/><br/>If you have not delivered the book, please confirm cancellation for the below reason.You can select your own reason if you have a different opinion.
                                    </xsl:otherwise>
                                </xsl:choose>
                                <br/><br/>
                                
                                <form method="post" action="">
                                    <select name="reason" style="width: 350px;" class="form-textbox" >
                                        <option value="21">
                                            <xsl:if test="//Textmark/CancelCode='21'">
                                                <xsl:attribute name="selected">selected</xsl:attribute>
                                            </xsl:if>                                            
                                            It's not possible to find a convenient delivery schedule.
                                        </option>
                                        <option value="22">
                                            <xsl:if test="//Textmark/CancelCode='22'">
                                                <xsl:attribute name="selected">selected</xsl:attribute>
                                            </xsl:if>                                            
                                            The book delivery did not happen as scheduled.
                                        </option>
                                        <option value="23">
                                            <xsl:if test="//Textmark/CancelCode='23'">
                                                <xsl:attribute name="selected">selected</xsl:attribute>
                                            </xsl:if>                                            
                                            The book quality is not as expected.
                                        </option>
                                        <option value="24">
                                            <xsl:if test="//Textmark/CancelCode='24'">
                                                <xsl:attribute name="selected">selected</xsl:attribute>
                                            </xsl:if>                                            
                                            No payment is made.
                                        </option>
                                        <option value="25">
                                            <xsl:if test="//Textmark/CancelCode='25'">
                                                <xsl:attribute name="selected">selected</xsl:attribute>
                                            </xsl:if>                                            
                                            There's another reason for cancellation.
                                        </option>
                                    </select>
                                    <br/><br/>
                                    <input type="hidden" name="form-submitted" value="1" />
                                    <input type="submit" value="Confirm" class="form-submit-button"/>
                                </form>
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