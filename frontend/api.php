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
        $result = $this->request('POST', '/batch', [], $params);

        $array = [];

        foreach($result as $name => $value)
        {
            $array[$name] = json::decode(json::encode($value)); # TODO: Replace with fast solution
        }

        return $array;
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

        $response = file_get_contents($this->endpoint . (empty($get) ? $url : ($url . '?' . http_build_query($get))), false, $ctx);

        $object = json::decode($response, true);

        if(is_null($object))
        {
            var_dump($response);
            die();
        }

        $content = $object['content'];

        return (is_object($content) or (is_array($content) and array_values($content) !== $content)) ? (object) $content : $content;
    }

    private $endpoint;
}

?>