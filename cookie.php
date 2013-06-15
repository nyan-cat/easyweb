<?php

class cookie
{
    static function start($salt, $expire = null, $domain = null, $path = '/')
    {
        self::$salt = $salt;
        self::$expire = $expire;
        self::$domain = $domain;
        self::$path = $path;
    }

    static function get($name)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    static function set($name, $value, $expire = null, $domain = null, $path = null)
    {
        setcookie
        (
            $name,
            $value,
            is_null($expire) ? (is_null(self::$expire) ? 0 : (time() + self::$expire)) : (time() + $expire),
            is_null($path) ? self::$path : $path,
            is_null($domain) ? (is_null(self::$domain) ? '' : self::$domain) : $domain
        );
    }

    static function get_signed($name)
    {
        $value = self::get($name);
        $digest = self::get($name . '_digest');

        if(is_null($value) || is_null($digest))
        {
            return null;
        }

        $string = $value . $name . self::$salt . $_SERVER['HTTP_USER_AGENT'];
        $hash = hash('sha512', $string);
        $hash = hash('sha512', $hash . $string);

        if($digest === $hash)
        {
            return $value;
        }

        $string = $value . $name . self::$salt . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'];
        $hash = hash('sha512', $string);
        $hash = hash('sha512', $hash . $string);

        return $digest === $hash ? $value : null;
    }

    static function set_signed($name, $value, $bind_to_ip = false, $expire = null, $domain = null, $path = null)
    {
        $string = $value . $name . self::$salt . $_SERVER['HTTP_USER_AGENT'] . ($bind_to_ip ? $_SERVER['REMOTE_ADDR'] : '');
        $digest = hash('sha512', $string);
        $digest = hash('sha512', $digest . $string);

        self::set($name, $value, $expire, $domain, $path);
        self::set($name . '_digest', $digest, $expire, $domain, $path);
    }

    static function erase($name)
    {
        setcookie($name, '', 1);
        setcookie($name . '_digest', '', 1);
    }

    private static $salt = 'Use cookie::start to initialize salt with unique string';
    private static $expire;
    private static $domain;
    private static $path;
}

?>