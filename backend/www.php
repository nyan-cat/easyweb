<?php

require_once(www_root . 'backend/method.php');
require_once(www_root . 'backend/method_group.php');
require_once(www_root . 'backend/sql.php');
require_once(www_root . 'backend/sql_procedure.php');
require_once(www_root . 'backend/solr.php');
require_once(www_root . 'backend/solr_procedure.php');
require_once(www_root . 'backend/foursquare.php');
require_once(www_root . 'backend/foursquare_procedure.php');
require_once(www_root . 'backend/geoip_procedure.php');
require_once(www_root . 'backend/php_procedure.php');
require_once(www_root . 'backend/dispatcher.php');
require_once(www_root . 'facilities/datetime.php');
require_once(www_root . 'facilities/filesystem.php');
require_once(www_root . 'facilities/image.php');
require_once(www_root . 'facilities/json.php');
require_once(www_root . 'facilities/string.php');
require_once(www_root . 'facilities/xml.php');

class www
{
    private function __construct($options)
    {
        $this->dispatcher = new dispatcher();
        include('www_load.php');
    }

    function __sleep()
    {
        return ['vars', 'methods', 'domains', 'folders', 'batch', 'dispatcher', 'schema', 'documentation'];
    }

    static function create($options)
    {
        if(isset($options->cache))
        {
            $cache = $options->cache . 'cache.tmp';
            if($www = fs::read($cache))
            {
                $www = unserialize($www);
                $www->bind();
                return $www;
            }
            else
            {
                $www = new www($options);
                fs::write($cache, serialize($www));
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

    function request($type, $url, $host, $request_headers, $post = [])
    {
        foreach($post as $name => $param)
        {
            if(is_string($param) and $param == '_empty_array')
            {
                $post[$name] = [];
            }
        }

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

                $body[$param] = $this->call('GET', $url, $host, $get);
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
                'body'    => $encoder['success']->__invoke( $this->call($type, $url, $host, $get, $post) )
            ];
        }
    }

    function wrap($mixed, $domain, $lifetime = 0)
    {
        isset($this->domains[$domain]) or backend_error('bad_method', 'Unknown domain: ' . $domain);

        $expire_at = $lifetime ? @time() + $lifetime : 0;

        return security::wrap($mixed, $this->domains[$domain], $expire_at);
    }

    function access($groups)
    {
        $result = [];

        foreach($groups as $name => $args)
        {
            if(preg_match('/\A\w+\Z/', $name) ? $this->$name($args) : $this->dispatcher->parse_query($name, $args))
            {
                $result[] = $name;
            }
        }

        return $result;
    }

    function folder($name)
    {
        isset($this->folders[$name]) or backend_error('bad_config', "Unknown folder $name");
        return $this->folders[$name];
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
                    !isset($param->domains) or $g['@domain'] = implode(', ', $param->domains);
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
                    !isset($param->domains) or $p['@domain'] = implode(', ', $param->domains);
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
        return $this->dispatcher->__call($name, $args);
    }

    function parse_query($name, $args)
    {
        return $this->dispatcher->parse_query($name, $args);
    }

    static function encode($object)
    {
        return json_encode($object, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    function call($type, $url, $host, $get = [], $post = [])
    {
        isset($this->methods[$url]) or backend_error('bad_query', 'Method not found: ' . $url . ' | ' . implode(', ', array_keys($get)) . ' | ' . implode(', ', array_keys($post)));

        foreach($this->methods[$url]->find($type, $get, $post) as $method)
        {
            if($method->host() == '*' or $method->host() == $host)
            {
                list($matched, $result) = $method->call($get, $post);

                if($matched)
                {
                    return $result;
                }
            }
        }

        backend_error('bad_query', 'Method not found: ' . $url . ' | ' . implode(', ', array_keys($get)) . ' | ' . implode(', ', array_keys($post)));
    }

    function get($url, $params = [])
    {
        return $this->call('GET', $url, '*', $params);
    }

    function post($url, $post = [], $get = [])
    {
        return $this->call('POST', $url, '*', $get, $post);
    }

    function variable($name)
    {
        return isset($this->vars[$name]) ? $this->vars[$name] : "[Variable not found: $name]";
    }

    static $success;
    static $error;

    private $encoders = [];
    private $vars = [];
    private $methods = [];
    private $domains = [];
    private $dispatcher;
    private $folders = [];
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