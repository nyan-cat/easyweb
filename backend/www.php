<?php

require_once(www_root . 'xml.php');
require_once(www_root . 'backend/method.php');
require_once(www_root . 'backend/sql.php');
require_once(www_root . 'backend/sql_procedure.php');
require_once(www_root . 'backend/solr.php');
require_once(www_root . 'backend/solr_procedure.php');
require_once(www_root . 'backend/foursquare.php');
require_once(www_root . 'backend/foursquare_procedure.php');
require_once(www_root . 'backend/geoip_procedure.php');
require_once(www_root . 'backend/dispatcher.php');

class www
{
    function __construct($success = null, $error = null)
    {
        $this->success = is_null($success) ? self::$default_success : $success;
        $this->error = is_null($error) ? self::$default_error : $error;
        $this->dispatcher = new dispatcher();
        include('www_load.php');
    }

    function request($type, $url, $post = [])
    {
        $query = parse_url($url);
        $path = $query['path'];

        if(isset($this->methods[$path]))
        {
            $method = $this->methods[$path];
            $get = [];

            if(isset($query['query']))
            {
                parse_str($query['query'], $get);
            }

            try
            {
                return $this->success->__invoke( $method->call($get, $post) );
            }
            catch(Exception $e)
            {
                return $this->error->__invoke('error', $e->getMessage());
            }
        }
        else if($path == $this->schema)
        {
            $schema = [];

            foreach($this->methods as $url => $method)
            {
                list($type, $get, $post) = $method->schema();
                $m = ['type' => $type];
                empty($get) or $m['get'] = $get;
                empty($post) or $m['post'] = $post;
                $schema[$url] = $m;
            }

            return self::encode($schema);
        }
        else if($path == $this->documentation)
        {
            $xml = new xml();

            $methods = $xml->element('methods');

            $xml->append($methods);

            foreach($this->methods as $url => $m)
            {
                $method = $xml->element('method');
                $method['@url'] = $url;
                $methods->append($method);

                list($type, $get, $post) = $m->schema();

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

            $xslt = new XSLTProcessor();
            $xsl = new DOMDocument();
            $xsl->load(www_root . 'backend/documentation.xsl');
            $xslt->importStylesheet($xsl);
            $documentation = $xslt->transformToDoc($xml->get());
            return $documentation->saveXML();
        }
        else
        {
            backend_error('bad_request', "Method not found: $path");
        }
    }

    function query($name, $args)
    {
        return $this->dispatcher($name, $args);
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

    static $default_success;
    static $default_error;

    private $success;
    private $error;
    private $methods = [];
    private $dispatcher;
    private $schema = null;
    private $documentation = null;
}

www::$default_success = function($content)
{
    return www::encode(['status' => 'success', 'content' => $content]);
};

www::$default_error = function($type, $message)
{
    return www::encode(['status' => 'error', 'type' => $type, 'message' => $message]);
};

?>