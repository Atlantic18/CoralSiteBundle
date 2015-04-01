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
     * Path where the content is stored
     *
     * @var string
     */
    private $path;

    /**
     * Content constructor
     *
     * @param string $type
     * @param string $content
     */
    public function __construct($type, $content, $path = null)
    {
        $this->type    = $type;
        $this->content = $content;
        $this->path    = $path;
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

    /**
     * Get Path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}