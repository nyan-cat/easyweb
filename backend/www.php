<?php

require_once(www_root . 'xml.php');
require_once(www_root . 'backend/method.php');
require_once(www_root . 'backend/method_group.php');
require_once(www_root . 'backend/sql.php');
require_once(www_root . 'backend/sql_procedure.php');
require_once(www_root . 'backend/solr.php');
require_once(www_root . 'backend/solr_procedure.php');
require_once(www_root . 'backend/foursquare.php');
require_once(www_root . 'backend/foursquare_procedure.php');
require_once(www_root . 'backend/geoip_procedure.php');
require_once(www_root . 'backend/dispatcher.php');
require_once(www_root . 'backend/access.php');
require_once(www_root . 'backend/group.php');

class www
{
    private function __construct()
    {
        $this->access = new access();
        $this->dispatcher = new dispatcher();
        include('www_load.php');
    }

    function __sleep()
    {
        return ['methods', 'access', 'dispatcher', 'schema', 'documentation'];
    }

    static function create()
    {
        $cache = cache_location . 'cache.tmp';

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
        }
    }

    function bind($success = null, $error = null, $content_type = 'application/json')
    {
        $this->encoders[$content_type] =
        [
            'success' => is_null($success) ? self::$success : $success,
            'error' => is_null($error) ? self::$error : $error
        ];
    }

    function insert_method($url, $method)
    {
        if(!isset($this->methods[$url]))
        {
            $this->methods[$url] = new method_group();
        }

        $this->methods[$url]->insert($method);
    }

    function request($type, $url, $request_headers, $post = [])
    {
        $query = parse_url($url);
        $path = $query['path'];
        $headers =  new headers();
        $headers['Content-Type'] = 'application/json';

        if(isset($this->methods[$path]))
        {
            $get = [];

            if(isset($query['query']))
            {
                parse_str($query['query'], $get);
            }

            $encoder = $this->encoders[$content_type];

            if($method = $this->methods[$path]->find($type, $get, $post))
            {
                $content_type = $headers['Content-Type'];
                if(!isset($this->encoders[$content_type]))
                {
                    return
                    [
                        'code'    => 400,
                        'message' => 'Bad Request',
                        'headers' => $headers,
                        'body'    => "Don't know how to encode into $content_type"
                    ];
                }

                try
                {
                    if($group = $method->access())
                    {
                        if(!$this->access->parse_evaluate($group, array_merge($get, $post)))
                        {
                            return
                            [
                                'code'    => 403,
                                'message' => 'Forbidden',
                                'headers' => $headers,
                                'body'    => $encoder['error']->__invoke('bad_access', "Caller is not a member of $group")
                            ];
                        }
                    }

                    return
                    [
                        'code'    => 200,
                        'message' => 'OK',
                        'headers' => $headers,
                        'body'    => $encoder['success']->__invoke( $method->call($get, $post) )
                    ];
                }
                catch(Exception $e)
                {
                    return
                    [
                        'code'    => 400,
                        'message' => 'Bad Request',
                        'headers' => $headers,
                        'body'    => $encoder['error']->__invoke('error', $e->getMessage())
                    ];
                }
            }
            else
            {
                return
                [
                    'code'    => 404,
                    'message' => 'Not Found',
                    'headers' => $headers,
                    'body'    => $encoder['error']->__invoke('bad_request', 'No methods matched')
                ];
            }
        }
        else if($path == $this->schema)
        {
            $schema = [];

            foreach($this->methods as $url => $group)
            {
                foreach($group->schema() as $gs)
                {
                    list($type, $get, $post) = $gs;

                    foreach($get as $name => &$param)
                    {
                        isset($param['min']) or $param['min'] = datatype::min($param['type']);
                        isset($param['max']) or $param['max'] = datatype::max($param['type']);
                    }

                    foreach($post as $name => &$param)
                    {
                        isset($param['min']) or $param['min'] = datatype::min($param['type']);
                        isset($param['max']) or $param['max'] = datatype::max($param['type']);
                    }

                    $m = ['url' => $url, 'type' => $type];
                    empty($get) or $m['get'] = $get;
                    empty($post) or $m['post'] = $post;
                    $schema[] = $m;
                }
            }

            return
            [
                'code'    => 200,
                'message' => 'OK',
                'headers' => $headers,
                'body'    => self::encode($schema)
            ];
        }
        else if($path == $this->documentation)
        {
            $xml = new xml();

            $methods = $xml->element('methods');

            $xml->append($methods);

            foreach($this->methods as $url => $group)
            {
                foreach($group->schema() as $schema)
                {
                    list($type, $get, $post) = $schema;

                    $method = $xml->element('method');
                    $methods->append($method);
                    $method['@id'] = str_pad(dechex(crc32("$type:$url:" . implode(':', array_keys($get)) . implode(':', array_keys($post)))), 8, '0', STR_PAD_LEFT);;
                    $method['@url'] = $url;
                    $method['@type'] = strtoupper($type);

                    foreach($get as $name => $param)
                    {
                        $g = $xml->element('get');
                        $g['@name'] = $name;
                        $g['@type'] = $param['type'];
                        $g['@min'] = isset($param['min']) ? $param['min'] : 'default (' . datatype::min($param['type']) . ')';
                        $g['@max'] = isset($param['max']) ? $param['max'] : 'default (' . datatype::max($param['type']) . ')';
                        $g['@required'] = $param['required'] ? 'true' : 'false';
                        !isset($param['default']) or $g['@default'] = $param['default'];
                        $g['@secure'] = $param['secure'] ? 'true' : 'false';
                        $method->append($g);
                    }

                    foreach($post as $name => $param)
                    {
                        $p = $xml->element('post');
                        $p['@name'] = $name;
                        $p['@type'] = $param['type'];
                        $p['@min'] = isset($param['min']) ? $param['min'] : 'default (' . datatype::min($param['type']) . ')';
                        $p['@max'] = isset($param['max']) ? $param['max'] : 'default (' . datatype::max($param['type']) . ')';
                        $p['@required'] = $param['required'] ? 'true' : 'false';
                        !isset($param['default']) or $p['@default'] = $param['default'];
                        $p['@secure'] = $param['secure'] ? 'true' : 'false';
                        $method->append($p);
                    }
                }
            }

            $xslt = new XSLTProcessor();
            $xsl = new DOMDocument();
            $xsl->load(www_root . 'backend/documentation.xsl');
            $xslt->importStylesheet($xsl);
            $documentation = $xslt->transformToDoc($xml->get());
            $headers['Content-Type'] = 'text/html';

            return
            [
                'code'    => 200,
                'message' => 'OK',
                'headers' => $headers,
                'body'    => $documentation->saveXML()
            ];
        }
        else
        {
            return
            [
                'code'    => 404,
                'message' => 'Not Found',
                'headers' => $headers,
                'body'    => "Method not found: $path"
            ];
        }
    }

    function query($name, $args)
    {
        return $this->dispatcher->query($name, $args);
    }

    function invoke($name, $args)
    {
        $this->query($name, $args);
    }

    function evaluate($name, $args)
    {
        $fetch = function($entity) use(&$fetch)
        {
            if(is_array($entity))
            {
                (count($entity) == 1 and isset($entity[0])) or backend_error('bad_query', 'Query result is not evaluateable');
                return $fetch($entity[0]);
            }
            else
            {
                return $entity;
            }
        };

        return $fetch($this->query($name, $args));
    }

    function __call($name, $args)
    {
        return $this->query($name, $args[0]);
    }

    static function encode($object)
    {
        return json_encode($object, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    static $success;
    static $error;

    private $encoders = [];
    private $methods = [];
    private $access;
    private $dispatcher;
    private $schema = null;
    private $documentation = null;
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