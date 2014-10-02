<?php

namespace http;

class request
{
    function __construct($uri, $method = 'GET', $protocol = 'HTTP/1.1', $get = [], $post = [])
    {
        $this->uri = $uri;
        $this->method = $method;
        $this->protocol = $protocol;
        $this->get = (object) $get;
        $this->post = (object) $post;
    }

    static function current()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $get = $_GET;

        $parsed = parse_url($uri);
        if(isset($parsed['query']))
        {
            $uri = $parsed['path'];
            parse_str($parsed['query'], $get);
        }

        $request = new request($uri, $_SERVER['REQUEST_METHOD'], $_SERVER['SERVER_PROTOCOL'], $get, $_POST);
        $request->headers = getallheaders();
        $request->cookies = $_COOKIE;

        foreach($_FILES as $name => $input)
        {
            if(is_array($input['error']))
            {
                $files = [];

                foreach($input['error'] as $n => $error)
                {
                    if($error == UPLOAD_ERR_OK)
                    {
                        $files[] = (object)
                        [
                            'name' => $input['name'][$n],
                            'type' => $input['type'][$n],
                            'size' => $input['size'][$n],
                            'tmp'  => $input['tmp_name'][$n]
                        ];
                    }
                }

                if(!empty($files))
                {
                    $request->files[$name] = $files;
                }
            }
            else
            {
                if($input['error'] == UPLOAD_ERR_OK)
                {
                    $request->files[$name] = (object)
                    [
                        'name' => $input['name'],
                        'type' => $input['type'],
                        'size' => $input['size'],
                        'tmp'  => $input['tmp_name']
                    ];
                }
            }
        }

        return $request;
    }

    var $uri;
    var $method;
    var $protocol;
    var $headers = [];
    var $cookies = [];
    var $files = [];
    var $get;
    var $post;
}

?>