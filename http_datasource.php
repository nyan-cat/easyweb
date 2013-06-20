<?php

class http_datasource implements ArrayAccess
{
    function __construct($url, $content_type, $username = null, $password = null)
    {
        $this->options['url'] = $url;
        $this->options['content-type'] = $content_type;
        $this->options['username'] = $username;
        $this->options['password'] = $password;
    }

    function offsetExists($offset)
    {
        runtime_error('Method not implemented');
    }

    function offsetGet($offset)
    {
        isset($this->options[$offset]) or runtime_error('Unknown HTTP datasource option: ' . $offset);
        return $this->options[$offset];
    }

    function offsetSet($offset, $value)
    {
        runtime_error('Method not implemented');
    }

    function offsetUnset($offset)
    {
        runtime_error('Method not implemented');
    }

    private $options = [];
}

?>