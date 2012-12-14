<?php

require_once('exception.php');
require_once('validate.php');

class procedure
{
    function __construct($name, $params, $empty, $root, $output = array(), $permission = null)
    {
        $this->mangled = self::mangle($name, $params);
        $this->params = $params;
        $this->empty = $empty;
        if($root)
        {
            $this->root = explode(',', $root);
            foreach($this->root as &$value)
            {
                $value = trim($value);
            }
        }

        foreach($output as $name => &$transform)
        {
            $transform = explode(',', $transform);
            foreach($transform as &$value)
            {
                $value = trim($value);
            }
        }

        $this->permission = $permission;
    }

    function validate($args)
    {
        foreach($args as $name => $value)
        {
            validate::assert($this->params[$name], $value);
        }
    }

    function mangled()
    {
        return $this->mangled;
    }

    function permission()
    {
        return $this->permission;
    }

    static function mangle($procedure, $args)
    {
        foreach($args as $name => $vt)
        {
            $procedure .= "[$name]";
        }
        return $procedure;
    }

    static function mangle_values($procedure, $args)
    {
        foreach($args as $name => $value)
        {
            $procedure .= "[$name -> $value]";
        }
        return $procedure;
    }

    protected function transform($name, $value)
    {
        if(isset($this->output[$name]) && !empty($this->output[$name]))
        {
            foreach($this->output[$name] as $transform)
            {
                switch($transform)
                {
                case 'none':
                    break;
                case 'vars':
                    $value = $value; // TODO: Apply vars
                    break;
                case 'json2xml':
                    $value = $this->json2xml(json_decode($value, true));
                    break;
                case 'nl2p':
                    $value = $this->nl2p($value);
                    break;
                default:
                    runtime_error('Unknown transform: ' . $transform);
                }
            }
            return $value;
        }
        else
        {
            return nl2br(htmlspecialchars($value));
        }
    }

    private function json2xml($nvp) // TODO: Return DOM instead of text
    {
        $result = '';
        if(is_array($nvp))
        {
            foreach($nvp as $key => $value)
            {
                $key = is_numeric($key) ? 'token' : $key;
                if(is_array($value))
                {
                    $result .= "<$key>" . $this->json2xml($value) . "</$key>";
                }
                else
                {
                    $result .= "<$key>$value</$key>";
                }
            }
        }
        return $result;
    }

    private function nl2p($value)
    {
        return '<p>' . preg_replace("/([\n]{1,})/i", "</p>\n<p>", trim(htmlspecialchars($value))) . '</p>';
    }

    private $mangled;
    private $params;
    protected $empty;
    protected $root;
    private $output;
    private $permission;
}

?>