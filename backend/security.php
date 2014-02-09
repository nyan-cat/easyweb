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

        return base64_encode(json::encode($package));
    }

    static function unwrap($token, $domains, $address = null)
    {
        if($package = base64_decode($token, true))
        {
            if($package = json::decode($package))
            {
                if(isset($package->value) and isset($package->domain) and isset($package->expire_at) and isset($package->digest))
                {
                    $digest = $package->digest;
                    unset($package->digest);

                    if($digest === self::digest($package))
                    {
                        if(isset($package->address))
                        {
                            if(is_null($address) or $package->address !== $address)
                            {
                                return null;
                            }
                        }

                        if($package->expire_at === 0 or $package->expire_at > @time())
                        {
                            foreach($domains as $domain)
                            {
                                if(ends_with('.' . $package->domain, '.' . $domain))
                                {
                                    return $package->value;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return null;
    }

    private static function digest($mixed)
    {
        return hash_hmac(self::$algorithm, json::encode($mixed), self::$salt);
    }

    private static $algorithm = 'md5'; # http://www.php.net/manual/en/function.hash-algos.php
    private static $salt = 'Initialize salt by calling security::initialize at the beginning of your main script';
}

?>