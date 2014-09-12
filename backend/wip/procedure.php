<?php

require_once(www_root . 'backend/wip/datatype.php');

class procedure
{
    function __construct($params, $required, $result, $embed = null)
    {
        $this->params = $params;
        $this->required = $required;
        $this->result = $result;
        $this->embed = $embed;
    }

    function query($params)
    {
        $this->preprocess($params);
        if($this->embed === null)
        {
            return $this->query_direct($params);
        }
        else
        {
            $result = $this->query_direct($params);

            if($this->result == 'object')
            {
                self::embed($result, $this->embed);
            }
            elseif($this->result == 'array')
            {
                foreach($result as $object)
                {
                    self::embed($object, $this->embed);
                }
            }
            else
            {
                error('bad_query_result', 'Only object or array can be embedded');
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
        foreach($params as $name => &$value)
        {
            if(isset($this->params[$name]->type) and $name[0] != '_')
            {
                datatype::assert($this->params[$name], $value);
            }
            if(isset($this->params[$name]->encode) and $this->params[$name]->encode == 'json')
            {

                $value = json\encode($value);
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
    private $embed;
}

?>