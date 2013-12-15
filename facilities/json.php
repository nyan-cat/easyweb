<?php

class json
{
    static function encode($value)
    {
        return json_encode($value, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    static function decode($json, $assoc = false)
    {
        return json_decode($json, $assoc);
    }
}

?>