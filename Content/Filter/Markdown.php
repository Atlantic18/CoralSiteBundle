<?php

namespace Coral\SiteBundle\Content\Filter;

use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;

class Markdown implements FilterInterface
{
    private $markdownParser;

    function __construct(MarkdownParserInterface $markdownParser)
    {
        $this->markdownParser = $markdownParser;
    }

    /**
     * Convert input string to output
     *
     * @param  string $input
     * @return string
     */
    public function render($input)
    {
        return $this->markdownParser->transformMarkdown($input);
    }
}