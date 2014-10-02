<?php

namespace http;

class response
{
    function __construct($code = 200, $message = 'OK', $protocol = 'HTTP/1.1', $content = null)
    {
        $this->protocol = $protocol;
        $this->code = $code;
        $this->message = $message;
        if($content !== null)
        {
            $this->content = $content;
        }
    }

    function write()
    {
        header("{$this->protocol} {$this->code} {$this->message}");

        foreach($this->headers as $name => $value)
        {
            header("$name: $value");
        }

        foreach($this->cookies as $name => $cookie)
        {
            $cookie = (object) $cookie;
            setcookie($name, $cookie->value, @time() + $cookie->expire, $cookie->path, '.' . $cookie->domain);
        }

        if(isset($this->content))
        {
            echo $this->content;
        }
    }

    var $protocol;
    var $code;
    var $message;
    var $headers = [];
    var $cookies = [];
    var $content = null;
}

?>