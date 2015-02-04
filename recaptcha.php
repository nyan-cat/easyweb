<?php

namespace recaptcha;

class options
{
    static $secret = '';
}

function assert($captcha)
{
    $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . options::$secret . "&response=$captcha";
    $content = file_get_contents($url);
    $content !== false or error('bad_captcha', 'Bad response from reCAPTCHA service: error processing request');
    $result = json_decode($content);
    $result !== null or error('bad_captcha', 'Bad response from reCAPTCHA service: result is not valid JSON');
    $result->success or error('bad_captcha', 'User is robot!');
}

?>