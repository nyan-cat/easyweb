<?php

require_once('http.php');

class http_procedure extends procedure
{
    function __construct($datasource, $vars, $name, $method, $url, $params, $get, $post, $content_type, $empty, $root, $permission = null, $cache = true)
    {
        parent::__construct($vars, $name, $params, $empty, $root, [], $permission, $cache);
        $this->datasource = $datasource;
        $this->method = $method;
        $this->url = $datasource['url'] . $url;
        $this->get = $get;
        $this->post = $post;
        $this->content_type = is_null($content_type) ? $datasource['content-type'] : $content_type;
    }

    function query($args, $document)
    {
        $this->validate($args);
        $xml = new xml();

        $get = [];
        foreach($this->get as $name => $value)
        {
            $get[$name] = vars::apply_assoc($value, $args);
        }

        switch($this->method)
        {
        case 'get':
            return self::to_xml(http::get($this->url, $get, $this->datasource['username'], $this->datasource['password']));

        case 'post':
            {
                $post = [];
                foreach($this->post as $name => $value)
                {
                    $post[$name] = vars::apply_assoc($value, $args);
                }

                return self::to_xml(http::post($this->url, $post, $get, $this->datasource['username'], $this->datasource['password']));
            }

        default:
            runtime_error('Unknown HTTP procedure method: ' . $this->method);
        }
    }

    private function to_xml($result)
    {
        switch($this->content_type)
        {
        case 'xml':
            return xml::parse($result);

        case 'json':
            return xml::json($this->root[0], $result);

        default:
            runtime_error('Unknown HTTP procedure content type: ' . $this->content_type);
        }
    }

    private $datasource;
    private $method;
    private $url;
    private $get;
    private $post;
    private $content_type;
}

?>