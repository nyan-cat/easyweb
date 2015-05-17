<?php

function starts_with($haystack, $needle)
{
    return $needle === "" or strpos($haystack, $needle) === 0;
}

function ends_with($haystack, $needle)
{
    return $needle === "" or substr($haystack, -strlen($needle)) === $needle;
}

function replace($patterns, $callbacks, $subject)
{
    foreach($patterns as $n => $pattern)
    {
        $subject = preg_replace_callback($pattern, $callbacks[$n], $subject);
    }

    return $subject;
}

function mb_ucfirst($string, $enc = 'UTF-8')
{
    return mb_strtoupper(mb_substr($string, 0, 1, $enc), $enc) . 
           mb_substr($string, 1, mb_strlen($string, $enc), $enc);
}

?>