<?php

require_once(www_root . 'backend/wip/datatype.php');

class procedure
{
    function __construct($name, $params, $required, $result, $output = null)
    {
        $this->name = $name;
        $this->params = $params;
        $this->required = $required;
        $this->result = $result;
        $this->output = $output;
    }

    function query($params)
    {
        $this->preprocess($params);

        if($this->output === null)
        {
            return $this->query_direct($params);
        }
        else
        {
            $result = $this->query_direct($params);

            if($result !== null)
            {
                if($this->result == 'value')
                {
                    $object = (object) [$this->name => $result];
                    $this->postprocess($object);
                    $result = $object->{$this->name};
                }
                elseif($this->result == 'object')
                {
                    $this->postprocess($result);
                }
                elseif($this->result == 'array')
                {
                    foreach($this->array_ref($result) as &$object)
                    {
                        $this->postprocess($object);
                    }
                }
                elseif($this->result == 'multiarray')
                {
                    foreach($result as &$array)
                    {
                        foreach($array as &$object)
                        {
                            $this->postprocess($object);
                        }
                    }
                }
            }

            return $result;
        }
    }

    function params()
    {
        return $this->params;
    }

    protected function & array_ref(&$object)
    {
        return $object;
    }

    private function preprocess(&$params)
    {
        foreach($this->params as $name => $options)
        {
            if(isset($options->type))
            {
                datatype::assert($options->type, $params[$name]);
            }
            if(isset($options->filter) and $options->filter == 'json')
            {
                $params[$name] = json\encode($params[$name]);
            }
        }
    }

    private function postprocess(&$object)
    {
        foreach($this->output as $name => $options)
        {
            if(isset($object->$name) and isset($options->filter))
            {
                if($options->filter == 'embed')
                {
                    if(empty($object->$name))
                    {
                        unset($object->$name);
                    }
                    else
                    {
                        self::embed($object, $name);
                    }
                }
                elseif($options->filter == 'json')
                {
                    if(empty($object->$name))
                    {
                        unset($object->$name);
                    }
                    else
                    {
                        $object->$name = json\decode($object->$name);
                    }
                }
            }
        }
    }

    static private function embed(&$object, $metadata)
    {
        if(($json = json\decode($object->$metadata, true)) !== null)
        {
            if(is_array($json))
            {
                foreach($json as $name => $value)
                {
                    $object->$name = $value;
                }
            }
        }
    }

    private $name;
    private $params;
    protected $required;
    protected $result;
    private $output;
}

?>