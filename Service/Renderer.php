<?php

namespace Coral\SiteBundle\Service;

use Coral\SiteBundle\Content\Content;
use Coral\SiteBundle\Exception\RenderException;
use Coral\SiteBundle\Content\Filter\FilterInterface;

class Renderer
{
    /**
     * List of available filters for area
     *
     * @var array
     */
    private $filters;

    public function __construct()
    {
        $this->filters     = array();
    }

    /**
     * Inject filter via DI
     *
     * @param FilterInterface $filter
     */
    public function addFilter(FilterInterface $filter, $type)
    {
        $this->filters[$type] = $filter;
    }

    /**
     * Render Content via a filter
     *
     * @param  Content $content Content to render
     * @return string
     */
    public function render(Content $content)
    {
        if(!array_key_exists($content->getType(), $this->filters))
        {
            throw new RenderException(
                'Content filter [' . $content->getType() . '] not found. Available filters: ' .
                implode(', ', array_keys($this->filters)) . '.'
            );
        }

        return $this->filters[$content->getType()]->render($content->getContent());
    }
}