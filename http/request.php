<?php

namespace http;

class request
{
    function __construct($method, $uri, $protocol = 'HTTP/1.1')
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->protocol = $protocol;
    }

    static function current()
    {
        $request = new request($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_PROTOCOL']);
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

        $request->data = (object) $_POST;
    }

    $method;
    $uri;
    $protocol;
    $headers = [];
    $cookies = [];
    $files = [];
    $data = (object) [];
}

?>