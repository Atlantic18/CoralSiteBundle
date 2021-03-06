<?php

namespace Coral\SiteBundle\Content\Filter;

use Coral\SiteBundle\Content\Content;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;

class Markdown extends AbstractContentFilter implements FilterInterface
{
    private $markdownParser;

    function __construct(MarkdownParserInterface $markdownParser, $contentPath)
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
        return $this->markdownParser->transformMarkdown($this->getFileContent($content));
    }
}