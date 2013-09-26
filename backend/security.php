<?php

class security
{
    static function digest($mixed)
    {
        return hash_hmac('sha512', (is_array($mixed) ? implode(':', $mixed) : $mixed), self::$salt);
    }

    private static $salt = 'epe5KcYRr55A4o3U80xAsR3164egZK2iT53d43nX1e08B2MP6I52Hg327m7E4G32';
}

?>