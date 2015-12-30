<?php

namespace Coral\SiteBundle\Content\Filter;

use Coral\SiteBundle\Content\Content;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Twig_Environment as Environment;

class Twig implements FilterInterface
{
    private $twig;
    private $context;

    function __construct(Environment $twig, ParameterBag $context)
    {
        $this->twig      = $twig;
        $this->context   = $context;
    }

    /**
     * Convert input string to output
     *
     * @param  Content $content
     * @return string
     */
    public function render(Content $content)
    {
        return $this->twig->render('@coral' . $content->getPath());
    }
}