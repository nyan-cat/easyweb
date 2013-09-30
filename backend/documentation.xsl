<?xml version="1.0" encoding="utf-8" ?>

<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:www="https://github.com/nyan-cat/easyweb"
    exclude-result-prefixes="php www">

    <xsl:template match="methods">
        <html>
            <head>
                <style>
                    body
                    {
                        font-family: "Arial";
                        text-align: center;
                    }

                    .body
                    {
                        display: inline-block;
                        text-align: left;
                        width: 1000px;
                    }

                    h1
                    {
                        font-weight: normal;
                    }

                    pre
                    {
                        background-color: #eee;
                        border: 1px solid #ddd;
                        border-radius: 0.25em;
                        padding: 1em;
                        text-align: left;
                    }

                    .special
                    {
                        font-style: italic;
                        color: #c00;
                    }

                    .param
                    {
                        color: #0c0;
                    }

                    .method
                    {
                        margin-bottom: 3em;
                    }

                    .method:last-child
                    {
                        margin-bottom: 0;
                    }
                </style>
                <title>Documentation</title>
            </head>
            <body>
                <div class="body">
                    <xsl:apply-templates />
                </div>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="method">
        <div class="method">
            <h1><xsl:value-of select="@url" /></h1>
            <div>
                Request example:
                <pre>
POST <xsl:value-of select="@url" /> HTTP/1.1<span class="special">\r\n</span>
Host: hostname<span class="special">\r\n</span>
<br />
<span class="special">\r\n</span>
<br />
<xsl:if test="count(param)"><xsl:for-each select="param">
<xsl:value-of select="@name" />=<span class="param">...</span><xsl:if test="position() != last()">&amp;</xsl:if>
</xsl:for-each></xsl:if>
                </pre>
            </div>
        </div>
    </xsl:template>
</xsl:stylesheet>