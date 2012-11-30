<?php

require_once('xml.php');

class locale
{
    function __construct($country, $language)
    {
        $this->setup($country, $language);
    }

    function setup($country, $language)
    {
        $this->country = $country;
        $this->language = $language;
    }

    function load($filename)
    {
        $locale = new xml();
        $locale->load($filename);
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

    private $country;
    private $language;
    private $local = array();
}

?>