<?php

namespace json;

require_once(www_root . 'json/document.php');
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

    function get($slice = '')
    {
        $schema = $this->schema;
        $properties = (object) [];

        foreach($this->schema->properties as $name => $property)
        {
            if(!isset($property->slice) or
                (is_array($property->slice) and in_array($slice, $property->slice)) or
                (!is_array($property->slice) and $property->slice == $slice))
            {
                $properties->$name = $property;
            }
        }

        $schema->properties = $properties;

        return $schema;
    }

    function create($source)
    {
        if($source instanceof document)
        {
            return $this->create(function($path) use($source)
            {
                return isset($source[$path]) ? $source[$path] : null;
            });
        }
        else
        {
            $result = [];
            foreach($this->schema->properties as $name => $property)
            {
                if(($value = $this->fetch($property, $name, $source)) !== null)
                {
                    $result[$name] = $value;
                }
            }
            return $result;
        }
    }

    function solr()
    {
        $document = new \xml\document();
        $fields = $document->element('fields');

        $this->make_solr(null, $this->schema->properties, $document, $fields);

        $document->append($fields);

        header ("Content-Type:text/xml");

        echo $document->render(); die();
    }

    private function make_solr($path, $properties, $document, $fields)
    {
        foreach($properties as $name => $property)
        {
            if(isset($this->schema->types->{$property->type}))
            {
                $property = $this->schema->types->{$property->type};
            }

            $current = $path === null ? $name : "$path.$name";

            if($property->type == 'object')
            {
                $this->make_solr($current, $property->properties, $document, $fields);
            }
            else
            {
                $types =
                [
                    'enum'    => 'string',
                    'boolean' => 'boolean',
                    'integer' => 'int',
                    'number'  => 'float'
                ];

                if(isset($types[$property->type]))
                {
                    $field = $document->element('field');
                    $field['@name'] = $current;
                    $field['@type'] = $types[$property->type];
                    $field['@required'] = (isset($property->required) and !$property->required) ? 'false' : 'true';
                    $field['@indexed'] = 'true';
                    $field['@stored'] = 'true';
                    $fields->append($field);
                }
            }
        }
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

    private function fetch($property, $path, $source, $required = true)
    {        
        if(isset($this->schema->types->{$property->type}))
        {
            $property = $this->schema->types->{$property->type};
        }

        if($property->type == 'object')
        {
            $result = [];
            foreach($property->properties as $name => $child)
            {
                if(($value = $this->fetch($child, "$path.$name", $source)) !== null)
                {
                    $result[$name] = $value;
                }
            }
            return $result;
        }
        elseif($property->type == 'array')
        {
            $result = [];
            for($n = 0; $n < $property->max; ++$n)
            {
                if(($value = $this->fetch($property->item, $path . "[$n]", $source, false)) !== null)
                {
                    $result[] = $value;
                }
            }
            $count = count($result);
            ($count >= $property->min and $count <= $property->max)
                or error('bad_parameter', 'Bad metadata array size: ' . $path . ', expected [' . $property->min . '; ' . $property->max . '], ' . $count . ' given');
            return $result;
        }
        else
        {
            $source = is_array($source) ? isset($source[$path]) ? $source[$path] : $source['*'] : $source;
            $source = is_array($source) ? isset($source[$property->type]) ? $source[$property->type] : $source['*'] : $source;

            $result = $source($path, $property);

            if($result === null and $required)
            {
                (isset($property->required) and !$property->required) or error('missing_parameter', 'Required metadata parameter not set: ' . $path);
            }

            return $result;
        }
    }

    private static function load_schema($filename)
    {
        $schema = decode(\fs\read($filename), true);
        if(isset($schema['extend']))
        {
            $extend = $schema['extend'];
            unset($schema['extend']);
            $schema = join(self::load_schema(\fs\path($filename) . '/' . $extend), $schema);
        }
        return $schema;
    }

    private $schema;
}

?>