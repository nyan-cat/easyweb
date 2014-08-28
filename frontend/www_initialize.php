<?php

require_once(www_root . 'templating/twig.php');

$this->api = new api($config->schema);

$templater = null;

if($config->engine == 'twig')
{
    $templater = new twig($config->templates, [], []);
}

foreach($config->pages as $page)
{
    $this->router->attach(isset($page->method) ? $page->method : 'GET', new page
    (
        $page->uri,
        $page->params,
        isset($page->template) ? $templater->load($page->template) : null,
        isset($page->script) ? new script($this->api, $page->script) : null,
        $this->api
    ));
}

?>