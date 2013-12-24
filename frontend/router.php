<?php

require_once('page.php');

class router
{
    function insert($page)
    {
        $this->pages[] = $page;
    }

    function request($url, $global, $get, $post, $cookies, $files)
    {
        foreach($this->pages as $page)
        {
            if($page->match($url, $params))
            {
                return $page->request($params, $global, $get, $post, $cookies, $files);
            }
        }

        return null;
    }

    private $pages = [];
}

?>