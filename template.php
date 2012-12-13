<?php

require_once('exception.php');

class template
{
    function __construct($src, $args = array(), $doc = null)
    {
        $this->src = $src;
        $this->args = $args;
        $this->doc = $doc;
    }

    function insert($name, $template)
    {
        !isset($this->children[$name]) or runtime_error('Duplicate template: ' . $name);
        $template->parent = $this;
        $this->children[$name] = $template;
    }

    function source()
    {
        return $this->src;
    }

    function args()
    {
        return $this->args;
    }

    function document()
    {
        return $this->doc ? $this->doc : ($this->parent ? $this->parent->document() : null);
    }

    function get($name)
    {
        isset($this->children[$name]) or runtime_error('Template not found: ' . $name);
        return $this->children[$name];
    }

    private $src;
    private $args;
    private $doc;
    private $parent = null;
    private $children = array();
}

?>