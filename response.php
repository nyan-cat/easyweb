<?php

class response
{
    function __construct($code, $message, $content_type)
    {
        $this->code = $code;
        $this->message = $message;
        $this->headers['Content-Type'] = $content_type;
    }

    function location($url)
    {
        $this->headers['Location'] = $url;
    }

    function flush()
    {
        header('HTTP/' . $this->version . ' ' . $this->code . ' ' . $this->message);
        foreach($this->headers as $name => $value)
        {
            header("$name: $value");
        }
    }

    function xml()
    {
        return preg_match('/\Atext\/xml;/i', $this->headers['Content-Type']);
    }

    private $version = '1.1';
    private $code;
    private $message;
    private $headers = array();
}

?>