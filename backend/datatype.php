<?php

require_once('exception.php');

class datatype
{
    static function match($type, $value)
    {
        isset(self::$types[$type]) or backend_error('bad_type', "Unknown type: $type");

        $validator = self::$types[$type];

        if(is_string($validator))
        {
            return preg_match('/\A' . $validator . '\Z/', $value) == 1;
        }
        else if(is_object($validator) and ($validator instanceof Closure))
        {
            return $validator($value);
        }
        else if(is_array($validator))
        {
            return in_array($type, $validator);
        }
        else
        {
            backend_error('bad_type', 'Unknown validator type');
        }
    }

    static function assert($type, $value)
    {
        self::match($type, $value) or backend_error('bad_value', $value . ' doesn\'t match to ' . $type);
    }

    static function register($name, $type)
    {
        !isset(self::$types[$name]) or backend_error('Duplicate type: ' . $type);
        self::$types[$name] = $type;
    }

    private static $types =
    [
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
    ];
}

?>