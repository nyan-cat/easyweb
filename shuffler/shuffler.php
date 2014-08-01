<?php

namespace shuffler;

require_once(__DIR__ . '/../rnd/generator.php');
require_once('branch.php');
require_once('node.php');
require_once('text.php');

class shuffler
{
    function __construct()
    {
        $this->generator = new \rnd\generator('');
    }

    function shuffle($template, $seed)
    {
        $this->generator->reset($seed);
        $node = new node();
        $this->parse($node, $template);
        return $node->get();
    }

    private function parse($node, $template)
    {
        $this->parse_node($node, $template, 0, strlen($template));
    }

    private function parse_node($node, $template, $begin, $end)
    {
        for($n = $begin, $tn = $begin; $n != $end; ++$n)
        {
            if($template[$n] == '[')
            {
                if($n != $begin)
                {
                    $node->push(new text(substr($template, $tn, $n - $tn)));
                }
                $begin_branch = $n + 1;
                $end_branch = self::search_bracket($template, $begin_branch, $end);
                $branch = new branch($this->generator);
                $this->parse_branch($branch, $template, $begin_branch, $end_branch);
                $node->push($branch);
                $n = $end_branch;
                $tn = $n + 1;
            }
        }
        if($tn != $end)
        {
            $node->push(new text(substr($template, $tn, $end - $tn)));
        }
    }

    private function parse_branch($branch, $template, $begin, $end)
    {
        $depth = 0;
        for($n = $begin, $tn = $begin; $n != $end; ++$n)
        {
            if($template[$n] == '[')
            {
                ++$depth;
            }
            else if($template[$n] == ']')
            {
                --$depth;
            }
            else if($template[$n] == '|' && !$depth)
            {
                $node = new node();
                $this->parse_node($node, $template, $tn, $n);
                $branch->push($node);
                $tn = $n + 1;
            }
        }
        $node = new node();
        $this->parse_node($node, $template, $tn, $end);
        $branch->push($node);
    }

    private static function search_bracket($template, $begin, $end)
    {
        $depth = 0;
        for($n = $begin; $n != $end; ++$n)
        {
            if($template[$n] == '[')
            {
                ++$depth;
            }
            else if($template[$n] == ']')
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

    private $generator;
}

?>