<?php

require_once(www_root . 'json/json.php');

class security
{
    static function initialize($algorithm, $salt)
    {
        self::$algorithm = $algorithm;
        self::$salt = $salt;
    }

    static function wrap($mixed, $expire_at, $host = null)
    {
        $package =
        [
            'value' => $mixed,
            'expire_at' => $expire_at
        ];

        if($host !== null)
        {
            $package['host'] = $host;
        }

        ksort($package);

        $package['digest'] = self::digest($package);

        return base64_encode(json\encode($package));
    }

    static function unwrap($token, $host = null)
    {
        if($package = base64_decode($token, true))
        {
            if($package = json\decode($package))
            {
                if(isset($package->value) and isset($package->expire_at) and isset($package->digest))
                {
                    $digest = $package->digest;
                    unset($package->digest);

                    if($digest === self::digest($package))
                    {
                        if(isset($package->host) and $package->host !== $host)
                        {
                            return null;
                        }

                        if($package->expire_at === 0 or $package->expire_at > @time())
                        {
                            return $package->value;
                        }
                    }
                }
            }
        }
        
        return null;
    }

    private static function digest($mixed)
    {
        return hash_hmac(self::$algorithm, json\encode($mixed), self::$salt);
    }

    private static $algorithm = 'md5'; # http://www.php.net/manual/en/function.hash-algos.php
    private static $salt = 'Initialize salt by calling security::initialize at the beginning of your main script';
}

?>