<?xml version="1.0" encoding="utf-8" ?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns:www="https://github.com/nyan-cat/easyweb" xmlns:func="http://exslt.org/functions" extension-element-prefixes="php func" exclude-result-prefixes="php www func">
    <xsl:output omit-xml-declaration="yes" encoding="utf-8" />
    <func:function name="www:access">
        <xsl:param name="expression" />
        <func:result select="php:function('wwwaccess', string($expression), .)" />
    </func:function>
    <func:function name="www:base64-decode">
        <xsl:param name="string" />
        <func:result select="php:function('wwwbase64decode', string($string))" />
    </func:function>
    <func:function name="www:base64-encode">
        <xsl:param name="string" />
        <func:result select="php:function('wwwbase64encode', string($string))" />
    </func:function>
    <func:function name="www:crc32">
        <xsl:param name="string" />
        <func:result select="php:function('wwwcrc32', string($string))" />
    </func:function>
    <func:function name="www:escape-uri">
        <xsl:param name="uri" />
        <func:result select="php:function('wwwescapeuri', string($uri))" />
    </func:function>
    <func:function name="www:unescape-uri">
        <xsl:param name="uri" />
        <func:result select="php:function('wwwunescapeuri', string($uri))" />
    </func:function>
    <func:function name="www:evaluate">
        <xsl:param name="name" />
        <xsl:param name="args" select="''" />
        <func:result select="php:function('wwwevaluate', $name, string($args))" />
    </func:function>
    <func:function name="www:format-number">
        <xsl:param name="number" />
        <func:result select="php:function('wwwformatnumber', string($number))" />
    </func:function>
    <func:function name="www:local">
        <xsl:param name="alias" />
        <func:result select="php:function('wwwlocal', string($alias))" />
    </func:function>
    <func:function name="www:locale">
        <xsl:param name="name" />
        <func:result select="php:function('wwwlocale', string($name))" />
    </func:function>
    <func:function name="www:morph">
        <xsl:param name="template" />
        <xsl:param name="seed" />
        <func:result select="php:function('wwwmorph', string($template), string($seed))" />
    </func:function>
    <func:function name="www:paginate">
        <xsl:param name="current" />
        <xsl:param name="count" />
        <xsl:param name="size" />
        <func:result select="php:function('wwwpaginate', string($current), string($count), string($size))" />
    </func:function>
    <func:function name="www:regex-replace">
        <xsl:param name="subject" />
        <xsl:param name="find" />
        <xsl:param name="replace" />
        <func:result select="php:function('wwwregexreplace', string($subject), string($find), string($replace))" />
    </func:function>
    <func:function name="www:replace">
        <xsl:param name="subject" />
        <xsl:param name="find" />
        <xsl:param name="replace" />
        <func:result select="php:function('wwwreplace', string($subject), string($find), string($replace))" />
    </func:function>
    <func:function name="www:md5">
        <xsl:param name="string" />
        <func:result select="php:function('wwwmd5', string($string))" />
    </func:function>
    <func:function name="www:query">
        <xsl:param name="name" />
        <xsl:param name="args" select="''" />
        <func:result select="php:function('wwwquery', $name, string($args))" />
    </func:function>
    <func:function name="www:rfc-822">
        <xsl:param name="datetime" />
        <func:result select="php:function('wwwrfc822', string($datetime))" />
    </func:function>
    <func:function name="www:rfc-2822">
        <xsl:param name="datetime" />
        <func:result select="php:function('wwwrfc2822', string($datetime))" />
    </func:function>
    <func:function name="www:sequence">
        <xsl:param name="count" />
        <func:result select="php:function('wwwsequence', string($count))" />
    </func:function>
    <func:function name="www:session">
        <xsl:param name="type" />
        <xsl:param name="name" />
        <func:result select="php:function('wwwsession', string($type), string($name))" />
    </func:function>
    <func:function name="www:split">
        <xsl:param name="subject" />
        <xsl:param name="pattern" />
        <func:result select="php:function('wwwsplit', string($subject), string($pattern))" />
    </func:function>
    <func:function name="www:tokenize">
        <xsl:param name="subject" />
        <xsl:param name="pattern" />
        <func:result select="php:function('wwwtokenize', string($subject), string($pattern))" />
    </func:function>
    <func:function name="www:var">
        <xsl:param name="name" />
        <func:result select="php:function('wwwvar', string($name))" />
    </func:function>
</xsl:stylesheet>