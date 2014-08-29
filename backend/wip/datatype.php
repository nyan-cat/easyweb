<?php

require_once(www_root . 'error.php');

class datatype
{
    static function match($name, $value, $min = null, $max = null)
    {
        if($name == 'mixed')
        {
            return true;
        }

        if($name == 'object')
        {
            return is_object($value);
        }

        if($name == 'array')
        {
            return is_array($value);
        }

        isset(self::$types[$name]) or error('bad_parameter_type', "Unknown type: $name");

        if($min !== null or $max !== null)
        {
            $length = mb_strlen($value);

            if(($min !== null and $length < $min) or ($max !== null and $length > $max))
            {
                return false;
            }
        }

        $validator = self::$types[$name];

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
            return in_array($value, $validator);
        }
        else
        {
            error('bad_validator_type', "Unknown validator type: $name");
        }
    }

    static function assert($name, $value, $min = null, $max = null)
    {
        self::match($name, $value, $min, $max) or error('bad_parameter', $value . ' doesn\'t match to ' . $name . '[' . (is_null($min) ? '?' : $min) . ',' . (is_null($max) ? '?' : $max) . ']');
    }

    static function attach($name, $validator)
    {
        self::$types[$name] = $validator;
    }

    private static $types =
    [
        'bool'            => '[01]',
        'natural'         => '[1-9]\d*',
        'int'             => '\-?\d+',
        'real'            => '\-?\d+\.?\d*',
        'uint'            => '\d+',
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