<?php

require_once(www_root . 'filesystem/filesystem.php');
require_once(www_root . 'http/router.php');
require_once(www_root . 'json/json.php');
require_once(www_root . 'frontend/wip/api.php');
require_once(www_root . 'frontend/locale.php');
require_once(www_root . 'frontend/wip/page.php');

class www
{
    private function __construct($options)
    {
        $this->router = new http\router();
        switch(fs\extension($options->config))
        {
        case 'xml':
            $config = self::from_xml($options->config);
            break;
        }
        $this->initialize($options, $config);
    }

    private static function from_xml($filename)
    {
        return (include(www_root . 'frontend/www_xml.php'));
    }

    private function initialize($options, $config)
    {
        include(www_root . 'frontend/www_initialize.php');
    }

    static function create($options, $extensions)
    {
        if(isset($options->cache))
        {
            $cache = $options->cache . 'cache.tmp';
            if($www = fs\read($cache))
            {
                $www = unserialize($www);
            }
            else
            {
                $www = new www($options);
                fs\write($cache, serialize($www));
            }
            $www->locale->setup($options->language, $options->country);
        }
        else
        {
            $www = new www($options);
        }

        foreach($www->templaters as $name => $templater)
        {
            $templater->extend(isset($extensions[$name]) ? $extensions[$name] : []);
        }

        return $www;
    }

    function request($request, $global = [])
    {
        $response = $this->router->request($request, $global);

        if($response !== null and isset($response->content))
        {
            $response->content = preg_replace_callback('/<a [^>]*href="([^"]+)"[^>]*>([\s\S]+?)<\/a>/i', function($matches) use($request)
            {
                return $matches[1] == $request->uri ? $matches[2] : $matches[0];
            }, $response->content);
        }

        return $response;
    }

    function get($uri, $params = [])
    {
        return $this->api->get($uri, $params);
    }

    function post($uri, $post = [], $get = [])
    {
        return $this->api->post($uri, $post, $get);
    }

    function put($uri, $post = [], $get = [])
    {
        return $this->api->put($uri, $post, $get);
    }

    function delete($uri, $get = [])
    {
        return $this->api->delete($uri, $get);
    }

    private $api;
    private $locale;
    private $router;
    private $templaters = [];
}

?>