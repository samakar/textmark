<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'X'" />

<xsl:template match="/">
<html>
    <head>
        <title><xsl:value-of select="//Textmark/Title" /></title>
        <style type="text/css">
            label {display:inline-block;width:80px;text-align:left}
        </style>
        <script type="text/javascript"><![CDATA[
            function validateForm() {
                var nickName = document.forms["formbuyback"]["nickname"].value;
                var cellPhone = document.forms["formbuyback"]["cellphone"].value;
                var error = '';
                if (nickName==null || nickName==""){
                    error = "Nickname must be filled out.\n";
                }else {
                    var regexObj = /^[a-zA-Z]'?[- a-zA-Z]*$/;
                    if (!regexObj.test(nickName)) {
                        error += "The name has invalid characters.\n"
                    }            
                }
                if (cellPhone==null || cellPhone==""){
                    //error += "Phone number must be filled out.\n";
                }else {
                    var regexObj = /^\(?([2-9][0-8][0-9])\)?[ ]*[-.]?[ ]*([2-9][0-9]{2})[ ]*[-.]?[ ]*([0-9]{4})$/;
                    if (!regexObj.test(cellPhone)) {
                        error += "The phone number is invalid or in wrong format."
                        error += "Entering your phone number is optional.\n"
                        error += "If you don't want to share your phone number, leave it blank.\n"
                   }
                }
                if (error!='') {
                    alert(error);
                    return false;
                }
            }
        ]]></script>        
        <xsl:copy-of select="$analytics-meta-css" />
    </head>
  
    <body>
        <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div  class="post" style="border-bottom:none;">
                    <xsl:choose>
                        <xsl:when test="//Confirmation='TRUE'">
                            <h2 class="title">Trade Confirmed!</h2>
                        </xsl:when>
                        <xsl:otherwise>
                            <h2 class="title">Confirm Trade</h2>                            
                       </xsl:otherwise>
                    </xsl:choose>
        
                    <div style="width:480px;float:left;margin-right:30px">        

                      <table id="table-detail" style="width:480px;margin:0px">
                        <tr><th colspan="2"><xsl:value-of select="//Textmark/Title" /></th></tr>
                        <tr>
                            <td><img class="frontcover" src="{//MediumImage/URL}" /></td>
                            <td>
                                <xsl:choose>
                                    <xsl:when test="//Confirmation='TRUE'">
                                        <p><b>Thank you!</b></p>
                                        <p>We informed the other party about your book.</p>
                                        <p>After the payment, we'll send you an email about purchase details. We also give your contact information to the other party to contact you and arrange an appointment.</p>
                                        <p>We'll pay you back as soon as the delivery of the book is confirmed.</p>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <br/>
                                        <form action="" id="formbuyback" method="post" onsubmit="return validateForm()">
                                            <label for="name">Name</label>
                                            <input type="text" name="name" id="name" maxlength="15" size="20" class="form-textbox"  title="Valid name is required.">
                                                <xsl:attribute name="value">
                                                    <xsl:value-of select="//Textmark/Nickname" />
                                                </xsl:attribute>
                                            </input>
                                            <br />
                                            <label for="cellphone">Cell Phone</label>
                                            <input type="text" name="cellphone" id="cellphone" maxlength="15" size="20" class="form-textbox" title="Cell phone number is optional.">
                                                <xsl:attribute name="value">
                                                    <xsl:value-of select="//Textmark/CellPhone" />
                                                </xsl:attribute>
                                            </input>
                                            <input type="hidden" name="isbn" value="{//ItemAttributes/EAN}" />
                                            <input type="hidden" name="form-submitted" value="1" />
                                            <p>I confirm my book is in good condition. The book cover, spine and pages are all intact. The book may contain some minor highlighting and markings.</p>
                                            <br/>
                                            <input type="submit" value="Confirm" class="form-submit-button"/>
                                        </form>
                                    </xsl:otherwise>
                                </xsl:choose>

                                <xsl:if test="count(Root/Errors) &gt; 0">
                                    <p  class="form-error"><xsl:copy-of select="Root/Errors/Er" /></p>
                                    <p>Operation failed. Please check your nickname and phone number and try again.</p>
                                </xsl:if>

                                <p><xsl:value-of select="Root/Message" /></p>
                            </td>
                        </tr>
                        <tfoot>
                            <tr><td colspan="2">
                                <ul>
                                    <li>Entering your cell phone number is optional.</li>
                                    <li>We reveal your contact information only to the student that has paid for your book.</li>
                                    <li>We recommend you enter your phone number to speed up transaction.</li>
                                </ul>
                            </td></tr>
                        </tfoot>
                    </table>                
 
                    </div>

                    <div class="helpbox" style="float:right;width:250px;">
                        <strong>How it works...</strong>
                        <ol>
                            <li>A student in your campus pays for your book.</li>
                            <li>We give your contact information to the student.</li>
                            <li>The student will contact you to make an appointment.</li>
                            <li>You will hand over your book and we pay you back.</li>
                        </ol>
                    </div>                
                </div>
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>
</xsl:stylesheet>
