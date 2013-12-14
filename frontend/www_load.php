<?php

$config = xml::load_absolute($options->config);

$templates = $config->root()->attribute('templates');
$data = $config->root()->attribute('data');
$engine = $config->root()->attribute('engine');
$schema = $config->root()->attribute('schema');
$api = new api($schema);

foreach($config->query('/config/pages//page') as $page)
{
    $params = [];

    foreach($config->query_assoc('param[@name and @value]', $page, '@name', '@value') as $name => $value)
    {
        $params[$name] = (object) ['type' => 'value', 'value' => $value];
    }

    foreach($config->query_assoc('param[@name and @get]', $page, '@name', '@get') as $name => $get)
    {
        $params[$name] = (object) ['type' => 'get', 'value' => $get];
    }

    $this->router->insert(new page
    (
        $page['@url'],
        $params,
        trim($page->value()),
        $templates,
        $data,
        isset($options->cache) ? $options->cache : null,
        $page['@template'],
        $page->attribute('engine') ? $page['@engine'] : $engine,
        $api
    ));
}

?>