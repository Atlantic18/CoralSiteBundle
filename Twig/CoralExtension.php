<?php

namespace Coral\SiteBundle\Twig;

use Knp\Bundle\MarkdownBundle\Helper\MarkdownHelper;

class CoralExtension extends \Twig_Extension
{
    protected $markdownHelper;

    function __construct(MarkdownHelper $markdownHelper)
    {
        $this->markdownHelper = $markdownHelper;
    }

    public function getFilters()
    {
        return array(
            'coral' => new \Twig_Filter_Method($this, 'coral', array('is_safe' => array('html'))),
        );
    }

    public function coral($text, $renderer)
    {
        if($renderer == 'json')
        {
            return "<pre><code data-language=\"javascript\">$text</code></pre>";
        }
        if($renderer == 'markdown')
        {
            return $this->markdownHelper->transform($text);
        }
        return $text;
    }

    public function getName()
    {
        return 'coral';
    }
}