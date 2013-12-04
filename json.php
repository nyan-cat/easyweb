<?php

class json
{

    static function encode($value)
    {
        return json_encode($value);
    }

    static function decode($json, $assoc = false)
    {
        return json_decode($json, $assoc);
    }
}

?>