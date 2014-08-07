<?php

namespace Coral\SiteBundle\Content;

/**
 * DTO for Page Service
 */
class Area
{
    /**
     * Area name
     *
     * @var string
     */
    private $name;
    /**
     * Area list of content
     *
     * @var array
     */
    private $contents;
    /**
     * Flag whether this area is inherited
     *
     * @var boolean
     */
    private $inherited;

    /**
     * Area constructor
     *
     * @param string $name Area name
     */
    public function __construct($name, $inherited = false)
    {
        $this->name      = $name;
        $this->inherited = (boolean) $inherited;
        $this->contents  = null;
    }

    /**
     * Area name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return whether this area is inherited
     *
     * @return boolean
     */
    public function isInherited()
    {
        return $this->inherited;
    }

    /**
     * Add Content to the last place of Area
     *
     * @param Content $content
     */
    public function addContentAsLast(Content $content)
    {
        if(null === $this->contents)
        {
            $this->contents = array();
        }
        $this->contents[] = $content;
    }

    /**
     * Get area content by index
     *
     * @param int $index Content index
     * @return array Array of Contents
     */
    public function getContentByIndex($index)
    {
        $index = intval($index);
        return array_key_exists($index, $this->contents) ? $this->contents[$index] : null;
    }

    /**
     * Returns true if area is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return (null === $this->contents);
    }
}