<?php

require_once(www_root . 'facilities/filesystem.php');
require_once(www_root . 'facilities/form.php');
require_once(www_root . 'facilities/json.php');
require_once(www_root . 'facilities/string.php');
require_once(www_root . 'facilities/xml.php');
require_once(www_root . 'frontend/api.php');
require_once(www_root . 'frontend/locale.php');
require_once(www_root . 'frontend/router.php');

class www
{
    private function __construct($options)
    {
        $this->router = new router();
        include('www_load.php');
    }

    static function create($options)
    {
        if(isset($options->cache))
        {
            $cache = $options->cache . 'cache.tmp';
            if($www = fs::read($cache))
            {
                $www = unserialize($www);
                $www->locale->setup($options->language, $options->country);
                return $www;
            }
            else
            {
                $www = new www($options);
                fs::write($cache, serialize($www));
                return $www;
            }
        }
        else
        {
            return new www($options);
        }
    }

    function extend($extensions)
    {
        $this->router->extend($extensions);
    }

    function request($url, $global, $get, $post, $cookies, $files)
    {
        $response = $this->router->request($url, $global, $get, $post, $cookies, $files);

        if(isset($response->content))
        {
            $response->content = preg_replace_callback('/<a [^>]*href="([^"]+)"[^>]*>([\s\S]+?)<\/a>/i', function($matches) use($url)
            {
                return $matches[1] == $url ? $matches[2] : $matches[0];
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

    private $api;
    private $locale;
    private $router;
}

?>