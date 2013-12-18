<?php

require_once(www_root . 'json.php');
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
        return ['methods', 'access', 'batch', 'dispatcher', 'schema', 'documentation'];
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
        $url = $query['path'];
        $get = [];
        !isset($query['query']) or parse_str($query['query'], $get);
        $headers = new headers();
        $headers['Content-Type'] = 'application/json';
        $content_type = $headers['Content-Type'];
        $encoder = $this->encoders[$content_type];

        if($url == $this->batch)
        {
            $body = [];

            foreach($post as $param => $url)
            {
                $query = parse_url($url);
                $url = $query['path'];
                $get = [];
                !isset($query['query']) or parse_str($query['query'], $get);

                foreach($get as $name => &$value)
                {
                    $value = preg_replace_callback
                    (
                        '/\{@([\w\.]+)\}/',
                        function($matches) use(&$body)
                        {
                            $params = explode('.', $matches[1]);

                            $current = null;

                            foreach($params as $member)
                            {
                                if(!$current)
                                {
                                    $current = $body[$member];
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
                        },
                        $value
                    );
                }

                $body[$param] = $this->call('GET', $url, $get);
            }

            return (object)
            [
                'code'    => 200,
                'message' => 'OK',
                'headers' => $headers,
                'body'    => $encoder['success']->__invoke($body)
            ];
        }
        elseif($url == $this->schema)
        {
            return $this->schema();
        }
        elseif($url == $this->documentation)
        {
            return $this->documentation();
        }
        else
        {
            return (object)
            [
                'code'    => 200,
                'message' => 'OK',
                'headers' => $headers,
                'body'    => $encoder['success']->__invoke( $this->call($type, $url, $get, $post) )
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

    function wrap($mixed, $domain, $lifetime = 0)
    {
        $expire_at = $lifetime ? @time() + $lifetime : 0;

        return security::wrap($mixed, $domain, $expire_at);
    }

    function schema()
    {
        $schema = [];

        foreach($this->methods as $url => $group)
        {
            foreach($group->schema() as $gs)
            {
                list($type, $get, $post) = $gs;

                foreach($get as $name => &$param)
                {
                    isset($param->min) or $param->min = datatype::min($param->type);
                    isset($param->max) or $param->max = datatype::max($param->type);
                }

                foreach($post as $name => &$param)
                {
                    isset($param->min) or $param->min = datatype::min($param->type);
                    isset($param->max) or $param->max = datatype::max($param->type);
                }

                $m = ['url' => $url, 'type' => $type];
                empty($get) or $m['get'] = $get;
                empty($post) or $m['post'] = $post;
                $schema[] = $m;
            }
        }

        return (object)
        [
            'code'    => 200,
            'message' => 'OK',
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => self::encode($schema)
        ];
    }

    function documentation()
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
                $method['@id'] = str_pad(dechex(crc32("$type:$url:" . implode(':', array_keys($get)) . implode(':', array_keys($post)))), 8, '0', STR_PAD_LEFT);
                $method['@url'] = $url;
                $method['@type'] = strtoupper($type);

                foreach($get as $name => $param)
                {
                    $g = $xml->element('get');
                    $g['@name'] = $name;
                    $g['@type'] = $param->type;
                    $g['@min'] = isset($param->min) ? $param->min : 'default (' . datatype::min($param->type) . ')';
                    $g['@max'] = isset($param->max) ? $param->max : 'default (' . datatype::max($param->type) . ')';
                    $g['@required'] = $param->required ? 'true' : 'false';
                    !isset($param->default) or $g['@default'] = $param->default;
                    $g['@secure'] = $param->secure ? 'true' : 'false';
                    $method->append($g);
                }

                foreach($post as $name => $param)
                {
                    $p = $xml->element('post');
                    $p['@name'] = $name;
                    $p['@type'] = $param->type;
                    $p['@min'] = isset($param->min) ? $param->min : 'default (' . datatype::min($param->type) . ')';
                    $p['@max'] = isset($param->max) ? $param->max : 'default (' . datatype::max($param->type) . ')';
                    $p['@required'] = $param->required ? 'true' : 'false';
                    !isset($param->default) or $p['@default'] = $param->default;
                    $p['@secure'] = $param->secure ? 'true' : 'false';
                    $method->append($p);
                }
            }
        }

        $xslt = new XSLTProcessor();
        $xsl = new DOMDocument();
        $xsl->load(www_root . 'backend/documentation.xsl');
        $xslt->importStylesheet($xsl);
        $documentation = $xslt->transformToDoc($xml->get());

        return (object)
        [
            'code'    => 200,
            'message' => 'OK',
            'headers' => ['Content-Type' => 'text/html'],
            'body'    => $documentation->saveXML()
        ];
    }

    function __call($name, $args)
    {
        return $this->query($name, $args[0]);
    }

    static function encode($object)
    {
        return json_encode($object, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    function call($type, $url, $get = [], $post = [])
    {
        isset($this->methods[$url]) or backend_error('bad_query', 'Method not found');

        foreach($this->methods[$url]->find($type, $get, $post) as $method)
        {
            if($group = $method->access())
            {
                if(!$this->access->parse_evaluate($group, array_merge($get, $post)))
                {
                    continue;
                }
            }

            return $method->call($get, $post);
        }

        backend_error('bad_query', 'Method not found');
    }

    static $success;
    static $error;

    private $encoders = [];
    private $methods = [];
    private $access;
    private $dispatcher;
    private $batch = null;
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