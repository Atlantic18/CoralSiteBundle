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
     * Path where the content is stored
     *
     * @var string
     */
    private $path;

    /**
     * Content constructor
     *
     * @param string $type
     * @param string $path Path is relative to the coral content path can start either with / or be relative
     */
    public function __construct($type, $path)
    {
        $this->type = $type;

        //In case the path doesn't contain starting /
        //The path is not absolute, it's always within content path
        if(substr($path, 0, 1) != "/")
        {
            $path = '/' . $path;
        }

        $this->path = $path;
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
     * Get Path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}