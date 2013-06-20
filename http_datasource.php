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
        return isset($this->options[$offset]) ? $this->options[$offset] : null;
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