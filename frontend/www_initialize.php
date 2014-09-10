<?php

require_once(www_root . 'templating/twig.php');

$this->api = new api($config->schema);

if($config->engine == 'twig')
{
    $this->templaters[$config->engine] = new twig($config->templates, [], $extensions[$config->engine]);
}

foreach($config->pages as $page)
{
    $templater = isset($page->engine) ? $page->engine : $config->engine;

    $this->router->attach(isset($page->method) ? $page->method : 'GET', new page
    (
        $page->uri,
        $page->params,
        isset($page->template) ? $this->templaters[$templater]->load($page->template) : null,
        isset($page->script) ? new script($this->api, $page->script) : null,
        $this->api
    ));
}

?>