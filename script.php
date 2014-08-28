<?php

class script
{
    function __construct($self, $script)
    {
        $this->self = $self;
        $this->script = $script;
    }

    function evaluate($params)
    {
        $prototype = empty($params) ? '' : ('$' . implode(',$', array_keys($params)));

        $closure = eval("return function($prototype) { {$this->script} };");

        return call_user_func_array($closure->bindTo($this->self), array_values($params));
    }

    private $self;
    private $script;
}

?>