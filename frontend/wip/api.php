<?php

require_once(www_root . 'error.php');

class api
{
    function __construct($schema)
    {
        preg_match('/\Ahttp:\/\/[^\/]+/', $schema, $endpoint);

        $this->endpoint = $endpoint[0];
    }

    function batch($params, $get)
    {
        $result = $this->request('POST', '/batch', $get, $params);

        $array = [];

        foreach($result as $name => $value)
        {
            $array[$name] = json\decode(json\encode($value)); # TODO: Replace with fast solution
        }

        return $array;
    }

    function request($type, $uri, $get = [], $post = [])
    {
        $request = ['method' => $type, 'protocol_version' => '1.1', 'header' => 'Connection: Close', 'ignore_errors' => true];

        foreach($post as $name => &$param)
        {
            if(is_array($param) and empty($param))
            {
                $param = '_empty_array';
            }
        }

        if(!empty($post))
        {
            $request['header'] .= "\r\nContent-type: application/x-www-form-urlencoded";
            $request['content'] = http_build_query($post);
        }

        $ctx = stream_context_create(['http' => $request]);

        $response = file_get_contents($this->endpoint . (empty($get) ? $uri : ($uri . '?' . http_build_query($get))), false, $ctx);

        preg_match('/\A[^ ]+ (\d+) .+\Z/', $http_response_header[0], $matches);

        if($matches[1] == 200)
        {
            if(!empty($response))
            {
                $result = json\decode($response);
                $result !== null or error('bad_backend_response', "Response from backend server is not valid JSON: $response");
                return $result;
            }
            else
            {
                error('bad_backend_response', 'Empty backend response');
            }
        }
        else
        {
            error('bad_backend_response', 'Backend HTTP response is not 200: ' . $response);
        }
    }

    function get($uri, $params = [])
    {
        return $this->request('GET', $uri, $params);
    }

    function post($uri, $post = [], $get = [])
    {
        return $this->request('POST', $uri, $get, $post);
    }

    function put($uri, $post = [], $get = [])
    {
        return $this->request('PUT', $uri, $get, $post);
    }

    function delete($uri, $params = [])
    {
        return $this->request('DELETE', $uri, $params);
    }

    private $endpoint;
}

?>