<?php

class json
{
    static function encode($value, $format = false)
    {
        $flags = $format ? (JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : JSON_UNESCAPED_UNICODE;
        return json_encode($value, $flags);
    }

    static function decode($json, $assoc = false)
    {
        return json_decode($json, $assoc);
    }
}

?>