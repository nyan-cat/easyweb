<?php

class api
{
    function __construct($schema)
    {
        preg_match('/\Ahttp:\/\/[^\/]+/', $schema, $endpoint);

        $this->endpoint = $endpoint[0];
    }

    function batch($params)
    {
        return $this->request('POST', '/batch', [], $params);
    }

    function request($type, $url, $get = [], $post = [])
    {
        $request = ['method' => $type];

        if(!empty($post))
        {
            $request['header'] = 'Content-type: application/x-www-form-urlencoded';
            $request['content'] = http_build_query($post);
        }

        $ctx = stream_context_create(['http' => $request]);

        $response = file_get_contents($this->endpoint . (empty($get) ? $url : ($url . http_build_query($get))), false, $ctx);

        $object = json::decode($response, true);

        if(is_null($object))
        {
            var_dump($response);
            die();
        }

        return $object['content'];
    }

    private $endpoint;
}

?>