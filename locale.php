<?php

require_once('xml.php');

class locale
{
    function __construct($language, $country)
    {
        $this->setup($language, $country);
    }

    function setup($language, $country)
    {
        $this->language = $language;
        $this->country = $country;
    }

    function load($filename)
    {
        $locale = xml::load($filename);
        foreach($locale->query('/local//*[count(*)=0]') as $node)
        {
            $path = explode(':', str_replace('/', ':', trim($node->path(), '/')));
            array_shift($path);
            $this->local[implode(':', $path)] = $node->value();
        }
    }

    function get($alias)
    {
        $alias .= ':' . $this->language;
        return isset($this->local[$alias]) ? $this->local[$alias] : "[Alias not found: $alias]";
    }

    private $language;
    private $country;
    private $local = array();
}

?>