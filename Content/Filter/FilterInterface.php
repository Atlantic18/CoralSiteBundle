<?php

namespace Coral\SiteBundle\Content\Filter;

use Coral\SiteBundle\Content\Content;

interface FilterInterface
{
    /**
     * Convert input Content to output
     *
     * @param  Content $content
     * @return string
     */
    public function render(Content $content);
}