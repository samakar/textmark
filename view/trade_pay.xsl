<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'X'" />

<xsl:template match="/">
  
<html>
    <head>
        <title><xsl:value-of select="//Textmark/Title" /></title>
        <!--  http://blog.stevenlevithan.com/archives/validate-phone-number  -->
        <script type="text/javascript"><![CDATA[
            function validateForm() {
                var nickName =document.forms["formPayment"]["name"].value;
                var cellPhone =document.forms["formPayment"]["cellphone"].value;
                var error = '';
                if (nickName==null || nickName=="" || nickName=="name"){
                    error = "Nickname must be filled out.\n";
                }else {
                    var regexObj = /^[a-zA-Z]'?[- a-zA-Z]*$/;
                    if (!regexObj.test(nickName)) {
                        error += "The name has invalid characters.\n"
                    }            
                }
                if (cellPhone==null || cellPhone=="" || cellPhone=="Cell Phone Number"){
                    document.forms["formPayment"]["cellphone"].value='';
                }else {
                    var regexObj = /^\(?([2-9][0-8][0-9])\)?[ ]*[-.]?[ ]*([2-9][0-9]{2})[ ]*[-.]?[ ]*([0-9]{4})$/;
                    if (!regexObj.test(cellPhone)) {
                        error += "The phone number is invalid or in wrong format.\n"
                        error += "Entering your phone number is optional.\n"
                        error += "If you don't want to share your phone number, leave it blank.\n"
                    }
                }
                if (error!='') {
                    alert(error);
                    return false;
                }
            }

            function init(){
                var inputs=document.getElementsByTagName("input");
                for (var i=0;i<inputs.length;i++){
                    if (inputs[i].type!="submit") {
                        inputs[i].onfocus = function(){this.style.color='black'; if(this.value==this.name)this.value='';}
                        inputs[i].onblur = function(){if(this.value.replace(/\s/g,'')==''){this.style.color='gray';this.value=this.name;}}
                        if (inputs[i].value==''){
                            inputs[i].value = inputs[i].name;
                            inputs[i].style.color = "gray";
                        }
                    }
                }
            }   
        ]]></script>
        <xsl:copy-of select="$analytics-meta-css" />
    </head>
  
    <body onload="init()">
         <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div  class="post" style="border-bottom:none;">
                    <xsl:choose>
                        <xsl:when test="//Confirmation='TRUE'">
                            <h2 class="title">Thank you!</h2>
                            <div  style="width: 430px; float: left;margin-right:50px">        
                                <table id="table-detail" style="width:200px">
                                    <tr><th colspan="2"><xsl:value-of select="//Textmark/Title" /></th></tr>
                                    <tr>
                                        <td><img class="frontcover" src="{//MediumImage/URL}" /></td>
                                        <td>
                                            <p>Thank you for the payment.</p>
                                            <p>Please make an appointmant with <b><xsl:value-of select="//Textmark/SellerNickname" /></b>
                                            to receive your book as soon as possible:</p>
                                            <ul><li>Cellphone: <xsl:value-of select="//Textmark/SellerPhone" /></li>
                                            <li>Email: <xsl:value-of select="//Textmark/SellerEmail" /></li></ul>
                                            <p>We have sent you a confirmation email.</p>
                                            <p>Please confirm delivery of the book after '<xsl:value-of select="//Textmark/SellerNickname" />' hands over your book.</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </xsl:when>
                        <xsl:otherwise>
                            <h2 class="title">Pay the Best Price</h2>
                            <xsl:choose>
                                <xsl:when test="//Textmark/Rental='TRUE'">
                                    <xsl:call-template name="form">
                                        <xsl:with-param name="price" select="//Textmark/RentPrice" />
                                    </xsl:call-template>            
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:call-template name="form">
                                        <xsl:with-param name="price" select="//Textmark/UsedPrice" />
                                    </xsl:call-template>            
                                </xsl:otherwise>
                            </xsl:choose>                              
                        </xsl:otherwise>
                    </xsl:choose>

                    <div>
                        <div class="helpbox" style="float:right;width:250px;margin-bottom:30px;">
                            <strong>How it works...</strong>
                            <ol>
                                <li>You make the payment through <a href="https://www.paypal.com/" target="_blank">PayPal</a>.</li>
                                <li>We give you the contact information of other student that owns the book.</li>
                                <li>You contact the student and make an appointment in campus.</li>
                                <li>The student will hand over your book.</li>
                            </ol>
                        </div>
                       <div style="display:block;float:right;padding-right:40px">
                            <img src="resource/image/paypal.jpg" height="100px" width="200px" border="1"/>
                       </div> 
                    </div>
                </div>
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>

<xsl:template name="form" select="/">
<xsl:param name="price"/>
    
<div  style="width: 430px; float: left;margin-right:50px">
    <table id="table-detail" style="width:350px;margin:0px">
        <tr><th colspan="2"><xsl:value-of select="//Textmark/Title" /></th></tr>
        <tr>
            <td>
                <img class="frontcover" src="{//MediumImage/URL}" />
            </td>
            <td>
                <xsl:choose>
                    <xsl:when test="//Textmark/Payment=$price">
                        <strong>Price: $<xsl:value-of select="//Textmark/Payment" /></strong>
                    </xsl:when>
                    <xsl:when test="//Textmark/Payment &lt; $price and //Textmark/Payment &gt; 0">
                        <div>Book Price: $<xsl:value-of select="$price" /></div>
                        <div>Your Balance: $<xsl:value-of select="format-number($price - //Textmark/Payment, '####0.00')" /></div>
                        <hr/>
                        <strong>You pay: $<xsl:value-of select="//Textmark/Payment" /></strong>
                    </xsl:when>
                    <xsl:otherwise>
                        <div>Book Price: $<xsl:value-of select="$price" /></div>
                        <div>Your Balance: $<xsl:value-of select="$price - //Textmark/Payment" /></div>
                        <hr/>
                        <strong>You pay: $0</strong>
                    </xsl:otherwise>
                </xsl:choose>
       
                <br/>
                <br/>
                <form action="" id="formPayment" method="post" onsubmit="return validateForm()">
                    <label style="font-size:12px">Contact Information</label>
                    <input maxlength="25" name="name" style="width: 175px; " title="Valid name is required." type="text" value="{//Textmark/Nickname}" />   
                    <input maxlength="25" name="Cell Phone Number" style="width: 175px; " title="Cell phone number is optional" type="text" value="{//Textmark/CellPhone}" />
                    <input type="hidden" name="isbn" value="{//ItemAttributes/EAN}" />
                    <input type="hidden" name="payment" value="{//Textmark/Payment}" />
                    <br/><br/><br/>
                    <input type="hidden" name="form-submitted" value="1" />
                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" title="Secure Payment by PayPal" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"/>
                    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"/>
                </form>
            </td>
         </tr>
         <tfoot>
            <tr><td colspan="2">
                <ul>
                    <li>Entering your cellphone number is optional.</li>
                    <li>We reveal your contact information only to the other party.</li>
                    <li>We recommend you enter your phone number to speed up transaction.</li>
                    <li>The book is used and in good condition.</li>
                    <li>Paypal securely processes payments for TextMark.</li>
                    <li>If you don't have Paypal account, you can still check out by using your debit or credit card to pay.</li>
                </ul>
            </td></tr>
        </tfoot>
    </table>
</div>

</xsl:template>
</xsl:stylesheet>