<?php

require_once(www_root . 'templating/twig/engine.php');

$this->api = new api($config->schema);
$this->locale = new locale($options->language, $options->country);
$this->locale->load($config->locale);
$this->vars = json\decode(fs\read($config->vars));

if($config->engine == 'twig')
{
    $this->templaters[$config->engine] = new twig\engine($config->templates, [], $config->data, $this->locale);
}

foreach($config->pages as $page)
{
    $templater = isset($page->engine) ? $page->engine : $config->engine;

    $this->router->attach(isset($page->method) ? $page->method : 'GET', new page
    (
        $page->uri,
        $page->params,
        isset($page->template) ? $this->templaters[$templater]->load($page->template) : null,
        isset($page->script) ? new script($this, $page->script) : null,
        $this->api
    ));
}

foreach($config->schemas as $name => $src)
{
    $this->schemas[$name] = json\schema::load($src);
}

?>