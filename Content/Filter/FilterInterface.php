<?php

namespace Coral\SiteBundle\Content\Filter;

interface FilterInterface
{
    /**
     * Convert input string to output
     *
     * @param  string $input
     * @return string
     */
    public function render($input);
}