<?xml version="1.0" encoding="UTF-8"?>

<!--
    Document   : menu.xsl
    Created on : May 30, 2012, 3:06 AM
    Author     : Amir
    Description: top menu
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:param name="logged"/>
<xsl:param name="username"/>
<xsl:param name="collegename"/>
<xsl:param name="collegedomain"/>
<xsl:param name="showcasehide"/>

<xsl:variable name="header">
    <div id="header">
        <div id="logo">
            <a href="index.php">TextMark</a>
        </div>
        <div id="logo" style="display:block;float:right;margin-top:10px;font-size:25px;padding:0px;text-align:right;">  
            <span>
                <a href="http://{$collegedomain}" target="_blank"><xsl:value-of select="$collegename" /></a>
            </span>
            <br/>
            <span id="join">
                <xsl:choose>
                    <xsl:when test="$logged='FALSE'">
                            <a href="index.php?op=user_login">Login</a>
                            /<a href="index.php?op=user_join">Join</a>
                    </xsl:when>
                    <xsl:otherwise>
                            Hello:<a href="index.php?op=user_profile" title="Your Profile" ><xsl:value-of select="$username" /></a>
                            |<a href="index.php?op=user_logout" >Logout</a>
                    </xsl:otherwise>
                </xsl:choose>
            </span>                        
        </div>
    </div>
    <!-- end #header -->
    <div id="menu">
        <ul>
            <li>
                <xsl:if test="$menu='F'">
                    <xsl:attribute name="class">current_page_item</xsl:attribute>
                </xsl:if>
                <a href="index.php?op=book_find">Find</a>
            </li>
            <xsl:if test="$showcasehide='FALSE'">
                <li>
                    <xsl:if test="$menu='B'">
                        <xsl:attribute name="class">current_page_item</xsl:attribute>
                    </xsl:if>
                    <a href="index.php?op=book_showcase">Buy</a>
                </li>
            </xsl:if>
            <li>
                <xsl:if test="$menu='S'">
                    <xsl:attribute name="class">current_page_item</xsl:attribute>
                </xsl:if>
                <a href="index.php?op=book_show&amp;wishlist=FALSE">Sell</a>
            </li>
            <li>
                <xsl:if test="$menu='W'">
                    <xsl:attribute name="class">current_page_item</xsl:attribute>
                </xsl:if>
                <a href="index.php?op=book_show&amp;wishlist=TRUE">Wish List</a>
            </li>
            <li style="float:right;">
                <xsl:if test="$menu='A'">
                    <xsl:attribute name="class">current_page_item</xsl:attribute>
                </xsl:if>
                <a href="index.php?op=user_faq">FAQ</a>
            </li>
            <li style="float:right;">
                <xsl:if test="$menu='N'">
                    <xsl:attribute name="class">current_page_item</xsl:attribute>
                </xsl:if>
                <a href="index.php?op=user_feedback">Feedback</a>
            </li>
        </ul>
    </div>
    <!-- end #menu -->
</xsl:variable>


<xsl:variable name="footer">
<div id="footer">
	<p>
            Copyright &#169; &#xA0; 2013 textmark.net &#xA0; All Rights Reserved
            <br/><a href="mailto:student@textmark.net">Contact Us</a>
        </p>
</div>
<!-- end #footer -->
</xsl:variable>

<xsl:variable name="fbSDK">
    <div id="fb-root"></div>
    <script><![CDATA[
        window.fbAsyncInit = function() {
            FB.init({
            appId      : '167262410055462',
            channelUrl : '//textmark.net/channel.php',
            status     : true,
            cookie     : true,
            xfbml      : true  // parse XFBML
            });

            // Additional initialization code here
        };

        // Load the SDK Asynchronously
        (function(d){
            var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement('script'); js.id = id; js.async = true;
            js.src = "//connect.facebook.net/en_US/all.js";
            ref.parentNode.insertBefore(js, ref);
        }(document));
    ]]></script>
</xsl:variable>

<!-- Google Analytics tracking code -->

<xsl:variable name="analytics-meta-css">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link href="resource/css/style.css" rel="stylesheet" type="text/css" media="screen" />
    <script type="text/javascript"><![CDATA[
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-33917856-1']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();

    ]]></script>
</xsl:variable>

<xsl:variable name="jquery">
    <link type="text/css" href="resource/css/ui-lightness/jquery-ui-1.8.23.custom.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"/>
    <script type="text/javascript"><![CDATA[
        $(function() {
            $( "#accordion" ).accordion({
                    autoHeight: false,
                    navigation: true
            });
        });
	$(function() {
            $( "#tabs" ).tabs({
                    event: "mouseover"
            });
	});
    ]]></script>
</xsl:variable>

</xsl:stylesheet>
