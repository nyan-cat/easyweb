<?php

namespace datetime;

function mysql($string)
{
    return date('Y-m-d H:m:s', parse($string));
}

function solr($string)
{
    return date('Y-m-d\TH:m:s\Z', parse($string));
}

function parse($string)
{
    return is_numeric($string) ? $string : strtotime($string);
}

?>