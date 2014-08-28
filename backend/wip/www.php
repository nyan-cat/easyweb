<?php

require_once(www_root . 'filesystem/filesystem.php');
require_once(www_root . 'http/response.php');
require_once(www_root . 'http/router.php');
require_once(www_root . 'json/json.php');
require_once(www_root . 'backend/wip/sql.php');
require_once(www_root . 'backend/wip/sql_procedure.php');
require_once(www_root . 'backend/wip/dispatcher.php');
require_once(www_root . 'backend/wip/method.php');

class www
{
    private function __construct($options)
    {
        $this->router = new http\router();
        $this->dispatcher = new dispatcher();
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
        return (include(www_root . 'backend/www_xml.php'));
    }

    private function initialize($config)
    {
        include(www_root . 'backend/www_initialize.php');
    }

    static function create($options)
    {
        if(isset($options->cache))
        {
            $cache = $options->cache . 'cache.tmp';
            if($www = fs\read($cache))
            {
                $www = unserialize($www);
                $www->bind();
                return $www;
            }
            else
            {
                $www = new www($options);
                fs\write($cache, serialize($www));
                $www->bind();
                return $www;
            }
        }
        else
        {
            $www = new www($options);
            $www->bind();
            return $www;
        }
    }

    static function encode($object)
    {
        return json\encode($object, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    function bind($success = null, $error = null, $content_type = 'application/json')
    {
        $this->encoders[$content_type] =
        [
            'success' => is_null($success) ? self::$success : $success,
            'error' => is_null($error) ? self::$error : $error
        ];
    }

    function request($request)
    {
        $content_type = 'application/json';
        $encoder = $this->encoders[$content_type];
        return new http\response
        (
            200, 'OK', $request->protocol,
            $encoder['success']->__invoke
            (
                $this->router->request($request)
            )
        );
    }

    function __get($name)
    {
        return $this->dispatcher->__get($name);
    }

    function __call($name, $args)
    {
        return $this->dispatcher->__call($name, $args);
    }

    static $success;
    static $error;

    private $router;
    private $dispatcher;
    private $encoders = [];
}

www::$success = function($content)
{
    return www::encode(['status' => 'success', 'content' => $content]);
};

www::$error = function($type, $message)
{
    return www::encode(['status' => 'error', 'type' => $type, 'message' => $message]);
};

?>