<?php

class cookie
{
    static function start($salt, $path = '/', $expire = null, $domain = null)
    {
        self::$salt = $salt;
        self::$path = $path;
        self::$expire = $expire;
        self::$domain = $domain;
    }

    static function get($name)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    static function set($name, $value, $path = null, $expire = null, $domain = null)
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

        $string = $value . self::$salt . $_SERVER['HTTP_USER_AGENT'];
        $hash = hash('sha512', $hash);
        $hash = hash('sha512', $hash . $string);

        if($digest === $hash)
        {
            return $value;
        }

        $string = $value . self::$salt . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['HTTP_REMOTE_ADDR'];
        $hash = hash('sha512', $hash);
        $hash = hash('sha512', $hash . $string);

        return $digest === $hash ? $value : null;
    }

    static function set_signed($name, $value, $bind_to_ip = false, $path = null, $expire = null, $domain = null)
    {
        $string = $value . self::$salt . $_SERVER['HTTP_USER_AGENT'] . $bind_to_ip ? $_SERVER['HTTP_REMOTE_ADDR'] : '';
        $digest = hash('sha512', $string);
        $digest = hash('sha512', $digest . $string);

        self::set($name, $value, $path, $expire, $domain);
        self::set($name . '_digest', $digest, $path, $expire, $domain);
    }

    private static $salt = 'Use cookie::start to initialize salt with unique string';
    private static $path;
    private static $expire;
    private static $domain;
}

?>