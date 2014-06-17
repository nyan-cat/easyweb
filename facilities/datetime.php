<?php

class dt
{
    function mysql($string)
    {
        return date("Y-m-d H:m:s", self::parse($string));
    }

    private static function parse($string)
    {
        return is_numeric($string) ? $string : strtotime($string);
    }
}

?>