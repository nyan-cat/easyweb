<?php

function starts_with($haystack, $needle)
{
    return $needle === "" or strpos($haystack, $needle) === 0;
}

function ends_with($haystack, $needle)
{
    return $needle === "" or substr($haystack, -strlen($needle)) === $needle;
}

?>