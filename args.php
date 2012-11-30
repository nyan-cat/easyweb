<?php

function args_decode($expression)
{
    $args = array();

    foreach(explode(',', $expression) as $nvp)
    {
        preg_match('/\A(\w+) +\-> +(.+)\Z/', trim($nvp), $match) or runtime_error('Bad arguments syntax: ' . $expression);
        $args[trim($match[1])] = trim($match[2]);
    }

    return $args;
}

?>