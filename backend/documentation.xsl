<?xml version="1.0" encoding="utf-8" ?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template match="methods">
        <html>
            <head>
                <script type="text/javascript" src="http://code.jquery.com/jquery-1.10.2.min.js"><![CDATA[]]></script>
                <style>
                    *:focus
                    {
                        outline: none;
                    }

                    body
                    {
                        background-color: #f6f8f4;
                        font-family: "Arial";
                        margin: 0;
                        padding: 0;
                        text-align: center;
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
                        background: #234;
                        box-shadow: 0 0 0.5em rgba(0, 0, 0, 0.5);
                    }

                    ul
                    {
                        list-style-type: none;
                        padding: 1em;
                        margin: 0;
                    }

                    ul li
                    {
                        font-family: "Consolas", "Courier new";
                        padding-bottom: 0.5em;
                    }

                    ul li:last-child
                    {
                        padding-bottom: 0;
                    }

                    .contents a, .contents a:visited
                    {
                        color: #def;
                        text-decoration: none;
                        border-bottom: 1px dotted #789;
                    }

                    .contents a.GET, .contents a.GET:visited
                    {
                        color: #6fb;
                        text-decoration: none;
                        border-bottom: 1px dotted #285;
                    }

                    .contents span.GET
                    {
                        color: #285;
                    }

                    .contents a.POST, .contents a.POST:visited
                    {
                        color: #6bf;
                        text-decoration: none;
                        border-bottom: 1px dotted #258;
                    }

                    .contents span.POST
                    {
                        color: #258;
                    }

                    .contents a.PUT, .contents a.PUT:visited
                    {
                        color: #fb6;
                        text-decoration: none;
                        border-bottom: 1px dotted #852;
                    }

                    .contents span.PUT
                    {
                        color: #852;
                    }

                    .contents a.DELETE, .contents a.DELETE:visited
                    {
                        color: #f6b;
                        text-decoration: none;
                        border-bottom: 1px dotted #825;
                    }

                    .contents span.DELETE
                    {
                        color: #825;
                    }

                    .contents a:hover, .contents a.GET:hover, .contents a.POST:hover, .contents a.PUT:hover, .contents a.DELETE:hover
                    {
                        color: #fff;
                        border-bottom-color: #9ab;
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
                        font-size: 0.9em;
                        margin: 0;
                        overflow: auto;
                        padding: 1em;
                        text-align: left;
                    }

                    form
                    {
                        margin: 0;
                        padding: 0;
                    }

                    .form
                    {
                        background-color: #efd;
                        border: 1px solid #dec;
                        border-radius: 0.25em;
                        padding: 1em;
                    }

                    input
                    {
                        border: 1px solid #9c9;
                        border-radius: 0.25em;
                        font-size: 0.9em;
                        padding: 0.5em;
                        margin-bottom: 1em;
                        width: 35em;
                    }

                    input:focus
                    {
                        border-color: #0c0;
                        box-shadow: 0 0 0.5em rgba(0, 255, 0, 0.25);
                    }

                    label
                    {
                        color: #696;
                        display: inline-block;
                        font-size: 1em;
                        margin-right: 0.5em;
                        text-align: right;
                        width: 10em;
                    }

                    button
                    {
                        background-color: #090;
                        border: 1px solid #080;
                        border-radius: 0.25em;
                        color: #fff;
                        cursor: pointer;
                        font-size: 0.9em;
                        font-weight: bold;
                        padding: 0.5em 2em;
                    }

                    button:hover
                    {
                        background-color: #0c0;
                        border-color: #0b0;
                    }

                    .hide
                    {
                        display: none;
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
                <script type="text/javascript">
                    String.prototype.endsWith = function(suffix)
                    {
                        return this.indexOf(suffix, this.length - suffix.length) !== -1;
                    };

                    $(document).ready(function()
                    {
                        $("a.request").click(function(e)
                        {
                            $("#" + $(this).data("form")).toggle("fast");
                            e.preventDefault();
                        });

                        $("form").submit(function(e)
                        {
                            function makeResponse(jqXHR, color, elapsed)
                            {
                                var headers = jqXHR.getAllResponseHeaders();
                                if(!headers.endsWith("\r\n\r\n"))
                                {
                                    headers += "\r\n";
                                }
                                return '<span style="color: ' + color + ';"><i>Request took ' + (elapsed / 1000) + ' sec</i>\r\n\r\nHTTP/1.1 ' + jqXHR.status + ' ' + jqXHR.statusText + '\r\n' + headers + '</span>' + jqXHR.responseText;
                            }

                            var data = {};
                            $(this).find("input").each(function()
                            {
                                data[$(this).attr("name")] = $(this).val();
                            });

                            var pre = "#" + $(this).data("pre");
                            var type = $(this).data("type").toUpperCase();

                            var start = new Date();

                            var options =
                            {
                                url : $(this).find(".url").val(),
                                type : type,
                                dataType : "text",
                                success : function(data, textStatus, jqXHR)
                                {
                                    var end = new Date();
                                    $(pre).html(makeResponse(jqXHR, "#666", end - start));
                                    if(!$(pre).is(":visible"))
                                    {
                                        $(pre).show("fast");
                                    }
                                },
                                error : function(jqXHR, textStatus)
                                {
                                    var end = new Date();
                                    $(pre).html(makeResponse(jqXHR, "#900", end - start));
                                    if(!$(pre).is(":visible"))
                                    {
                                        $(pre).show("fast");
                                    }
                                }
                            };

                            if(type != "GET")
                            {
                                options.data = $(this).serialize();
                            }

                            $.ajax(options);

                            e.preventDefault();
                        });
                    });
                </script>
            </head>
            <body>
                <table width="100%" height="100%" cellpadding="0" cellspacing="0" style="font-size: 1em;">
                    <tr>
                        <td valign="top" class="contents">
                            <div style="overflow-y: scroll; height: 100%;">
                                <ul>
                                    <xsl:for-each select="method">
                                        <li>
                                            <a href="#{@id}" title="{@type}" class="{@type}"><xsl:value-of select="@url" /></a><span class="{@type}" style="font-size: 0.8em;">&#160;<xsl:for-each select="(get | post)[position() &lt;= 3]"><xsl:value-of select="@name" /><xsl:if test="position() != last()">&#160;</xsl:if></xsl:for-each><xsl:if test="count(get | post) > 3"><span style="color: #567;"> ...</span></xsl:if></span>
                                        </li>
                                    </xsl:for-each>
                                </ul>
                            </div>
                        </td>
                        <td align="center" valign="top" width="100%">
                            <div style="overflow-y: scroll; height: 100%;">
                                <span style="display: inline-block; width: 1000px; text-align: left;">
                                    <xsl:apply-templates />
                                </span>
                            </div>
                        </td>
                    </tr>
                </table>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="method">
        <xsl:variable name="get"><xsl:if test="count(get)"><xsl:for-each select="get"><xsl:value-of select="@name" />=<span class="param">...</span><xsl:if test="position() != last()">&amp;</xsl:if></xsl:for-each></xsl:if></xsl:variable>
        <xsl:variable name="get-raw"><xsl:value-of select="$get" /></xsl:variable>
        <xsl:variable name="post"><xsl:if test="count(post)"><xsl:for-each select="post"><xsl:value-of select="@name" />=<span class="param">...</span><xsl:if test="position() != last()">&amp;</xsl:if></xsl:for-each></xsl:if></xsl:variable>
        <xsl:variable name="post-raw"><xsl:value-of select="$post" /></xsl:variable>
        <div class="method">
            <a name="{@id}"><![CDATA[]]></a>
            <h1><a href="#{@id}" class="sharp">#</a><xsl:value-of select="@url" /><sup class="small"><xsl:value-of select="@type" /></sup></h1>
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
                            <th>Domain</th>
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
                                <td><xsl:choose><xsl:when test="@domain"><b><xsl:value-of select="@domain" /></b></xsl:when><xsl:otherwise><span class="inactive">—</span></xsl:otherwise></xsl:choose></td>
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
                            <th>Domain</th>
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
                                <td><xsl:choose><xsl:when test="@domain"><b><xsl:value-of select="@domain" /></b></xsl:when><xsl:otherwise><span class="inactive">—</span></xsl:otherwise></xsl:choose></td>
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
            <div class="row">
                <xsl:variable name="url"><xsl:value-of select="@url" /><xsl:if test="count(get)">?<xsl:for-each select="get"><xsl:value-of select="@name" />=<xsl:if test="position() != last()">&amp;</xsl:if></xsl:for-each></xsl:if></xsl:variable>
                <h2><a href="#" class="inner request" data-form="form-{@id}">Try it out ↓</a></h2>
                <div id="form-{@id}" class="hide">
                    <div class="form">
                        <form method="{@type}" action="{@url}" data-type="{@type}" data-pre="result-{@id}">
                            <div>
                                <label>URL</label><input type="text" value="{$url}" placeholder="URL" class="url" />
                            </div>
                            <xsl:for-each select="post">
                                <div>
                                    <label><xsl:value-of select="@name" /></label><input type="text" name="{@name}" placeholder="{@name}" />
                                </div>
                            </xsl:for-each>
                            <div>
                                <label><![CDATA[]]></label><button>Invoke</button>
                            </div>
                        </form>
                    </div>
                    <pre id="result-{@id}" class="hide" style="margin-top: 1em;"></pre>
                </div>
            </div>
        </div>
    </xsl:template>
</xsl:stylesheet>