<?php

class bbcode
{
    static function parse($node, $allow = null, $deny = null)
    {
        $find = array();
        $replace = array();
        foreach(self::$find as $n => $rule)
        {
            list($code, $expression) = $rule;

            $allowed = !$allow || ($allow && in_array($code, $allow));
            $denied = $deny && in_array($code, $deny);

            if($allowed && !$denied)
            {
                $find[] = $expression;
                $replace[] = self::$replace[$n];
            }
        }

        $xml = new xml();
        foreach($node->children() as $child)
        {
            $xml->append($xml->import($child));
        }
        foreach($xml->query('//text()') as $text)
        {
            $parent = $text->parent();
            $bbcode = preg_replace($find, $replace, $text->value());
            $bbcode = xml::parse("<?xml version=\"1.0\" encoding=\"utf-8\" ?><bbcode>$bbcode</bbcode>");
            foreach($bbcode->query('/bbcode/* | /bbcode/text()') as $child)
            {
                $parent->insert($xml->import($child), $text);
            }
            $parent->remove($text);
        }
        return $xml;
    }

    static private $find = array
    (
        array('b', "/\[b\](.+?)\[\/b\]/"),
        array('i', "/\[i\](.+?)\[\/i\]/"),
        array('u', "/\[u\](.+?)\[\/u\]/"),
        array('s', "/\[s\](.+?)\[\/s\]/"),
        array('img', "/\[img\](.+?)\[\/img\]/"),
        array('url', "/\[url=(.+?)\](.+?)\[\/url\]/"),
        array('url', "/\[url\](.+?)\[\/url\]/"),
        array('quote', "/\[quote\](.+?)\[\/quote\]/"),
        array('code', "/\[code\](.+?)\[\/code\]/")
    );

    static private $replace = array
    (
        '<b>\1</b>',
        '<i>\1</i>',
        '<u>\1</u>',
        '<del>\1</del>',
        '<img src="\1" title="" />',
        '<a href="\1">\2</a>',
        '<a href="\1">\1</a>',
        '<blockquote>\1</blockquote>',
        '<pre>\1</pre>'
    );
}

?>