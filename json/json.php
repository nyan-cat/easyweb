<?php

namespace json;

function encode($value, $options = JSON_UNESCAPED_UNICODE)
{
    return json_encode($value, $options);
}

function decode($json, $assoc = false)
{
    return json_decode($json, $assoc);
}

?>