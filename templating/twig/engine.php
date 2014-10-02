<?php

namespace twig;

require_once(www_root . 'templating/twig/template.php');

class engine
{
    function __construct($templates, $options, $data, $locale)
    {
        $loader = new \Twig_Loader_Filesystem($templates);
        $this->twig = new \Twig_Environment($loader, $options);
        $this->data = $data;
        $this->locale = $locale;
    }

    function extend($extensions)
    {
        $extensions->filters +=
        [
            'ceil' => function($value)
            {
                return ceil($value);
            },
            'local' => function($alias)
            {
                return $this->locale->get($alias);
            }
        ];

        $extensions->functions +=
        [
            'json' => function($filename)
            {
                return \json\decode(\fs\read($this->data . $filename));
            }
        ];

        foreach($extensions->filters as $name => $closure)
        {
            if($closure instanceof \Closure)
            {
                $this->twig->addFilter(new \Twig_SimpleFilter($name, $closure));
            }
            else
            {
                $options = [];
                if(isset($closure->escape) and !$closure->escape)
                {
                    $options['is_safe'] = ['html'];
                }
                $this->twig->addFilter(new \Twig_SimpleFilter($name, $closure->function, $options));
            }
        }

        foreach($extensions->functions as $name => $closure)
        {
            $this->twig->addFunction(new \Twig_SimpleFunction($name, $closure));
        }
    }

    function load($filename)
    {
        return new template($this->twig, $filename);
    }

    private $twig;
    private $data;
    private $locale;
}

?>