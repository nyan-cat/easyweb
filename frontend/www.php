<?php

require_once(www_root . 'facilities/json.php');
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

    /*function __sleep()
    {
        return ['methods', 'access', 'dispatcher', 'schema', 'documentation'];
    }*/

    static function create($options)
    {
        return new www($options);

        /*$cache = cache_location . 'cache.tmp';

        if($www = fs::read($cache))
        {
            $www = unserialize($www);
            $www->bind();
            return $www;
        }
        else
        {
            $www = new www();
            fs::write($cache, serialize($www));
            $www->bind();
            return $www;
        }*/
    }

    function request($url)
    {
        return preg_replace_callback('/<a [^>]*href="([^"]+)"[^>]*>([\s\S]+?)<\/a>/i', function($matches) use($url)
        {
            return $matches[1] == $url ? $matches[2] : $matches[0];
        }, $this->router->request($url));
    }

    private $router;
}

?>