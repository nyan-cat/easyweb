<?xml version="1.0" encoding="utf-8" ?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns:www="http://easyweb.com/" xmlns:func="http://exslt.org/functions" extension-element-prefixes="php func" exclude-result-prefixes="php www func">
    <xsl:output omit-xml-declaration="yes" encoding="utf-8" />
    <func:function name="www:access">
        <xsl:param name="expression" />
        <func:result select="php:function('wwwaccess', string($expression))" />
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
    <func:function name="www:local">
        <xsl:param name="alias" />
        <func:result select="php:function('wwwlocal', string($alias))" />
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
        <xsl:param name="xpath" select="'//*'" />
        <func:result select="php:function('wwwquery', string($name), string($args), string($xpath))" />
    </func:function>
    <func:function name="www:rfc-822">
        <xsl:param name="datetime" />
        <func:result select="php:function('wwwrfc822', string($datetime))" />
    </func:function>
    <func:function name="www:rfc-2822">
        <xsl:param name="datetime" />
        <func:result select="php:function('wwwrfc2822', string($datetime))" />
    </func:function>
    <func:function name="www:var">
        <xsl:param name="name" />
        <func:result select="php:function('wwwvar', string($name))" />
    </func:function>
</xsl:stylesheet>