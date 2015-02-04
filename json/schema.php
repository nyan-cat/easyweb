<?php

namespace json;

require_once(www_root . 'json/flat.php');
require_once(www_root . 'filesystem/filesystem.php');
require_once(www_root . 'xml/document.php');

class schema
{
    function __construct($schema)
    {
        if(!is_object($schema))
        {
            $schema = decode(encode($schema));
        }
        $this->schema = $schema;
    }

    function get()
    {
        return $this->schema;
    }

    function create($source, $required = true)
    {
        $flat = new flat();

        $this->traverse(function($path, $property) use($source, $flat)
        {
            $closure = is_array($source) ? isset($source[$path]) ? $source[$path] : $source['*'] : $source;
            $closure = is_array($closure) ? isset($closure[$property->type]) ? $closure[$property->type] : $closure['*'] : $closure;

            if(($result = $closure($path, $property)) !== null)
            {
                $flat[$path] = $result;
            }
        });

        return $required ? $flat->render() : $flat;
    }

    function post($post, $required = true)
    {
        return $this->create(function($path, $property) use($post)
        {
            $param = str_replace('.', '_', $path);

            if(in_array($property->type, ['boolean']))
            {
                return isset($post->$param) and strtolower($post->$param) == 'on';
            }
            else
            {
                return isset($post->$param) ? $post->$param : null;
            }
        }, $required);
    }

    function solr()
    {
        $document = new \xml\document();
        $fields = $document->element('fields');

        $this->traverse(function($path, $property) use($document, $fields)
        {
            $types =
            [
                'boolean'  => 'boolean',
                'datetime' => 'date',
                'enum'     => 'string',
                'integer'  => 'int',
                'number'   => 'float',
                'string'   => 'string'
            ];

            $required = (isset($property->required) and $property->required) ? 'true' : 'false';
            $stored = 'true';
            $multiValued = 'false';

            if(isset($types[$property->type]))
            {
                $field = $document->element('field');
                $field['@name'] = $path;
                $field['@type'] = $types[$property->type];
                $field['@required'] = $required;
                $field['@indexed'] = in_array($property->type, ['string']) ? 'false' : 'true';
                $field['@stored'] = $stored;
                $field['@multiValued'] = $multiValued;
                $fields->append($field);
            }
        });

        $document->append($fields);

        return $document->render();
    }

    function conditions($get)
    {
        # TODO: Replace with something from the library
        $escape = function($string)
        {
            return str_replace('.', '_', $string);
        };

        $query = [];

        $this->traverse(function($path, $property) use($escape, $get, &$query)
        {
            switch($property->type)
            {
            case 'enum':
                $filter = (object)
                [
                    'condition' => 'any',
                    'values'    => []
                ];
                foreach($property->items as $item)
                {
                    $param = $escape("$path.$item");
                    if(isset($get->$param) and strtolower($get->$param) == 'on')
                    {
                        $filter->values[] = $item;
                    }
                }
                if(!empty($filter->values) and count($filter->values) < count($property->items))
                {
                    $query[$path] = $filter;
                }
                break;

            case 'integer':
                if(isset($property->min) and isset($property->max))
                {
                    $min = $escape("$path.min");
                    $max = $escape("$path.max");

                    if(isset($get->$min) and isset($get->$max))
                    {
                        $query[$path] = (object)
                        [
                            'condition' => 'range',
                            'min'       => $get->$min,
                            'max'       => $get->$max
                        ];
                    }
                }
                break;

            case 'money-optional':
                $param = $escape($path);
                if(isset($get->$param) and strtolower($get->$param) == 'on')
                {
                    $query["$path.enabled"] = (object)
                    [
                        'condition' => 'equal',
                        'value'     => true
                    ];
                }
                break;
            }

        }, false);

        return $query;
    }

    static function query($query, $solr)
    {
        foreach($query as $name => $param)
        {
            switch($param['condition'])
            {
            case 'any':
                $solr[] = $name . ':(' . implode(' OR ', $param['values']) . ')';
                break;

            case 'range':
                $solr[] = $name . ':[' . $param['min'] . ' TO ' . $param['max'] . ']';
                break;

            case 'equal':
                $solr[] = $name . ':' . $param['value'];
                break;
            }
        }

        return implode(' AND ', $solr);
    }

    static function load($filename)
    {
        $schema = self::load_schema($filename);
        if(isset($schema['types']))
        {
            foreach($schema['types'] as $name => &$type)
            {
                $path = \fs\path($filename) . '/' . $type;
                $type = decode(\fs\read($path), true);
            }
        }
        return new schema($schema);
    }

    private static function load_schema($filename)
    {
        $schema = decode(\fs\read($filename), true);
        if(isset($schema['extends']))
        {
            $extends = $schema['extends'];
            unset($schema['extends']);
            $schema = join(self::load_schema(\fs\path($filename) . '/' . $extends), $schema);
        }
        return $schema;
    }

    function traverse($closure, $resolve = true, $path = null, $object = null)
    {
        if($object === null)
        {
            $object = $this->schema->properties;
        }

        foreach($object as $name => $property)
        {
            $current = $path === null ? $name : "$path.$name";

            if($resolve and isset($this->schema->types->{$property->type}))
            {
                $property = $this->schema->types->{$property->type};
            }

            if($property->type == 'object')
            {
                $this->traverse($closure, $resolve, $current, $property->properties);
            }
            else
            {
                $closure($current, $property);
            }
        }
    }

    private $schema;
}

?>