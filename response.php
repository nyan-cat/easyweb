<?php

class response
{
    function __construct($code = '200', $message = 'OK')
    {
        $this->code = $code;
        $this->message = $message;
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

    private $version = '1.1';
    private $code;
    private $message;
    private $headers = array();
}

?>