<?php

$config = xml::load($options->config);
$root = $config->root();

$templates = $root->attribute('templates');
$data = $root->attribute('data');
$scripts = $root->attribute('scripts');
$engine = $root->attribute('engine');
$schema = $root->attribute('schema');
$api = new api($schema);
$locale = new locale($options->language, $options->country);
$locale->load($root['@locale']);

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

    $require = [];

    foreach($config->query('require/@src', $page) as $src)
    {
        $require[] = $src->value();
    }

    $this->router->insert(new page
    (
        $page['@url'],
        $params,
        $require,
        trim($page->value()),
        $templates,
        $data,
        $scripts,
        isset($options->cache) ? $options->cache : null,
        $page['@template'],
        $page->attribute('engine') ? $page['@engine'] : $engine,
        $api,
        $locale
    ));
}

?>