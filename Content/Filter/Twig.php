<?php

namespace Coral\SiteBundle\Content\Filter;

use Twig_Environment as Environment;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

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
     * @param  string $input
     * @return string
     */
    public function render($input)
    {
        return $this->twig->render($input, $this->context->all());
    }
}