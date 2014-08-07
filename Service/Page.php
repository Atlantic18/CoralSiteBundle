<?php

namespace Coral\SiteBundle\Service;

use Coral\SiteBundle\Content\Area;
use Coral\SiteBundle\Content\Content;
use Coral\SiteBundle\Content\Node;
use Coral\SiteBundle\Exception\PageException;
use Coral\SiteBundle\Parser\SortorderParser;

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
     * List of available filters for area
     *
     * @var array
     */
    private $filters;
    /**
     * Root content path
     *
     * @var string
     */
    private $contentPath;

    /**
     * Page constructor
     *
     * @param Node   $node
     * @param string $contentPath Where the content is located
     */
    public function __construct($contentPath)
    {
        $this->node        = null;
        $this->areas       = null;
        $this->filters     = array();
        $this->contentPath = $contentPath;
    }

    public function addFilter(\Swift_Transport $transport, $alias)
    {
        $this->filters[$alias] = $transport;
    }

    public function getFilter($alias)
    {
        if (array_key_exists($alias, $this->filters)) {
           return $this->filters[$alias];
        }
    }

    /**
     * Fill area content from files
     *
     * @param  Area   $area
     * @param  string $path Path from where to read sortorder file and content
     */
    private function fillArea(Area $area, $path)
    {
        $sortorderFileName  = $path . DIRECTORY_SEPARATOR . '.sortorder';

        if(file_exists($sortorderFileName))
        {
            foreach (SortorderParser::parse($sortorderFileName) as $subPath)
            {
                $contentFullPath = $path . DIRECTORY_SEPARATOR . $subPath;
                if(file_exists($contentFullPath))
                {
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
    }

    /**
     * Read area definitions and content from the disk
     *
     * @param  Node $node
     * @return array List of Area
     */
    private function scanAreas(Node $node)
    {
        $dirPath = $this->contentPath . $node->getUri();
        if(is_dir($dirPath) && (($dir = @opendir($dirPath)) !== false))
        {
            while (($subDirName = readdir($dir)) !== false)
            {
                if(
                    (substr($subDirName, 0, 1) == '.') &&
                    $subDirName != '.' &&
                    $subDirName != '..' &&
                    is_dir($dirPath . DIRECTORY_SEPARATOR . $subDirName)
                ) {
                    $areaName = $subDirName;
                    //Searching for all areas
                    if($node === $this->getNode())
                    {
                        //normalize area name
                        $areaName = (substr($areaName, 0, 6) == '.tree_') ? substr($areaName, 6) : substr($areaName, 1);
                        $area = new Area($areaName, false);
                        $this->areas[$areaName] = $area;
                        $this->fillArea($area, $dirPath . DIRECTORY_SEPARATOR . $subDirName);
                    }
                    //Searching for areas to inherit only
                    else
                    {
                        if(substr($areaName, 0, 6) == '.tree_')
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

        if(null !== $node->parent())
        {
            $this->scanAreas($node->parent());
        }
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

            $this->scanAreas($this->getNode());
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

    public function renderArea($name)
    {

    }

    public function setNode(Node $node)
    {
        $this->node = $node;
    }

    public function getNode()
    {
        return $this->node;
    }
}