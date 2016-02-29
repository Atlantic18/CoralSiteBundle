<?php

namespace Coral\SiteBundle\Content\Filter;

use Coral\SiteBundle\Content\Content;
use Coral\SiteBundle\Exception\ConfigurationException;
use Coral\SiteBundle\Exception\RenderException;

abstract class AbstractContentFilter
{

    /**
     * Root path where the content is stored
     *
     * @var string
     */
    private $contentPath;

    /**
     * Set content path to be used as base patch for relative Content->getPath()
     *
     * @param string $contentPath Content Path
     */
    protected function setContentPath($contentPath)
    {
        $contentPath = realpath($contentPath);

        if(false === $contentPath)
        {
            throw new ConfigurationException("Unable to realpath: [$contentPath]");
        }

        $this->contentPath = $contentPath;
    }

    /**
     * Content path string - base path
     *
     * @return string base path
     */
    protected function getContentPath()
    {
        return $this->contentPath;
    }

    /**
     * Reads a file content of Content object
     *
     * @param  Content $content
     * @return string
     */
    protected function getFileContent(Content $content)
    {
        $text = @file_get_contents($this->contentPath . $content->getPath());

        // @codeCoverageIgnoreStart
        if(null === $this->getContentPath())
        {
            throw new ConfigurationException('ContentPath is not set. Call setContentPath before reading a file content.');
        }
        // @codeCoverageIgnoreEnd

        if(false === $text)
        {
            throw new RenderException('Unable to read: ' . $content->getPath() . '. Full path: ' . ($this->getContentPath() . $content->getPath()));
        }

        return $text;
    }
}