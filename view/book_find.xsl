<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:include href="book_detail.xsl"/>
<xsl:include href="book_td.xsl"/>
<xsl:variable name="menu" select="'F'" />

<xsl:template match="/">

<xsl:variable name="found">
    <xsl:choose>
        <xsl:when test="count(ItemLookupResponse/Items)=0 and count(List/ItemLookupResponse)=0">FALSE</xsl:when>
        <xsl:otherwise>TRUE</xsl:otherwise>
    </xsl:choose>
</xsl:variable>

<xsl:variable name="show">
    <xsl:choose>
        <xsl:when test="$logged='FALSE' and $found='FALSE'">TRUE</xsl:when>
        <xsl:otherwise>FALSE</xsl:otherwise>
    </xsl:choose>
</xsl:variable>

<html xmlns:fb="http://ogp.me/ns/fb#">
    <head>
        <title>
            <xsl:choose>
                <xsl:when test="count(ItemLookupResponse/Items/Item/ItemAttributes/Title)=0">Buy and Sell Textbooks | TextMark.net</xsl:when>
                <xsl:otherwise><xsl:value-of select="//Textmark/Title" /></xsl:otherwise>
            </xsl:choose>
        </title>
        <xsl:copy-of select="$jquery" />
        <xsl:if test="$show='TRUE'">
            <link rel="stylesheet" type="text/css" media="screen" href="resource/css/coda-slider.css"/>
            <script src="resource/js/jquery.coda-slider-3.0.js"/>
        </xsl:if>   
        <script type="text/javascript"><![CDATA[
            function backToTop() {
                scroll(0,0);      
            }

            function validateForm() {
                var term =document.forms["formFind"]["ISBN"].value;
                var id =document.forms["formFind"]["ISBN"].id;
                if (term==id){
                    return false;
                }
            }

            function init(){
                var inputs=document.getElementsByTagName("input");
                for (var i=0;i<inputs.length;i++){
                    if (inputs[i].type!="submit") {
                        inputs[i].onfocus = function(){this.style.color='black'; if(this.value==this.alt)this.value='';}
                        inputs[i].onblur = function(){if(this.value.replace(/\s/g,'')==''){this.style.color='gray';this.value=this.alt;}}
                        if (inputs[i].value==''){
                            inputs[i].value = inputs[i].alt;
                            inputs[i].style.color = "gray";
                        }
                    }
                }
            } 
            
            // breake iframe as in facebook app
            if (top.location!= self.location){
                top.location = self.location
            }
            
            //slider
            $().ready(function(){
                $('#slider-id').codaSlider({
                    autoSlide:true,
                    autoHeight:false,
                    autoSlideInterval:2000,
                    dynamicTabsAlign:"left",
                    dynamicTabsPosition: "bottom",
                    dynamicArrows: false,
                    slideDelayDuration: 5000
                });
            });
            
            //show more less
            $(window).load(function(){
                $(".show-more a").on("click", function() {
                    var $link = $(this);
                    var $content = $link.parent().prev("div.text-content");
                    var linkText = $link.text();

                    switchClasses($content);

                    $link.text(getShowLinkText(linkText));

                    return false;
                });

                function switchClasses($content){
                    if($content.hasClass("short-text")){  
                        $content.switchClass("short-text", "full-text", 400);
                    } else {
                        $content.switchClass("full-text", "short-text", 400);
                    }
                }

                function getShowLinkText(currentText){
                    var newText = '';

                    if (currentText.toUpperCase() === "SHOW MORE") {
                        newText = "Show less";
                    } else {
                        newText = "Show more";
                    }

                    return newText;
                }
            });
        ]]></script>
        <!--               FB Open Graph Tags            -->
        <meta property="og:title" content="TextMark" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://textmark.net" />
        <meta property="og:image" content="http://textmark.net/resource/image/book.png" />
        <meta property="og:site_name" content="TextMark" />
        <meta property="fb:admins" content="1317041158" />
        <meta property="og:description" content="TextMark helps students in the same campus, sell or buy textbooks directly from each other."/>    
        <meta property="fb:app_id" content="167262410055462"/>
        <!--               FB Open Graph Tags            -->
        <xsl:copy-of select="$analytics-meta-css" />
   </head>
  
    <body onload="init()">
                    <!--               FB SDK            -->                
        <xsl:if test="$found='FALSE'">
            <xsl:copy-of select="$fbSDK" />
        </xsl:if>

        <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <!--               INTRODUCTION            -->                
                    <xsl:if test="$show='TRUE'">
                    <p style="font-size:90%;font-style:italic;text-align:center">TextMark helps you sell or buy textbooks directly from other students in campus. It's easy and free. Make and save big money!</p>
                        <div class="helpbox">       
                            <table><tr>
                                <td width="200px"><a href="index.php?op=user_join"><img width="120" height="120" src='resource/image/book.png' class="imglink" style="padding:20px 20px 5px 20px"/></a>                                    
                                <p class="slidetext" style="padding-left:25px"><strong><a href="index.php?op=user_join">Join TextMark</a></strong></p>
                            </td>
                            <td>
                            <div class="coda-slider"  id="slider-id">
                                <div>
                                    <h3 class="slidetitle">It's Efficient</h3>
                                    <p class="slidetext">TextMark helps students in the same campus, directly sell or buy textbooks from each other. TextMark eliminates shipping, warehousing and fulfillment associated with college bookstore and online book sellers. </p>
                                    <h2 class="title" style="visibility:hidden;font-size:20px">Efficient</h2>
                                </div>
                                <div>
                                    <h3 class="slidetitle">It Saves Money</h3>
                                    <p class="slidetext">TextMark helps students slash their spending on textbooks by half after trade-in. If students cannot get a book from their peers, TextMark finds the cheapest one in the marketplace.</p>
                                    <br/>
                                    <h2 class="title" style="visibility:hidden;font-size:20px">Valuable</h2>
                                </div>
                                <div>
                                    <h3 class="slidetitle">It's Green</h3>
                                    <p class="slidetext">It's best of breed: textbooks are recycled locally in campus while operation is managed online. It promotes sustainability and leaves no carbon footprint on the environment.</p>
                                    <h2 class="title" style="visibility:hidden;font-size:20px">Green</h2>
                                </div>
                                <div>
                                    <h3 class="slidetitle">It's Free and Simple</h3>
                                    <p class="slidetext">All you have to do is to enter your college email and a password. 
                                    Then make a list of the books you have and a list of the books you want. TextMark will find the best match for you!</p>
                                    <h2 class="title" style="visibility:hidden;font-size:20px">Free</h2>
                                </div>
                            </div>
                            </td></tr></table>
                        </div>
                    </xsl:if>

                    <!--               SEARCH BOX   & FB         -->
                    <div>
                        <div style="width: 350px;display:inline-block">
                            <h3 class="title">Find Book</h3>
                            <form method="POST" action="" name="formFind" onsubmit="return validateForm()">
                                <input type="text" name="ISBN" alt="Enter ISBN, title or auther's name" class="form-textbox" style="margin-right:5px;width:250px">
                                    <xsl:attribute name="value">
                                        <xsl:value-of select="ItemLookupResponse/ISBN" />
                                    </xsl:attribute>
                                </input>
                                <input type="hidden" name="form-submitted" value="1" />
                                <input type="submit" value="FIND" class="form-submit-button"/>
                            </form>
                        </div>
                        <xsl:if test="$found='FALSE'">
                            <div style="width:410px;display:inline-block;padding-left:50px;">
                                <fb:like href="http://www.facebook.com/textmark" send="true" width="360" show_faces="true"></fb:like>
                            </div>
                       </xsl:if>
                 </div>
                    <!--               ERROR            -->

                    <xsl:if test="count(ItemLookupResponse/Error) &gt; 0">
                        <p class="form-error"><xsl:value-of select="//Error" /></p>
                        <p>Please check ISBN value and retry your request.</p>
                    </xsl:if>            

                    <!--            BOOK DETAIL  ISBN       -->
                    <div>
                        <xsl:if test="count(ItemLookupResponse/Items) &gt; 0">     
                            <xsl:apply-templates select="ItemLookupResponse/Items/Item"/>
                        </xsl:if>
                    </div>

                    <!--            BOOK DETAIL Search       -->
                    <xsl:for-each select="//List/ItemLookupResponse/Items/Item">
                        <div>
                            <xsl:call-template name="book_detail" />
                            <img src='resource/image/top.png' class="imglink" onclick="backToTop()" title='Back to Top' style='margin-left:45px;cursor:pointer;'/>
                        </div>
                    </xsl:for-each>
                    
                    <!--            Recommendation         -->

                    <br/>
                    <hr/>               
                    <xsl:if test="count(//Recommendation) &gt; 0">
                        <p style="font-size:90%;font-style:italic">What students are looking for...</p>
                        <table id="table-recommendation">
                            <tr><xsl:for-each select="//Recommendation/ItemLookupResponse">
                                <xsl:call-template select="ItemLookupResponse" name="td-image"/>    
                            </xsl:for-each></tr>
                        </table>
                    </xsl:if>
                   
                </div>
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>

</xsl:stylesheet>