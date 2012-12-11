<?php

class args
{
    static function quote($value)
    {
        return '\'' . str_replace(array('\\', '\''), array('\\\\', '\\\''), $value) . '\'';
    }

    static function unquote($value)
    {
        return str_replace(array('\\\'', '\\\\'), array('\'', '\\'), trim(trim($value), '\''));
    }

    static function decode($expression)
    {
        $args = array();

        if(trim($expression) !== '')
        {
            foreach(explode(',', $expression) as $nvp)
            {
                preg_match('/\A(\w+) +\-> +(.+)\Z/', trim($nvp), $match) or runtime_error('Bad arguments syntax: ' . $expression);
                $args[trim($match[1])] = self::unquote($match[2]);
            }
        }

        return $args;
    }

    static function encode($args)
    {
        $expression = array();

        foreach($args as $name => $value)
        {
            $expression[] = $name . ' -> ' . self::quote($value);
        }
        
        return implode(',', $expression);
    }
}

?>