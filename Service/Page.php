<?php

namespace Coral\SiteBundle\Service;

use Coral\SiteBundle\Content\Area;
use Coral\SiteBundle\Content\Content;
use Coral\SiteBundle\Content\Node;
use Coral\SiteBundle\Utility\PropertiesParser;
use Coral\SiteBundle\Utility\SortorderParser;
use Coral\SiteBundle\Utility\Finder;
use Coral\SiteBundle\Exception\PageException;
use Coral\SiteBundle\Service\RequestFilter;
use Coral\SiteBundle\Exception\SitemapException;

use Symfony\Component\HttpFoundation\RequestStack;

class Page
{
    /**
     * List of Area
     *
     * @var array
     */
    private $areas;
    /**
     * Related sitemap Node of the page
     *
     * @var Node
     */
    private $node;
    /**
     * Root content path
     *
     * @var string
     */
    private $contentPath;
    /**
     * Request stack
     *
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * Page constructor
     *
     * @param Node   $node
     * @param string $contentPath Where the content is located
     */
    public function __construct(RequestStack $requestStack, $contentPath)
    {
        $this->node         = null;
        $this->areas        = null;
        $this->contentPath  = $contentPath;
        $this->requestStack = $requestStack;
    }

    /**
     * Fill area content from files
     *
     * @param  Area   $area
     * @param  string $path Path from where to read sortorder file and content
     */
    private function fillArea(Area $area, $path)
    {
        $finder = new Finder($path);

        foreach (SortorderParser::parse($finder) as $subPath)
        {
            $contentFullPath = $path . DIRECTORY_SEPARATOR . $subPath;
            if(file_exists($contentFullPath))
            {
                //Detect file extension
                $type = substr($subPath, strrpos($subPath, '.') + 1);

                if($fileContent = @file_get_contents($contentFullPath))
                {
                    $area->addContentAsLast(new Content($type, $fileContent));
                }
                else
                {
                    throw new PageException("Unable to read content from [$contentFullPath]");
                }
            }
            else
            {
                throw new  PageException("Unable to find content at [$contentFullPath]");
            }
        }
    }

    /**
     * Read area definitions and content from the disk
     *
     * @param  string $dirPath
     */
    private function scanAreas($dirPath)
    {
        $dirPath = realpath($dirPath);
        $realContentPath = realpath($this->contentPath);

        if(strcmp($realContentPath, $dirPath) > 0)
        {
            return;
        }

        if(is_dir($dirPath) && (($dir = @opendir($dirPath)) !== false))
        {
            while (($subDirName = readdir($dir)) !== false)
            {
                if(
                    ((substr($subDirName, 0, 1) == '.') || (substr($subDirName, 0, 1) == '_')) &&
                    $subDirName != '.' &&
                    $subDirName != '..' &&
                    is_dir($dirPath . DIRECTORY_SEPARATOR . $subDirName)
                ) {
                    $areaName = $subDirName;
                    //Searching for all areas
                    if(realpath($realContentPath . $this->getNode()->getUri()) == $dirPath)
                    {
                        //normalize area name
                        $areaName = (substr($areaName, 1, 5) == 'tree_') ? substr($areaName, 6) : substr($areaName, 1);
                        $area = new Area($areaName, false);
                        $this->areas[$areaName] = $area;
                        $this->fillArea($area, $dirPath . DIRECTORY_SEPARATOR . $subDirName);
                    }
                    //Searching for areas to inherit only
                    else
                    {
                        if(substr($areaName, 1, 5) == 'tree_')
                        {
                            $areaName = substr($areaName, 6);

                            if(!$this->hasArea($areaName))
                            {
                                $area = new Area($areaName, true);
                                $this->areas[$areaName] = $area;
                                $this->fillArea($area, $dirPath . DIRECTORY_SEPARATOR . $subDirName);
                            }
                        }
                    }
                }
            }

            closedir($dir);
        }
        else
        {
            throw new  PageException("Unable to read path");
        }

        $this->scanAreas($dirPath . '/..');
    }

    /**
     * Return Area object if it exists
     *
     * @param  string $name
     * @return Area
     */
    public function getArea($name)
    {
        if(null === $this->areas)
        {
            $this->areas = array();

            $this->scanAreas($this->contentPath . $this->getNode()->getUri());
        }

        return array_key_exists($name, $this->areas) ? $this->areas[$name] : null;
    }

    /**
     * Return true if area exists even empty
     *
     * @param  string  $name
     * @return boolean
     */
    public function hasArea($name)
    {
        return (null !== $this->getArea($name));
    }

    /**
     * Recursively reads Node properties. Node is not a structure
     *
     * @throws \Coral\SiteBundle\Exception\SitemapException in case .properties file is missing
     * @return Node
     */
    private function readNode()
    {
        $request = $this->requestStack->getCurrentRequest();
        $finder  = RequestFilter::getFinder($request, $this->contentPath);

        if(false !== $finder->getPropertiesPath())
        {
            $properties = PropertiesParser::parse($finder);
            $node = new Node($properties['name'], $request->getPathInfo());

            //Set properties
            foreach($properties['properties'] as $key => $value)
            {
                $node->setProperty($key, $value);
            }

            //Read properties from parent nodes
            $realContentPath = realpath($this->contentPath);
            $parentDir = realpath(dirname($finder->getPropertiesPath()));

            while(strcmp($realContentPath, $parentDir) <= 0)
            {
                $parentFinder = new Finder($parentDir);
                if(false !== $parentFinder->getPropertiesPath())
                {
                    $properties = PropertiesParser::parse($parentFinder);
                    foreach($properties['properties'] as $key => $value)
                    {
                        if((substr($key, 0, 5) == 'tree_') && !$node->hasProperty($key))
                        {
                            $node->setProperty($key, $value);
                        }
                    }
                }
                else
                {
                    throw new SitemapException(sprintf('Unable to read properties [%s].', $parentPropertiesFile));
                }
                $parentDir = realpath($parentDir . '/..');
            }

            return $node;
        }

        throw new SitemapException(sprintf('Unable to read node for request [%s].', $request->getPathInfo()));
    }

    /**
     * Get Node
     *
     * @return Node
     */
    public function getNode()
    {
        if(null === $this->node)
        {
            $this->node = $this->readNode();
        }

        return $this->node;
    }
}