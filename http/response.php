<?php

namespace http;

class response
{
    function __construct($code = '200', $message = 'OK', $protocol = 'HTTP/1.1')
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->protocol = $protocol;
    }

    function write()
    {
        header("{$this->protocol} {$this->code} {$this->message}");

        foreach($this->headers as $name => $value)
        {
            header("$name: $value");
        }
    }

    $protocol;
    $code;
    $message;
    $headers = [];
    $cookies = [];
    $data = (object) [];
}

?>