<?php

require_once('exception.php');

class post
{
    static function exists($name)
    {
        if(is_array($name))
        {
            foreach($name as $key)
            {
                if(!isset($_POST[$key]))
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return isset($_POST[$name]);
        }
    }

    static function checkbox($name)
    {
        return isset($_POST[$name]) and strtolower($_POST[$name]) == 'on';
    }

    static function radio($name)
    {
        isset($_POST[$name]) or runtime_error('POST radio not found: ' . $name);
        return $_POST[$name];
    }

    static function text($name)
    {
        isset($_POST[$name]) or runtime_error('POST text not found: ' . $name);
        return $_POST[$name];
    }

    static function password($name)
    {
        isset($_POST[$name]) or runtime_error('POST password not found: ' . $name);
        return $_POST[$name];
    }

    static function hidden($name)
    {
        isset($_POST[$name]) or runtime_error('POST hidden not found: ' . $name);
        return $_POST[$name];
    }

    static function textarea($name)
    {
        isset($_POST[$name]) or runtime_error('POST textarea not found: ' . $name);
        return $_POST[$name];
    }

    static function select($name)
    {
        isset($_POST[$name]) or runtime_error('POST select not found: ' . $name);
        return $_POST[$name];
    }
}

?>