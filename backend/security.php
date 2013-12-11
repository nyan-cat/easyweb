<?php

class security
{
    static function initialize($algorithm, $salt)
    {
        self::$algorithm = $algorithm;
        self::$salt = $salt;
    }

    static function wrap($mixed, $domain, $expire_at)
    {
        $mixed->_domain = $domain;
        $mixed->_expire_at = $expire_at;
        $mixed->_digest = self::digest($mixed);
        return $mixed;
    }

    static function unwrap($wrapped, $domains)
    {
        $mixed = json_decode($wrapped);
        !is_null($mixed) or backend_error('bad_secure_parameter', 'Secure parameter is not a JSON-encoded object');
        isset($mixed->_domain) or backend_error('bad_secure_parameter', 'Secure parameter has no domain member');
        isset($mixed->_expire_at) or backend_error('bad_secure_parameter', 'Secure parameter has no expire_at member');
        isset($mixed->_digest) or backend_error('bad_secure_parameter', 'Secure parameter has no digest member');
        $digest = $mixed->_digest;
        unset($mixed->_digest);
        $digest === self::digest($mixed) or backend_error('bad_secure_parameter', 'Secure parameter digest is invalid');
        !$mixed->_expire_at or $mixed->_expire_at > @time() or backend_error('bad_secure_parameter', 'Secure parameter expired');
        foreach($domains as $domain)
        {
            if(strpos($domain, $mixed->_domain) === 0)
            {
                unset($mixed->_domain);
                unset($mixed->_expire_at);
                return $mixed;
            }
        }
        
        backend_error('bad_secure_parameter', 'Secure parameter no domains matched');
    }

    private static function digest($mixed)
    {
        return hash_hmac(self::$algorithm, json::encode($mixed), self::$salt);
    }

    private static $algorithm = 'md5'; # http://www.php.net/manual/en/function.hash-algos.php
    private static $salt = 'Initialize salt by calling security::initialize at the beginning of your main script';
}

?>