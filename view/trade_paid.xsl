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
  
    <body onload="init()">
         <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div  class="post" style="border-bottom:none;">
                    <h2 class="title">Thank you!</h2>
                    <div  style="width: 430px; float: left;margin-right:50px">        
                        <table id="table-detail" style="width:450px;margin:0px">
                            <tr><th colspan="2"><xsl:value-of select="//Textmark/Title" /></th></tr>
                            <tr>
                                <td><img class="frontcover" src="{//MediumImage/URL}" /></td>
                                <td>
                                    <p>Thank you for your payment.</p>
                                    
                                    <xsl:choose>
                                        <xsl:when test="//Confirmation='TRUE'">
                                            <p>Please make an appointmant with <b><xsl:value-of select="//Textmark/SellerNickname" /></b>
                                            to receive your book as soon as possible:</p>
                                            <ul>
                                                <xsl:if test="//Textmark/SellerPhone!=''">
                                                    <li>Cellphone: 
                                                        <a>
                                                            <xsl:attribute name="href">
                                                                tel:<xsl:value-of select='//Textmark/SellerPhone'/>
                                                            </xsl:attribute>
                                                            <xsl:value-of select="//Textmark/SellerPhone" />
                                                        </a>
                                                    </li>
                                                </xsl:if>
                                                <li>
                                                    Email:
                                                    <a>
                                                        <xsl:attribute name="href">
                                                            mailto:<xsl:value-of select='//Textmark/SellerEmail'/>
                                                        </xsl:attribute>
                                                        <xsl:value-of select="//Textmark/SellerEmail" />
                                                    </a>
                                                </li>
                                            </ul>
                                            <p>We have sent you a confirmation email.</p>
                                            <p>Please confirm delivery of the book after <xsl:value-of select="//Textmark/SellerNickname" /> hands over your book.</p>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <p>We are waiting for your payment confirmation from PayPal.</p>
                                            <p>As soon as the confirmation is received, we'll send you the other student's contact information by email and update your book lists.</p>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div>
                        <div class="helpbox" style="float:right;width:250px;margin-bottom:30px;">
                            <strong>How it works...</strong>
                            <ol>
                                <li><a href="https://www.paypal.com/" target="_blank">PayPal</a> confirms your payment.</li>
                                <li>We give you the other student's contact information.</li>
                                <li>You contact the student and make an appointment in campus.</li>
                                <li>The student will hand over your book at the appointment.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>
</xsl:stylesheet>