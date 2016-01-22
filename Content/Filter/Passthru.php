<?php

namespace Coral\SiteBundle\Content\Filter;

use Coral\SiteBundle\Content\Content;

class Passthru extends AbstractContentFilter implements FilterInterface
{
    /**
     * Content path - base path - for file content reading
     *
     * @param string $contentPath base path
     */
    function __construct($contentPath)
    {
        $this->setContentPath($contentPath);
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
        return $this->getFileContent($content);
    }
}