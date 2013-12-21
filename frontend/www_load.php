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

    foreach($config->query_assoc('param[@name and @query]', $page, '@name', '@query') as $name => $value)
    {
        $params[$name] = (object) ['type' => 'query', 'value' => $value];
    }

    foreach($config->query('param[@name and @get]', $page) as $param)
    {
        $array = ['type' => 'get', 'value' => $param['@get']];

        if($default = $param->attribute('default'))
        {
            $array['default'] = $default;
        }

        $params[$param['@name']] = (object) $array;
    }

    foreach($config->query('param[@name and @post]', $page) as $param)
    {
        $array = ['type' => 'post', 'value' => $param['@post']];

        if($default = $param->attribute('default'))
        {
            $array['default'] = $default;
        }

        $params[$param['@name']] = (object) $array;
    }

    foreach($config->query('param[@name and @cookie]', $page) as $param)
    {
        $array = ['type' => 'cookie', 'value' => $param['@cookie']];

        if($default = $param->attribute('default'))
        {
            $array['default'] = $default;
        }

        $params[$param['@name']] = (object) $array;
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
        $page->attribute('template'),
        $page->attribute('engine') ? $page['@engine'] : $engine,
        $api,
        $locale
    ));
}

?>