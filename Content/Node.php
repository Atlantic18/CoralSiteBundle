<?php

namespace Coral\SiteBundle\Content;

/**
 * Node represents a simple item within a sitemap. In other words it can represent
 * a page, redirection, menu structure etc.
 */
class Node
{
    /**
     * Node nam
     * @var string
     */
    private $name;
    /**
     * Node uri (absolute url without hostname)
     * @var string
     */
    private $uri;
    /**
     * Child nodes
     * @var array
     */
    private $children;
    /**
     * Node properties (e.g. permissions, template)
     * @var array
     */
    private $properties;
    /**
     * Previous sibling
     *
     * @var Node
     */
    private $prevNode;
    /**
     * Next sibling
     *
     * @var Node
     */
    private $nextNode;
    /**
     * Parent Node
     *
     * @var Node
     */
    private $parent;

    /**
     * Node Name and uri is mandatory for a node.
     * @param string $name
     * @param string $uri
     */
    public function __construct($name, $uri)
    {
        $this->name       = $name;
        $this->uri        = $uri;
        $this->children   = null;
        $this->properties = null;
        $this->prevNode   = null;
        $this->nextNode   = null;
        $this->parent     = null;
    }

    /**
     * Add a child to the end of child nodes list
     *
     * @param Node $node
     */
    public function addChildAsLast(Node $node)
    {
        if(null === $this->children)
        {
            $this->children = array();
        }

        $this->children[] = $node;
    }

    /**
     * Node has any child nodes
     *
     * @return boolean true if node has any children
     */
    public function hasChildren()
    {
        return (null !== $this->children) && count($this->children);
    }

    /**
     * Get list of all child nodes
     *
     * @return array array of Node
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get child Node by Index
     *
     * @param  int $index 0-n index
     * @return Node       Node or null if index doesn't exist
     */
    public function getChildByIndex($index)
    {
        $index = intval($index);

        return ((null !== $this->children) && array_key_exists($index, $this->children)) ? $this->children[$index] : null;
    }

    /**
     * Next Node
     *
     * @return Node
     */
    public function next()
    {
        return $this->nextNode;
    }

    /**
     * Set Next Node
     *
     * @param Node $node
     */
    public function setNext(Node $node)
    {
        $this->nextNode = $node;
    }

    /**
     * Previous Node
     *
     * @return Node
     */
    public function prev()
    {
        return $this->prevNode;
    }

    /**
     * Set Previous Node
     *
     * @param Node $node
     */
    public function setPrev(Node $node)
    {
        $this->prevNode = $node;
    }

    /**
     * Parent Node
     *
     * @return Node
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Set Parent Node
     *
     * @param Node $node
     */
    public function setParent(Node $node)
    {
        $this->parent = $node;
    }

    /**
     * Set Node property
     *
     * @param string $key
     * @param string $value
     */
    public function setProperty($key, $value)
    {
        if(null === $this->properties)
        {
            $this->properties = array();
        }
        $key   = (string) $key;
        $value = (string) $value;

        $this->properties[$key] = $value;

        if((substr($key, 0, 5) == 'tree_') && $this->hasChildren())
        {
            foreach($this->children as $child)
            {
                if(!$child->hasProperty($key))
                {
                    $child->setProperty($key, $value);
                }
            }
        }
    }

    /**
     * Get Node uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Get Node name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Node peroperty
     *
     * @param  string  $key     Property name
     * @return boolean true if property is set
     */
    public function hasProperty($key)
    {
        return
            (null !== $this->properties) &&
            (
                array_key_exists((string) $key, $this->properties) ||
                array_key_exists('tree_' . $key, $this->properties)
            );
    }

    /**
     * Get Node property
     *
     * @param  string  $key     Property name
     * @param  boolean $default Default property value
     * @return string           Property value
     */
    public function getProperty($key, $default = false)
    {
        if(!$this->hasProperty($key))
        {
            return $default;
        }
        if(array_key_exists((string) $key, $this->properties))
        {
            return $this->properties[$key];
        }
        return $this->properties['tree_' . $key];
    }

    /**
     * Get all properties
     *
     * @return array Property name => Property value pairs
     */
    public function getAllProperties()
    {
        return $this->properties;
    }
}