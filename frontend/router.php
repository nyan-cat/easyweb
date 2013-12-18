<?php

require_once('page.php');

class router
{
    function insert($page)
    {
        $this->pages[] = $page;
    }

    function request($url, $global)
    {
        foreach($this->pages as $page)
        {
            if($page->match($url, $params))
            {
                return $page->request($params, $global);
            }
        }

        return null;
    }

    private $pages = [];
}

?>