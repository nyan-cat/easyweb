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

    function content($content = null)
    {
        if($content)
        {
            $this->content = $content;
        }
        else
        {
            return $this->content;
        }
    }

    function flush()
    {
        header('HTTP/' . $this->version . ' ' . $this->code . ' ' . $this->message);
        foreach($this->headers as $name => $value)
        {
            header("$name: $value");
        }
        echo $this->content;
    }

    function xml()
    {
        return preg_match('/\Atext\/xml;/i', $this->headers['Content-Type']);
    }

    private $version = '1.1';
    private $code;
    private $message;
    private $headers = array();
    private $content = '';
}

?>