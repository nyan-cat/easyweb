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

?>