<?php

namespace json;

function encode($value, $options = JSON_UNESCAPED_UNICODE)
{
    return json_encode($value, $options);
}

function decode($json, $assoc = false)
{
    return json_decode($json, $assoc);
}

function join($a, $b)
{
    foreach($b as $name => $property)
    {
        if(isset($a[$name]))
        {
            if(is_array($a[$name]) and is_array($property))
            {
                $assoc = (object)
                [
                    'a' => is_array($a[$name]) and $a[$name] !== array_values($a[$name]),
                    'b' => is_array($property) and $property !== array_values($property)
                ];

                if($assoc->a and $assoc->b)
                {
                    $a[$name] = join($a[$name], $property);
                }
                elseif(!$assoc->a and !$assoc->b)
                {
                    $a[$name] += $property;
                }
            }
            elseif(is_object($a[$name]) or is_object($property))
            {
                error('bad_parameter', 'Only two arrays can be joined');
            }
        }
        else
        {
            $a[$name] = $property;
        }
    }
    return $a;
}

?>