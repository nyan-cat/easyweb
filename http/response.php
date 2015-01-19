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

        foreach($this->cookies as $name => $cookie)
        {
            if($cookie !== null)
            {
                $cookie = (object) $cookie;
                setcookie
                (
                    $name,
                    $cookie->value,
                    isset($cookie->expire) ? @time() + $cookie->expire : null,
                    isset($cookie->path) ? $cookie->path : null,
                    isset($cookie->domain) ? '.' . $cookie->domain : null
                );
            }
            else
            {
                setcookie($name, null, -1, '/');
            }
        }

        foreach($this->headers as $name => $value)
        {
            header("$name: $value");
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