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
        $this->initialize($config);
    }

    private static function from_xml($filename)
    {
        return (include(www_root . 'frontend/www_xml.php'));
    }

    private function initialize($config)
    {
        include(www_root . 'frontend/www_initialize.php');
    }

    static function create($options)
    {
        if(isset($options->cache))
        {
            $cache = $options->cache . 'cache.tmp';
            if($www = fs\read($cache))
            {
                $www = unserialize($www);
                $www->locale->setup($options->language, $options->country);
                return $www;
            }
            else
            {
                $www = new www($options);
                fs\write($cache, serialize($www));
                return $www;
            }
        }
        else
        {
            return new www($options);
        }
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

    function get($url, $params = [])
    {
        return $this->api->get($url, $params);
    }

    function post($url, $post = [], $get = [])
    {
        return $this->api->post($url, $post, $get);
    }

    function put($url, $post = [], $get = [])
    {
        return $this->api->put($url, $post, $get);
    }

    function delete($url, $get = [])
    {
        return $this->api->delete($url, $get);
    }

    private $api;
    private $locale;
    private $router;
}

?>