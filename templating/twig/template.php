<?php

namespace twig;

class template
{
    function __construct($twig, $filename)
    {
        $this->twig = $twig;
        $this->filename = $filename;
    }

    function render($params)
    {
        if($this->template === null)
        {
            $this->template = $this->twig->loadTemplate($this->filename);
        }

        return $this->template->render($params);
    }

    private $twig;
    private $filename;
    private $template = null;
}

?>