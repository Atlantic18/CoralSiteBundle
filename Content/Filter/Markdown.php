<?php

namespace Coral\SiteBundle\Content\Filter;

use Knp\Bundle\MarkdownBundle\Helper\MarkdownHelper;

class Markdown implements FilterInterface
{
    private $markdownHelper;

    function __construct(MarkdownHelper $markdownHelper)
    {
        $this->markdownHelper = $markdownHelper;
    }

    /**
     * Convert input string to output
     *
     * @param  string $input
     * @return string
     */
    public function render($input)
    {
        return $this->markdownHelper->transform($input);
    }
}