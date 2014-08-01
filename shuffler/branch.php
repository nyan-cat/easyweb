<?php

namespace shuffler;

class branch
{
    function __construct($generator, &$values, $name = null)
    {
        $this->generator = $generator;
        $this->values = &$values;
        $this->name = $name;
    }

    function push($node)
    {
        $this->branches[] = $node;
    }

    function get()
    {
        if($this->name === null or ($this->name !== null and $this->values[$this->name] === null))
        {
            $index = $this->generator->irand(count($this->branches));

            if($this->name !== null)
            {
                $this->values[$this->name] = $index;
            }
        }
        else
        {
            $index = $this->values[$this->name];
        }

        return $this->branches[$index]->get();
    }

    private $generator;
    private $values;
    private $name;
    private $branches = [];
}

?>