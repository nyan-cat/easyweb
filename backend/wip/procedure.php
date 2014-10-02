<?php

require_once(www_root . 'backend/wip/datatype.php');

class procedure
{
    function __construct($params, $required, $result, $output = null)
    {
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

            if($this->result == 'object')
            {
                $this->postprocess($result);
            }
            elseif($this->result == 'array')
            {
                foreach($result as &$object)
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
            else
            {
                error('bad_query_result', 'Only object or array can be post-processed');
            }

            return $result;
        }
    }

    function params()
    {
        return $this->params;
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
                    self::embed($object, $name);
                }
                elseif($options->filter == 'json')
                {
                    $object->$name = json\decode($object->$name);
                }
            }
        }
    }

    static private function embed(&$object, $metadata)
    {
        foreach(json\decode($object->$metadata, true) as $name => $value)
        {
            $object->$name = $value;
        }
    }

    private $params;
    protected $required;
    protected $result;
    private $output;
}

?>