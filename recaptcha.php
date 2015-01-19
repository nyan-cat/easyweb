<?php

namespace recaptcha;

class options
{
    static $secret = '';
}

function valid($captcha, $ip)
{
    $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . options::$secret . "&response=$captcha&remoteip=$ip";
    $result = json_decode(file_get_contents($url));
    return $result->success;
}

?>