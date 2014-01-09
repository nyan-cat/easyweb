<?php

require_once('page.php');

class router
{
    function insert($page)
    {
        $this->pages[] = $page;
    }

    function extend($extensions)
    {
        $this->extensions = $extensions;
    }

    function request($url, $global, $get, $post, $cookies, $files)
    {
        foreach($this->pages as $page)
        {
            if($page->match($url, $params))
            {
                return $page->request($params, $global, $get, $post, $cookies, $files, $this->extensions);
            }
        }

        return null;
    }

    private $pages = [];
    private $extensions = null;
}

?>