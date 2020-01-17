<?php

namespace Coral\SiteBundle\Content\Filter;

use Coral\SiteBundle\Content\Content;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Twig\Environment as Environment;

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
     * Convert input Content to output
     *
     * @param  Content $content
     * @param  array   $parameters from renderer
     * @return string
     */
    public function render(Content $content, $parameters)
    {
        $parameters['context'] = $this->context->all();
        return $this->twig->render('@coral' . $content->getPath(), $parameters);
    }
}