<?php

class twig
{
    function __construct($templates, $options, $extensions)
    {
        $loader = new Twig_Loader_Filesystem($templates);
        $this->twig = new Twig_Environment($loader, $options);

        if(isset($extensions->filters))
        {
            foreach($extensions->filters as $name => $closure)
            {
                if($closure instanceof Closure)
                {
                    $this->twig->addFilter(new Twig_SimpleFilter($name, $closure));
                }
                else
                {
                    $options = [];
                    if(isset($closure->escape) and !$closure->escape)
                    {
                        $options['is_safe'] = ['html'];
                    }
                    $this->twig->addFilter(new Twig_SimpleFilter($name, $closure->function, $options));
                }
            }
        }

        if(isset($extensions->functions))
        {
            foreach($extensions->functions as $name => $closure)
            {
                $this->twig->addFunction(new Twig_SimpleFunction($name, $closure));
            }
        }
    }

    function load($filename)
    {
        return $this->twig->loadTemplate($filename);
    }

    private $twig;
}

?>