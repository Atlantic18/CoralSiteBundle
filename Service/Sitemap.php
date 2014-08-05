<?php

namespace Coral\SiteBundle\Service;

use Coral\SiteBundle\Content\Node;
use Coral\SiteBundle\Parser\PropertiesParser;
use Coral\SiteBundle\Parser\SortorderParser;
use Doctrine\Common\Cache\Cache;

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
        $propertiesFileName = $path . DIRECTORY_SEPARATOR . '.properties';
        $sortorderFileName  = $path . DIRECTORY_SEPARATOR . '.sortorder';

        if(file_exists($propertiesFileName))
        {
            $uri = str_replace($this->contentPath, '', $path);
            $uri = $uri ? $uri : '/';
            $properties = PropertiesParser::parse($propertiesFileName);
            $node = new Node($properties['name'], $uri);

            //Add child nodes and link references
            if(file_exists($sortorderFileName))
            {
                $index = 0;
                foreach (SortorderParser::parse($sortorderFileName) as $subPath)
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
            }

            //Set properties and set tree_ properties to nodes
            foreach ($properties['properties'] as $key => $value)
            {
                $node->setProperty($key, $value);
            }

            return $node;
        }

        throw new \Coral\SiteBundle\Exception\SitemapException("Unable to find properties for: [$propertiesFileName]");
    }

    /**
     * Get Root node
     * @return [type] [description]
     */
    public function getRoot()
    {
        $cacheKey = 'coral.sitemap.nodes';
        if(false === ($root = $this->cache->fetch($cacheKey)))
        {
            $root = $this->readNode($this->contentPath);

            if(!$this->cache->save($cacheKey, $root))
            {
                throw new \Coral\SiteBundle\Exception\SitemapException("Unable to store into cache.");
            }
        }

        return $root;
    }

    public function isRootCached()
    {
        return $this->cache->contains('coral.sitemap.nodes');
    }
}