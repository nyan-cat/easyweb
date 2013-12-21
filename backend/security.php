<?php

class security
{
    static function initialize($algorithm, $salt)
    {
        self::$algorithm = $algorithm;
        self::$salt = $salt;
    }

    static function wrap($mixed, $domain, $expire_at, $address = null)
    {
        $package =
        [
            'value' => $mixed,
            'domain' => $domain,
            'expire_at' => $expire_at
        ];

        is_null($address) or $package['address'] = $address;

        $package['digest'] = self::digest($package);

        return (object) ['value' => $mixed, 'token' => base64_encode(json::encode($package))];
    }

    static function unwrap($token, $domains, $address = null)
    {
        $package = base64_decode($token, true);
        $package !== false or backend_error('bad_secure_parameter', 'Security token is not valid base64 string');
        $package = json::decode($package);
        !is_null($package) or backend_error('bad_secure_parameter', 'Secure parameter is not a JSON-encoded object');
        isset($package->value) or backend_error('bad_secure_parameter', 'Secure parameter has no value member');
        isset($package->domain) or backend_error('bad_secure_parameter', 'Secure parameter has no domain member');
        isset($package->expire_at) or backend_error('bad_secure_parameter', 'Secure parameter has no expire_at member');
        isset($package->digest) or backend_error('bad_secure_parameter', 'Secure parameter has no digest member');

        $digest = $package->digest;
        unset($package->digest);
        $digest === self::digest($package) or backend_error('bad_secure_parameter', 'Secure parameter digest is invalid');

        if(isset($package->address))
        {
            !is_null($address) or backend_error('bad_secure_parameter', 'IP address for secure parameter validation is not specified');
            $package->address === $address or backend_error('bad_secure_parameter', 'Secure parameter IP address doesn\'t match');
        }
        
        $package->expire_at === 0 or $package->expire_at > @time() or backend_error('bad_secure_parameter', 'Secure parameter expired');

        foreach($domains as $domain)
        {
            if(strpos($domain, $package->domain) === 0)
            {
                return $package->value;
            }
        }
        
        backend_error('bad_secure_parameter', 'No domains are matched for secure parameter');
    }

    private static function digest($mixed)
    {
        return hash_hmac(self::$algorithm, json::encode($mixed), self::$salt);
    }

    private static $algorithm = 'md5'; # http://www.php.net/manual/en/function.hash-algos.php
    private static $salt = 'Initialize salt by calling security::initialize at the beginning of your main script';
}

?>