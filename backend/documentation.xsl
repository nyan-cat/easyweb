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
                        background-color: #f6f8f4;
                        font-family: "Arial";
                        text-align: center;
                    }

                    .body
                    {
                        display: inline-block;
                        text-align: left;
                        width: 1000px;
                    }

                    a.inner, a.inner:visited
                    {
                        border-bottom: 1px dotted #08b;
                        color: #08b;
                        text-decoration: none;
                    }

                    a.sharp, a.sharp:visited
                    {
                        color: #eee;
                        float: right;
                        font-family: "Tahoma";
                        font-size: 0.75em;
                        font-weight: bold;
                        text-decoration: none;
                        text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.5);
                    }

                    a.sharp:hover
                    {
                        color: #f66;
                        text-shadow: 1px 1px 0 rgba(102, 0, 0, 0.5);
                    }

                    a.inner:hover
                    {
                        border-color: #e00;
                        color: #e00;
                    }

                    .contents
                    {
                        display: inline-block;
                        position: fixed;
                        left: 1em;
                        top: 50%;
                    }

                    ol
                    {
                        border-radius: 0.25em;
                        border: 1px solid #ddd;
                        padding: 1em;
                    }

                    ol li
                    {
                        margin-left: 1.5em;
                        padding-bottom: 1em;
                    }

                    ol li:last-child
                    {
                        padding-bottom: 0;
                    }

                    h1
                    {
                        font-size: 2em;
                        font-weight: bold;
                        font-family: "Consolas", "Courier new";
                        margin: 0;
                        padding-bottom: 0.75em;
                    }

                    sup.small
                    {
                        color: #999;
                        font-family: "Arial";
                        font-size: 0.4em;
                        font-weight: bold;
                        margin-left: 0.5em;
                    }

                    h2
                    {
                        color: #090;
                        font-size: 1.25em;
                        font-weight: normal;
                        margin: 0;
                        padding-bottom: 0.5em;
                    }

                    pre
                    {
                        background-color: #eee;
                        border: 1px solid #ddd;
                        border-radius: 0.25em;
                        font-family: "Courier new";
                        margin: 0;
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

                    .inactive
                    {
                        color: #999;
                    }

                    .method
                    {
                        background-color: #fff;
                        border-radius: 0.5em;
                        box-shadow: 0 0 1em rgba(0, 0, 0, 0.15);
                        margin: 3em;
                        padding: 2em;
                    }

                    .row
                    {
                        margin-bottom: 2em;
                    }

                    .row:last-child
                    {
                        margin-bottom: 0;
                    }

                    table
                    {
                        border-collapse: collapse;
                        font-size: 0.9em;
                    }

                    td
                    {
                        padding: 0;
                        white-space: nowrap;
                    }

                    .table
                    {
                        margin: 0;
                        padding: 0;
                        width: 100%;
                    }

                    .table th, .table td
                    {
                        border-bottom: 1px solid #ddd;
                        border-right: 1px solid #ddd;
                        padding: 0.75em 2em 0.75em 0.75em;
                    }

                    .table th:last-child, .table td:last-child
                    {
                        border-right: none;
                    }

                    .table th
                    {
                        text-align: left;
                    }
                </style>
                <title>Documentation</title>
            </head>
            <body>
                <div class="body">
                    <!--<div class="contents">
                        <ol>
                            <xsl:for-each select="method">
                                <li>
                                    <a href="#{@url}" class="inner"><xsl:value-of select="@url" /></a>
                                </li>
                            </xsl:for-each>
                        </ol>
                    </div>-->
                    <xsl:apply-templates />
                </div>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="method">
        <xsl:variable name="get"><xsl:if test="count(get)"><xsl:for-each select="get"><xsl:value-of select="@name" />=<span class="param">...</span><xsl:if test="position() != last()">&amp;</xsl:if></xsl:for-each></xsl:if></xsl:variable>
        <xsl:variable name="get-raw"><xsl:value-of select="$get" /></xsl:variable>
        <xsl:variable name="post"><xsl:if test="count(post)"><xsl:for-each select="post"><xsl:value-of select="@name" />=<span class="param">...</span><xsl:if test="position() != last()">&amp;</xsl:if></xsl:for-each></xsl:if></xsl:variable>
        <xsl:variable name="post-raw"><xsl:value-of select="$post" /></xsl:variable>
        <div class="method">
            <a name="{@url}"><![CDATA[]]></a>
            <h1><a href="#{@url}" class="sharp">#</a><xsl:value-of select="@url" /><sup class="small"><xsl:value-of select="@type" /></sup></h1>
            <xsl:if test="count(get)">
                <div class="row">
                    <h2>GET-parameters</h2>
                    <table class="table">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Required</th>
                            <th>Default</th>
                            <th>Secure</th>
                        </tr>
                        <xsl:for-each select="get">
                            <tr>
                                <td width="100%"><xsl:value-of select="@name" /></td>
                                <td><xsl:value-of select="@type" /></td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="number(@min) = @min">
                                            <b><xsl:value-of select="@min" /></b>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <span class="inactive"><xsl:value-of select="@min" /></span>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="number(@max) = @max">
                                            <b><xsl:value-of select="@max" /></b>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <span class="inactive"><xsl:value-of select="@max" /></span>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </td>
                                <td><xsl:choose><xsl:when test="@required = 'true'"><b>true</b></xsl:when><xsl:otherwise><span class="inactive">false</span></xsl:otherwise></xsl:choose></td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="@default">
                                            <b><xsl:value-of select="@default" /></b>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <span class="inactive">—</span>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </td>
                                <td><xsl:choose><xsl:when test="@secure = 'true'"><b>true</b></xsl:when><xsl:otherwise><span class="inactive">false</span></xsl:otherwise></xsl:choose></td>
                            </tr>
                        </xsl:for-each>
                    </table>
                </div>
            </xsl:if>
            <xsl:if test="count(post)">
                <div class="row">
                    <h2>POST-parameters</h2>
                    <table class="table">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Required</th>
                            <th>Default</th>
                            <th>Secure</th>
                        </tr>
                        <xsl:for-each select="post">
                            <tr>
                                <td width="100%"><xsl:value-of select="@name" /></td>
                                <td><xsl:value-of select="@type" /></td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="number(@min) = @min">
                                            <b><xsl:value-of select="@min" /></b>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <span class="inactive"><xsl:value-of select="@min" /></span>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="number(@max) = @max">
                                            <b><xsl:value-of select="@max" /></b>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <span class="inactive"><xsl:value-of select="@max" /></span>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </td>
                                <td><xsl:choose><xsl:when test="@required = 'true'"><b>true</b></xsl:when><xsl:otherwise><span class="inactive">false</span></xsl:otherwise></xsl:choose></td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="@default">
                                            <b><xsl:value-of select="@default" /></b>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <span class="inactive">—</span>
                                        </xsl:otherwise>
                                    </xsl:choose></td>
                                <td><xsl:choose><xsl:when test="@secure = 'true'"><b>true</b></xsl:when><xsl:otherwise><span class="inactive">false</span></xsl:otherwise></xsl:choose></td>
                            </tr>
                        </xsl:for-each>
                    </table>
                </div>
            </xsl:if>
            <div class="row">
                <h2>Request example</h2>
                <pre>
<xsl:value-of select="@type" /><![CDATA[ ]]><xsl:value-of select="@url" /><xsl:if test="count(get)">?<xsl:copy-of select="$get" /></xsl:if> HTTP/1.1<span class="special">\r\n</span>
Host: website.com<span class="special">\r\n</span>
<br />
<xsl:if test="string-length($post-raw)">Content-Length: <xsl:value-of select="string-length($post-raw)" /><span class="special">\r\n</span><br /></xsl:if>
<span class="special">\r\n</span>
<br />
<xsl:if test="string-length($post-raw)"><xsl:copy-of select="$post" /></xsl:if>
                </pre>
            </div>
        </div>
    </xsl:template>
</xsl:stylesheet>