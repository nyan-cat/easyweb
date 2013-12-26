<?php

require_once('exception.php');

class datatype
{
    static function match($name, $value, $min = null, $max = null)
    {
        if($name == 'object')
        {
            return true;
        }

        isset(self::$types[$name]) or backend_error('bad_type', "Unknown type: $name");
        !is_null($min) or $min = self::min($name);
        !is_null($max) or $max = self::max($name);

        $type = self::$types[$name];

        $length = mb_strlen($value);

        if($length < $min or $length > $max)
        {
            return false;
        }

        $validator = $type['validator'];

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
            backend_error('bad_type', "Unknown validator type: $name");
        }
    }

    static function assert($name, $value, $min = null, $max = null)
    {
        self::match($name, $value, $min, $max) or backend_error('bad_value', $value . ' doesn\'t match to ' . $name . '[' . (is_null($min) ? '?' : $min) . ',' . (is_null($max) ? '?' : $max) . ']');
    }

    static function register($name, $min, $max, $validator)
    {
        !isset(self::$types[$name]) or backend_error('Duplicate type: ' . $name);
        self::$types[$name] = ['min' => $min, 'max' => $max, 'validator' => $validator];
    }

    static function min($name)
    {
        return self::$types[$name]['min'];
    }

    static function max($name)
    {
        return self::$types[$name]['max'];
    }

    private static $types =
    [
        'bool'            => ['min' => 1,  'max' => 1,     'validator' => '[01]'],
        'natural'         => ['min' => 1,  'max' => 50,    'validator' => '[1-9]\d*'],
        'int'             => ['min' => 1,  'max' => 50,    'validator' => '\-?\d+'],
        'real'            => ['min' => 1,  'max' => 50,    'validator' => '\-?\d+\.?\d*'],
        'uint'            => ['min' => 1,  'max' => 50,    'validator' => '\d+'],
        'uint:list'       => ['min' => 1,  'max' => 10000, 'validator' => '[\d,]+'],
        'alpha2'          => ['min' => 2,  'max' => 2,     'validator' => '[a-z]{2}'],
        'ipv4'            => ['min' => 7,  'max' => 15,    'validator' => '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}'],
        'md5'             => ['min' => 32, 'max' => 32,    'validator' => '[\da-f]{32}'],
        'key'             => ['min' => 1,  'max' => 1000,  'validator' => '[\d\.]+'],
        'tuple:list'      => ['min' => 1,  'max' => 1000,  'validator' => '[\d\.,\(\)\-]+'],
        'string'          => ['min' => 1,  'max' => 1000,  'validator' => '.+'],
        'string:optional' => ['min' => 0,  'max' => 1000,  'validator' => '.*'],
        'text'            => ['min' => 1,  'max' => 50000, 'validator' => '[\s\S]+'],
        'text:optional'   => ['min' => 0,  'max' => 50000, 'validator' => '[\s\S]*'],
        'email'           => ['min' => 6,  'max' => 100,   'validator' => '[\w\-\.]+@[\w\-\.]+'],
        'object'          => ['min' => 2,  'max' => 10000]
    ];
}

?>