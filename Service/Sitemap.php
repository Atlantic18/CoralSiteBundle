<?php

namespace Coral\SiteBundle\Service;

use Coral\SiteBundle\Content\Node;
use Coral\SiteBundle\Utility\PropertiesParser;
use Coral\SiteBundle\Utility\SortorderParser;
use Coral\SiteBundle\Utility\Finder;
use Symfony\Contracts\Cache\CacheInterface as Cache;

class Sitemap
{
    /**
     * Root path where the content is stored
     *
     * @var string
     */
    private $contentPath;
    /**
     * Cache driver
     *
     * @var Cache
     */
    private $cache;

    public function __construct(Cache $cache, $contentPath)
    {
        $this->contentPath = $contentPath;
        $this->cache       = $cache;
    }

    /**
     * Recursively builds a structure of the sitemap from files
     *
     * @param  string $path
     * @throws \Coral\SiteBundle\Exception\SitemapException in case .properties file is missing
     * @return Node
     */
    private function readNode($path)
    {
        $finder = new Finder($path);

        if(false !== $finder->getPropertiesPath())
        {
            $uri = str_replace($this->contentPath, '', $path);
            $uri = $uri ? $uri : '/';
            $properties = PropertiesParser::parse($finder);
            $node = new Node($properties['name'], $uri);

            //Add child nodes and link references
            $index = 0;
            foreach (SortorderParser::parse($finder) as $subPath)
            {
                $child = $this->readNode($path . DIRECTORY_SEPARATOR . $subPath);
                $node->addChildAsLast($child);
                $child->setParent($node);

                if($index)
                {
                    $child->setPrev($node->getChildByIndex($index - 1));
                    $node->getChildByIndex($index - 1)->setNext($child);
                }

                $index++;
            }

            //Set properties and set tree_ properties to nodes
            foreach ($properties['properties'] as $key => $value)
            {
                $node->setProperty($key, $value);
            }

            return $node;
        }

        throw new \Coral\SiteBundle\Exception\SitemapException("Unable to find properties for: [$path]");
    }

    /**
     * Get Root node
     * @return [type] [description]
     */
    public function getRoot()
    {
        $cacheKey = 'coral.sitemap.nodes';
        return $this->cache->get($cacheKey, function() {
            return $this->readNode($this->contentPath);
        });
    }
}