<?php

class security
{
    static function initialize($algorithm, $salt)
    {
        self::$algorithm = $algorithm;
        self::$salt = $salt;
    }

    static function wrap($mixed)
    {
        return json_encode(['value' => $mixed, 'digest' => self::digest($mixed)]);
    }

    static function unwrap($wrapped)
    {
        $object = json_decode($wrapped);
        !is_null($object) or backend_error('bad_secure_parameter', 'Secure parameter is not a JSON-encoded object');
        isset($object['value']) or backend_error('bad_secure_parameter', 'Secure parameter has no value member');
        isset($object['digest']) or backend_error('bad_secure_parameter', 'Secure parameter has no digest member');
        $object['digest'] === self::digest($object['value']) or backend_error('bad_secure_parameter', 'Secure parameter digest is invalid');
        return $object['value'];
    }

    private static function digest($mixed)
    {
        return hash_hmac('sha512', json_encode($mixed), self::$salt);
    }

    private static $algorithm = 'md5'; # http://www.php.net/manual/en/function.hash-algos.php
    private static $salt = 'Initialize salt by calling security::initialize at the beginning of your main script';
}

?>