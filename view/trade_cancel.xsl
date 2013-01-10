<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'X'" />

<xsl:template match="/">
<html>
    <head>
        <title><xsl:value-of select="//Textmark/Title" /></title>
        <script type="text/javascript"><![CDATA[
            function validateForm() {
                var reason =document.forms["cancel"]["reason"].value;
                if (reason==null || reason==""){
                    alert("Please specify the reason for cancellation.")
                    return false;
                }else {
                    return true;
                }
            }
        ]]></script>
        <xsl:copy-of select="$analytics-meta-css" />
    </head>  
    <body>
         <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <h2 class="title">Cancellation Request</h2>
  
                    <table id="table-detail">
                        <tr><th colspan="2"><xsl:value-of select="//Textmark/Title" /></th></tr>
                        <tr>
                            <td><img class="frontcover" src="{//MediumImage/URL}" /></td>
                            <td>
                                <xsl:choose>
                                    <xsl:when test="//Textmark/WishList='TRUE'"> 
                                        <!-- user is buyer -->
                                        The other party is already informed about your transaction. It's very inconvinient to cancel transaction now.
                                        If you have received the book already, please return it before cancellation.
                                        <br/><br/>If you want to cancel purchase, please specify the reason and press the cancel button.
                                        You have to wait for the other student to resolve the cancellation.
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <!-- user is seller -->
                                        The other party has already paid for your book. It's very inconvinient to cancel transaction now.
                                        If you cancel, you may not be able to sell or rent out your book at this great price in future.
                                        <br/><br/>If you still want to cancel transaction, please specify the reason and press the cancel button.
                                        You have to wait for the other student to resolve the cancellation.
                                    </xsl:otherwise>
                                </xsl:choose>
                                <br/><br/>
                                <form name="cancel" method="post" action="" onsubmit="return validateForm()">
                                    <select name="reason" style="width: 350px;" class="form-textbox">
                                        <option value="">----- Please specify the reason -----</option>
                                        <option value="21">We could not agree on a convenient delivery schedule</option>
                                        <option value="22">The book delivery did not happen as scheduled.</option>
                                        <option value="23">The book quality is not as expected.</option>
                                        <option value="24">No payment is made.</option>
                                        <option value="25">There's another reason for cancellation.</option>
                                    </select>
                                    <br/><br/>
                                    <input type="hidden" name="form-submitted" value="1" />
                                    <input type="submit" value="Cancel Purchase" class="form-submit-button"/>
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