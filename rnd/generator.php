<?php

namespace rnd;

class generator
{
    function __construct($seed)
    {
        $this->reset($seed);
    }

    function reset($seed)
    {
        $this->seed = $seed;
        $this->context = md5($seed);
    }

    function irand($min, $max = null)
    {
        if(is_null($max))
        {
            $max = $min;
            $min = 0;
        }
        $this->next();
        return gmp_intval(gmp_mod(gmp_init('0x' . $this->context), gmp_init($max - $min))) + $min;
    }

    private function next()
    {
        $this->context = md5($this->context . $this->seed);
    }

    private $seed;
    private $context = '';
}

?>