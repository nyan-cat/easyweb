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
        $this->encoders[$content_type] = (object)
        [
            'success' => $success === null ? self::$success : $success,
            'error'   => $error === null ? self::$error : $error
        ];
    }

    function request($request)
    {
        $response = null;
        $content_type = 'application/json';
        $encoder = $this->encoders[$content_type];
        
        try
        {
            $result = null;

            if($request->uri == $this->batch and $request->method == 'POST')
            {
                $result = $this->batch($request);
            }
            else
            {
                $result = $this->router->request($request);
            }

            $response = new http\response
            (
                200, 'OK', $request->protocol,
                $encoder->success->__invoke($result)
            );
        }
        catch(www_exception $e)
        {
            $response = new http\response($e->code, $e->message, $request->protocol, $encoder->error->__invoke($e));
        }

        if(isset($request->get->_code))
        {
            $response->code = $request->get->_code;
        }

        return $response;
    }

    function __get($name)
    {
        return $this->dispatcher->__get($name);
    }

    function __call($name, $args)
    {
        return $this->dispatcher->__call($name, $args);
    }

    private function batch($request)
    {
        $result = [];

        foreach($request->post as $param => $uri)
        {
            $query = parse_url($uri);
            $uri = $query['path'];
            $get = [];
            !isset($query['query']) or parse_str($query['query'], $get);

            foreach($get as $name => &$value)
            {
                $value = preg_replace_callback( '/\{@([\w\.]+)\}/', function($matches) use(&$result)
                {
                    $params = explode('.', $matches[1]);

                    $current = null;

                    foreach($params as $member)
                    {
                        if(!$current)
                        {
                            $current = $result[$member];
                        }
                        else
                        {
                            if(preg_match('/\A(\w+)\[(\d+)\]\Z/', $member, $array))
                            {
                                $current = $current->$array[1];
                                $current = $current[(int) $array[2]];
                            }
                            else
                            {
                                $current = $current->$member;
                            }
                        }
                    }

                    return $current;
                }, $value);
            }

            $result[$param] = $this->router->request(new http\request($uri, 'GET', $request->protocol, $get));
        }

        return $result;
    }

    static $success;
    static $error;

    private $batch;
    private $schema;
    private $documentation;

    private $router;
    private $dispatcher;
    private $encoders = [];
}

www::$success = function($content)
{
    return www::encode($content);
};

www::$error = function($e)
{
    return www::encode(['type' => $e->type, 'description' => $e->description, 'stacktrace' => $e->getTrace()]);
};

?>