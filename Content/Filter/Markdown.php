<?php

namespace Coral\SiteBundle\Content\Filter;

use Coral\SiteBundle\Content\Content;
use Twig\Extra\Markdown\MarkdownInterface;

class Markdown extends AbstractContentFilter implements FilterInterface
{
    private $markdownParser;

    function __construct(MarkdownInterface $markdownParser, $contentPath)
    {
        $this->markdownParser = $markdownParser;

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
        return $this->markdownParser->convert($this->getFileContent($content));
    }
}