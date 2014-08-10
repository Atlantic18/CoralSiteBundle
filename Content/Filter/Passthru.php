<?php

namespace Coral\SiteBundle\Content\Filter;

class Passthru implements FilterInterface
{
    function __construct()
    {
    }

    /**
     * Convert input string to output
     *
     * @param  string $input
     * @return string
     */
    public function render($input)
    {
        return $input;
    }
}