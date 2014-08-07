<?php

namespace Coral\SiteBundle\Content;

/**
 * DTO nothing more
 */
class Content
{
    /**
     * Content Type
     *
     * @var string
     */
    private $type;
    /**
     * Content
     *
     * @var string
     */
    private $content;

    /**
     * Content constructor
     *
     * @param string $type
     * @param string $content
     */
    public function __construct($type, $content)
    {
        $this->type    = $type;
        $this->content = $content;
    }

    /**
     * Get Content type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get Content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}