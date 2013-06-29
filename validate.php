<?php

require_once('exception.php');

class validate
{
    static function get($type)
    {
        isset(self::$patterns[$type]) or runtime_error('Data type not found: ' . $type);
        return self::$patterns[$type];
    }

    static function match($type, $value)
    {
        isset(validate::$patterns[$type]) or runtime_error('Unknown pattern: ' . $type);
        return preg_match('/\A' . validate::$patterns[$type] . '\Z/', $value) == 1;
    }

    static function assert($type, $value)
    {
        $type == 'mixed' or validate::match($type, $value) or runtime_error($value . ' doesn\'t match to ' . $type . ' -> ' . validate::$patterns[$type]);
    }

    static function register($type, $pattern)
    {
        !isset(self::$patterns[$type]) or runtime_error('Duplicate type: ' . $type);
        self::$patterns[$type] = $pattern;
    }

    private static $patterns = array
    (
        'bool'            => '[01]',
        'natural'         => '[1-9]\d*',
        'int'             => '\-?\d+',
        'real'            => '\-?\d+\.?\d*',
        'uint'            => '\d+',
        'uint:list'       => '[\d,]+',
        'alpha2'          => '[a-z]{2}',
        'ipv4'            => '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}',
        'md5'             => '[\da-f]{32}',
        'key'             => '[\d\.]+',
        'tuple:list'      => '[\d\.,\(\)\-]+',
        'string'          => '.+',
        'string:optional' => '.*',
        'text'            => '[\s\S]+',
        'text:optional'   => '[\s\S]*',
        'email'           => '[\w\-\.]+@[\w\-\.]+'
    );
}

?>