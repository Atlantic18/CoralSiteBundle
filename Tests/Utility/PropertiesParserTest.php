<?php

namespace Coral\SiteBundle\Tests\Utility;

use Coral\SiteBundle\Utility\Finder;
use Coral\SiteBundle\Utility\PropertiesParser;

class PropertiesParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidFileException()
    {
        $finder = new Finder('invalid_filename');
        PropertiesParser::parse($finder);
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\PropertiesParserException
     */
    public function testEmptyPropertiesException()
    {
        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/properties/empty');
        PropertiesParser::parse($finder);
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\PropertiesParserException
     */
    public function testInvalidPropertiesException()
    {
        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/properties/invalid');
        PropertiesParser::parse($finder);
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\PropertiesParserException
     */
    public function testInvalidPropertiesKeyException()
    {
        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/properties/invalid_key');
        PropertiesParser::parse($finder);
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\PropertiesParserException
     */
    public function testInvalidPropertiesKeyValueException()
    {
        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/properties/invalid_value');
        PropertiesParser::parse($finder);
    }

    public function testParse()
    {
        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/properties/correct');
        $properties = PropertiesParser::parse($finder);

        $this->assertTrue(is_array($properties));
        $this->assertCount(2, $properties);
        $this->assertArrayHasKey('name', $properties);
        $this->assertArrayHasKey('properties', $properties);
        $this->assertEquals('Homepage', $properties['name']);
        $this->assertCount(3, $properties['properties']);
        $this->assertEquals('default.html.twig', $properties['properties']['tree_template']);
        $this->assertEquals('acme, project, default', $properties['properties']['keywords']);
        $this->assertEquals('ACME: see for yourself', $properties['properties']['description']);
    }
}