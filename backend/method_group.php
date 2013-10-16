<?php

class method_group
{
    function insert($method)
    {
        $this->methods[] = $method;
    }


    function find($type, $get, $post)
    {
        foreach($this->methods as $method)
        {
            if($method->match($type, $get, $post))
            {
                return $method;
            }
        }
        return null;
    }

    function schema()
    {
        $schema = [];

        foreach($this->methods as $method)
        {
            $schema[] = $method->schema();
        }

        return $schema;
    }

    private $methods = [];

}

?>