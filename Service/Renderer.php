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
    /**
     * Root path where the content is stored
     *
     * @var string
     */
    private $contentPath;

    public function __construct($contentPath)
    {
        $this->contentPath    = $contentPath;
        $this->filters        = array();
    }

    /**
     * Render a file to include
     *
     * @param  string $fileName File name to be rendered from include paths or a relative file name
     * @return string rendered include
     */
    private function renderIncludeForContent(Content $content, $fileName)
    {
        $directory       = dirname($fileName);
        $baseName        = basename($fileName);
        $realContentPath = realpath($this->contentPath);

        // Including a file from current directory
        if($directory == '.')
        {
            $realDirectory = realpath($this->contentPath . DIRECTORY_SEPARATOR . dirname($content->getPath()));
        }
        // Include a file based on relative path
        elseif($directory[0] == '.')
        {
            $realDirectory = realpath($this->contentPath . DIRECTORY_SEPARATOR . dirname($content->getPath()) . DIRECTORY_SEPARATOR . $directory);
        }
        // Including a file based on absolute path
        else
        {
            $realDirectory = realpath($this->contentPath . DIRECTORY_SEPARATOR . $directory);
        }

        if(strcmp($realContentPath, $realDirectory) <= 0)
        {
            $fullPath = $realDirectory . DIRECTORY_SEPARATOR . $baseName;
            if(file_exists($fullPath))
            {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                return $this->render(new Content($type, substr($fullPath, strlen($realContentPath))));
            }

            throw new \InvalidArgumentException("Include file [$fullPath] does not exist.");
        }

        throw new \InvalidArgumentException($content->getPath() . "Unable to render [$realDirectory/$fileName]. It is outside of allowed content root path [$realContentPath].");
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

        $renderedContent = $this->filters[$content->getType()]->render($content);

        if(preg_match_all('/\{\{\s*(include)\s+([a-z0-9\_\-\.\/]+)\s*\}\}/i', $renderedContent, $matches))
        {
            $matchesCount = count($matches[0]);
            for($i = 0; $i < $matchesCount; $i++)
            {
                $includedContent = $this->renderIncludeForContent($content, $matches[2][$i]);
                $renderedContent = str_replace($matches[0][$i], $includedContent, $renderedContent);
            }
        }

        return $renderedContent;
    }
}