<?php

class morpher_generator
{
    const salt = '123456';

    function __construct($seed)
    {
        $this->seed = $seed;
        $this->value = md5($seed . self::salt);
    }

    function get($min, $max)
    {
        $this->value = md5($this->value . $this->seed . self::salt);
        $value = hexdec(substr($this->value, 0, 8));
        return $min + ($value % ($max - $min + 1));
    }

    private $seed;
    private $value;
}

class morpher_node_text
{
    function __construct($value)
    {
        $this->value = $value;
    }

    function get()
    {
        return $this->value;
    }

    private $value;
}

class morpher_node_variant
{
    function __construct($generator)
    {
        $this->generator = $generator;
    }

    function push($node)
    {
        $this->variants[] = $node;
    }

    function get()
    {
        $position = $this->generator->get(0, count($this->variants) - 1);
        return $this->variants[$position]->get();
    }

    private $generator;
    private $variants = array();
}

class morpher_node
{
    function push($node)
    {
        $this->children[] = $node;
    }

    function get()
    {
        $text = '';
        foreach($this->children as $node)
        {
            $text .= $node->get();
        }
        return $text;
    }

    private $children = array();
}

class morpher
{
    const variant_begin = '[';
    const variant_delimiter = '|';
    const variant_end = ']';

    function __construct($template, $seed)
    {
        $this->node = new morpher_node();
        self::parse_node($this->node, $template, 0, strlen($template), new morpher_generator($seed));
    }

    static function get($template, $seed)
    {
        $morpher = new morpher($template, $seed);
        return $morpher->roll();
    }

    function roll()
    {
        return $this->node->get();
    }

    private static function variant_ending($str, $begin, $end)
    {
        $depth = 0;
        for($n = $begin; $n != $end; ++$n)
        {
            if($str[$n] == self::variant_begin)
            {
                ++$depth;
            }
            else if($str[$n] == self::variant_end)
            {
                if($depth)
                {
                    --$depth;
                }
                else
                {
                    return $n;
                }
            }
        }
        return $end;
    }

    private static function parse_variant($variant, $str, $begin, $end, $generator)
    {
        $depth = 0;
        for($n = $begin, $tn = $begin; $n != $end; ++$n)
        {
            if($str[$n] == self::variant_begin)
            {
                ++$depth;
            }
            else if($str[$n] == self::variant_end)
            {
                --$depth;
            }
            else if($str[$n] == self::variant_delimiter && !$depth)
            {
                $node = new morpher_node();
                self::parse_node($node, $str, $tn, $n, $generator);
                $variant->push($node);
                $tn = $n + 1;
            }
        }
        $node = new morpher_node();
        self::parse_node($node, $str, $tn, $end, $generator);
        $variant->push($node);
    }

    private static function parse_node($node, $str, $begin, $end, $generator)
    {
        for($n = $begin, $tn = $begin; $n != $end; ++$n)
        {
            if($str[$n] == self::variant_begin)
            {
                if($n != $begin)
                {
                    $node->push(new morpher_node_text(substr($str, $tn, $n - $tn)));
                }
                $begin_variant = $n + 1;
                $end_variant = self::variant_ending($str, $begin_variant, $end);
                $variant = new morpher_node_variant($generator);
                self::parse_variant($variant, $str, $begin_variant, $end_variant, $generator);
                $node->push($variant);
                $n = $end_variant;
                $tn = $n + 1;
            }
        }
        if($tn != $end)
        {
            $node->push(new morpher_node_text(substr($str, $tn, $end - $tn)));
        }
    }

    private $node;
}

?>