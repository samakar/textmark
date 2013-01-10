<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
<xsl:include href="template.xsl"/>
<xsl:variable name="menu" select="'X'" />

<xsl:template match="/Root">
<html>
    <head>
        <title>Profile</title>
        <xsl:copy-of select="$analytics-meta-css" />
    </head>
  
    <body>
        <div id="wrapper">   
            <xsl:copy-of select="$header" />
            <div id="page">
                <div class="post">
                    <h2 class="title">Profile</h2>

                    <table id="table-detail" style="width:400px">
                        <tr><th colspan="2">Personel</th></tr>
                        <tr><td>name:</td><td><xsl:value-of select="Name" /></td></tr>
                        <tr><td>Email:</td><td><xsl:value-of select="Email" /></td></tr>
                        <tr><td>Cell Phone:</td><td><xsl:value-of select="CellPhone" /></td></tr>
                        <tr><td>Member Since:</td><td><xsl:value-of select="substring-before(Date, ' ')" /></td></tr>
                    </table>

                    <table id="table-detail" style="width:400px">
                        <tr><th colspan="2">Financial</th></tr>
                        <tr><td>Account Balance:</td><td>$<xsl:value-of select="Payable" /></td></tr>
                        <xsl:if test="Payable &gt; 0">
                            <tr><td colspan="2">
                                <form method="POST" action="">
                                    <input type="hidden" name="form-submitted" value="1" />
                                    <input type="submit" value="Pay Me Back"  class="form-submit-button"/>
                                </form>
                             </td></tr>
                        </xsl:if>
                    </table>

                </div>
            </div>
        </div>
        <xsl:copy-of select="$footer" />
    </body>
</html>
</xsl:template>
</xsl:stylesheet>
