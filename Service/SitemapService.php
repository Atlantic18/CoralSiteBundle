<?php

namespace Coral\SiteBundle\Service;

use Coral\SiteBundle\Content\Node;
use Coral\SiteBundle\Parser\PropertiesParser;
use Coral\SiteBundle\Parser\SortorderParser;

class SitemapService
{
    private $contentPath;

    public function __construct($contentPath)
    {
        $this->contentPath = $contentPath;
    }

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

    public function getRoot()
    {
        return $this->readNode($this->contentPath);
    }
}