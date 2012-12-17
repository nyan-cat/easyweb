<?php

class session
{
    static function start()
    {
        session_start();
    }

    static function destroy()
    {
        session_destroy();
        if(isset($_SESSION))
        {
            unset($_SESSION);
        }
    }

    static function value($name, $value = null)
    {
        if(is_null($value))
        {
            self::assert($name);
            return $_SESSION[$name];
        }
        else
        {
            self::assert();
            $_SESSION[$name] = $value;
        }
    }

    static function vector($name, $value = null)
    {
        if(is_null($value))
        {
            self::assert($name);
            (($array = @unserialize($_SESSION[$name])) !== false and array_values($array) === $array) or runtime_error('Session fetched variable is not a vector: ' . $name);
            return $array;
        }
        else
        {
            self::assert();
            (is_array($value) and array_values($value) === $value) or runtime_error('Session stored variable is not a vector: ' . $name);
            $_SESSION[$name] = serialize($value);
        }
    }

    static function map($name, $value = null)
    {
        if(is_null($value))
        {
            self::assert($name);
            (($array = @unserialize($_SESSION[$name])) !== false and array_values($array) !== $array) or runtime_error('Session fetched variable is not a map: ' . $name);
            return $array;
        }
        else
        {
            self::assert();
            (is_array($value) and array_values($value) !== $value) or runtime_error('Session stored variable is not a map: ' . $name);
            $_SESSION[$name] = serialize($value);
        }
    }

    static function xml($name, $value = null)
    {
        if(is_null($value))
        {
            self::assert($name);
            (($xtree = @unserialize($_SESSION[$name])) !== false and $xtree instanceof xtree) or runtime_error('Session fetched variable is not an XML: ' . $name);
            return $xtree->xml();
        }
        else
        {
            self::assert();
            ($value instanceof xml) or runtime_error('Session stored variable is not an XML: ' . $name);
            $_SESSION[$name] = serialize(xtree::create($value));
        }
    }

    static private function assert($name = null)
    {
        isset($_SESSION) or runtime_error('Session is not started');
        if(!is_null($name))
        {
            isset($_SESSION[$name]) or runtime_error('Session variable not found: ' . $name);
        }
    }
}

?>