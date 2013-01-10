<?xml version="1.0" encoding="UTF-8"?>

<!--
    Document   : error.xsl
    Created on : May 26, 2012, 12:53 AM
    Author     : tahmineh
    Description:
        Purpose of transformation follows.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'A'" />

<xsl:template match="/">
 <html>
    <head>
        <title>FAQ</title>
        <xsl:copy-of select="$jquery" />
       <xsl:copy-of select="$analytics-meta-css" />
   </head>
    <body>
        <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <h2 class="title">Frequently Asked Questions</h2>
                    <div id="accordion">
                        <h3 class="question">What is TextMark?</h3>
                        <div class="answer">TextMark helps students in the same campus, directly sell or buy their textbooks from each other. 
                            It eliminates the cost of shipping, warehousing and fulfillment associated with college bookstore and online book sellers. 
                            As a result TextMark slashes the average cost of textbooks by half after trade-in. 
                            </div>

                        <h3 class="question">How does it work?</h3>
                        <div class="answer">
                            Let’s take Kate as a student that wants to purchase a used textbook through TextMark: 
                            <ol>
                                <li>Join: Kate joins TextMark by entering her college email and a password.</li>
                                <li>Find: She finds her book and clicks on ‘Buy Used’ button.</li>
                                <li>Pay: Kate pays the price of the book to TextMark through PayPal.</li>
                                <li>Inform: Kate receives contact information of a student named John who's selling the book.</li>
                                <li>Take: Kate contacts John and schedules an appointment with him for the next day to take the book.</li>
                            </ol>
                        </div>

                        <h3 class="question">How do I join?</h3>
                        <div class="answer">It's easy and free. Just enter your college email and choose a password.</div>

                        <h3 class="question">What’s the problem with selling my books through an online marketplace?</h3>
                        <div class="answer">It's too much of a hassle to sell your books through an online marketplace (Amazon Marketplace, eBay , etc.):
                            <ul>
                                <li>You have to keep an eye on changing sale prices and other competitors in the marketplace</li>
                                <li>You have to set your book price lower than others because there are many professional sellers with a five star rating.</li>
                                <li>You won't be paid as you expect because of high commission fee. For example, Amazon commission fee is about 30% of sale price.</li>
                            </ul>
                        </div>

                        <h3 class="question">What’s the problem with selling my books directly to Amazon or bookstores?</h3>
                        <div class="answer">Their buyback price is usually much lower than TextMark.</div>

                        <h3 class="question">Why selling my books through TextMark is a good idea?</h3>
                        <div class="answer">TextMark is a community of college students. Therefore all the students enjoy the same opportunity to sell their books. Moreover they don’t need to set a price for their books. TextMark always sets the buyback price higher than the current marketplace price. Students don’t have to compete with each other and their books are sold based on ‘first come, first serve’ principle.  In other words, students help each other make a great saving on textbooks.</div>

                        <h3 class="question">Why TextMark buyback price is the highest?</h3>
                        <div class="answer">You directly deliver your textbooks to the other student.  Therefore there’s no cost of shipping, warehousing and fulfillment.  Moreover TextMark overhead costs are very low.  As a result TextMark slashes average cost of textbooks by half after trade-in.  Students always receive the highest dollar value for their books.</div>

                        <h3 class="question">What’s the problem with buying a used book from an online marketplace?</h3>
                        <div class="answer">If you buy your books through an online marketplace (Amazon Marketplace, eBay, etc.), you have to consider delivery time and quality of the book.  You have to wait between 3 to 10 days to receive your book, and at that time if you are not satisfied with the quality of the book, you have to return it and wait another one or two weeks to receive a replacement .</div>

                        <h3 class="question">What’s the problem with buying a used book from Amazon or college book store?</h3>
                        <div class="answer">Their sale price is usually much higher than TextMark.</div>

                        <h3 class="question">Why buying used books from TextMark is a good idea?</h3>
                        <div class="answer">You buy the book for a low price and receive it in short time. Moreover you can check the quality of the book before getting that from the seller.</div>

                        <h3 class="question">Why TextMark used book price is always the lowest?</h3>
                        <div class="answer">TextMark constantly sets its price according to lowest sale price in the market.  Moreover students deliver the books directly to each other. Therefore there’s no cost of shipping, warehousing and fulfillment.  TextMark commission fee is very low.  As a result TextMark slashes the price of textbooks after trade-in by 50%.</div>

                        <h3 class="question">Why happens if there’s not any student buying my book in the campus?</h3>
                        <div class="answer">As soon as there is a buyer in campus, we ask them to pay for your book and then we give them your contact information to schedule delivery.</div>

                        <h3 class="question">Why happens if there’s not any student selling the used book I need?</h3>
                        <div class="answer">You should reserve the book by adding it to your Wishlist. As soon as there’s a seller in campus, we inform you by email.</div>
                </div>          
                </div>          
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>
</xsl:stylesheet>
