<?php

class xtree
{
    static function create($xml)
    {
        $xtree = new xtree();
        foreach($xml->children() as $node)
        {
            $xtree->children[] = self::import($node);
        }
        return $xtree;
    }

    function xml()
    {
        $xml = new xml();
        foreach($this->children as $child)
        {
            $xml->append(self::node($xml, $child));
        }
        return $xml;
    }

    static private function import($node)
    {
        $xtree = new xtree();

        if($node->text())
        {
            $xtree->value = $node->value();
        }
        else if($node->element())
        {
            $xtree->name = $node->name();
            $xtree->attributes = $node->attributes();
            foreach($node->children() as $child)
            {
                $xtree->children[] = self::import($child);
            }
        }

        return $xtree;
    }

    static private function node($xml, $xtree)
    {
        if($xtree->name)
        {
            $node = $xml->element($xtree->name);
            foreach ($xtree->attributes as $name => $value)
            {
                $node["@$name"] = $value;
            }
            foreach ($xtree->children as $child)
            {
                $node->append(self::node($xml, $child));
            }
            return $node;
        }
        else
        {
            return $xml->text($xtree->value);
        }
    }

    private $name = null;
    private $attributes = array();
    private $value = null;
    private $children = array();
}

?>