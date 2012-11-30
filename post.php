<?php

class post
{
    static function checkbox($name)
    {
        return strtolower($_POST[$name]) == 'on';
    }

    static function radio($name)
    {
        return $_POST[$name];
    }

    static function text($name)
    {
        return $_POST[$name];
    }

    static function password($name)
    {
        return $_POST[$name];
    }

    static function textarea($name)
    {
        return $_POST[$name];
    }

    static function select($name)
    {
        return $_POST[$name];
    }
}

?>