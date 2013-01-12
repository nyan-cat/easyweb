<?php

require_once('exception.php');

class vars
{
    const names_flat      = '\w+';
    const names_objective = '[\w:]+\w';

    function __wakeup()
    {
        $this->initialize();
    }

    function initialize()
    {
        $top = array
        (
            'user:agent' => $_SERVER['HTTP_USER_AGENT'],
            'user:ip'    => $_SERVER['REMOTE_ADDR']
        );

        foreach($_GET as $name => $value)
        {
            $top["get:$name"] = $value;
        }

        foreach($_POST as $name => $value)
        {
            $top["post:$name"] = $value;
        }

        if(isset($_SESSION))
        {
            foreach($_SESSION as $name => $value)
            {
                $top["session:$name"] = $value;
            }
        }

        $this->push($top);
    }

    function get($name)
    {
        $top = &$this->vars[count($this->vars) - 1];
        isset($top[$name]) or runtime_error('Variable not found: ' . $name);
        return $top[$name];
    }

    function insert($name, $value)
    {
        $top = &$this->vars[count($this->vars) - 1];
        preg_match('/\A[\w:]+\w\Z/', $name) or runtime_error('Invalid characters in variable name: ' . $name);
        !isset($top[$name]) or runtime_error('Duplicate variable name: ' . $name);
        $top[$name] = $this->apply($value);
    }

    function push($args)
    {
        $this->vars[] = end($this->vars);
        foreach($args as $name => $value)
        {
            $this->insert($name, $value);
        }
    }

    function pop()
    {
        array_pop($this->vars);
    }

    function apply($string, $quotes = false, $names = vars::names_objective)
    {
        return preg_replace('/\$(' . $names . ')/e', "\$this->replace('\\1', \$quotes)", $string);
    }

    static function apply_assoc($string, $vars, $quotes = false, $names = vars::names_objective)
    {
        return preg_replace('/\$(' . $names . ')/e', "self::replace_assoc('\\1', \$vars, \$quotes)", $string);
    }

    private function replace($name, $quotes)
    {
        $top = &$this->vars[count($this->vars) - 1];
        return isset($top[$name]) ? ($quotes ? args::quote($top[$name]) : $top[$name]) : '$' . $name;
    }

    private static function replace_assoc($name, $vars, $quotes)
    {
        return isset($vars[$name]) ? ($quotes ? args::quote($vars[$name]) : $vars[$name]) : '$' . $name;
    }

    private $vars = array(array());
}

?>