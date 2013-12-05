<?php

$config = xml::load($options['config']);

$templates = $config->root()->attribute('templates');
$engine = $config->root()->attribute('engine');
$cache = $config->root()->attribute('cache');
$schema = $config->root()->attribute('schema');
$api = new api($schema);

foreach($config->query('/config/pages//page') as $page)
{
    $this->router->insert(new page
    (
        $page['@url'],
        $config->query_assoc('param[@name and @get]', $page, '@name', '@get'),
        trim($page->value()),
        $templates,
        $page['@template'],
        $page->attribute('engine') ? $page['@engine'] : $engine,
        $cache,
        $api
    ));
}

?>